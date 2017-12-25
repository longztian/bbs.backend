<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;
use lzx\cache\CacheHandlerInterface;
use lzx\cache\PageCache;
use lzx\cache\SegmentCache;
use lzx\db\DB;

class CacheHandler implements CacheHandlerInterface
{
    static public $path;
    protected $db;
    // Cache tables
    protected $nameTable;
    protected $treeTable;
    protected $eventTable;

    private function __construct(DB $db)
    {
        $this->db = $db;
        $this->nameTable = 'cache_names';
        $this->treeTable = 'cache_tree';
        $this->eventTable = 'cache_event_listeners';
    }

    /**
     * singleton design pattern
     */
    public static function getInstance(DB $db = null): CacheHandler
    {
        static $instance;

        if (!isset($instance)) {
            if ($db) {
                $instance = new self($db);
            } else {
                throw new Exception('no instance is available. a DB object is required for creating a new instance.');
            }
        }
        return $instance;
    }

    public function getCacheTreeTable(): string
    {
        return $this->treeTable;
    }

    public function getCacheEventTable(): string
    {
        return $this->eventTable;
    }

    public function setCacheTreeTable(string $treeTable): void
    {
        $this->treeTable = $treeTable;
    }

    public function setCacheEventTable(string $eventTable): void
    {
        $this->eventTable = $eventTable;
    }

    /**
     * Factory design patern
     */
    public function createCache(string $key): Cache
    {
        return $key[0] === '/' ? new PageCache($key) : new SegmentCache($key);
    }

    public function getCleanName(string $name): string
    {
        static $names = [];

        if (array_key_exists($name, $names)) {
            return $names[$name];
        }

        $name = trim($name);

        if (strlen($name) == 0 || strpos($name, ' ') !== false) {
            throw new Exception('cache name is empty : ' . $name);
        }

        if ($name[0] === '/') {
            // page uri
            if (!strpos($name, '#')) {
                // not previously processed
                // use # to seperate uri and query string
                if (strpos($name, '?')) {
                    // has query string
                    $name = str_replace('?', '#', $name);
                } else {
                    $name = $name . '#';
                }
            } else {
                // previously processed or pre-processed name
                // validate '#'
                if (substr_count($name, '#') > 1) {
                    throw new Exception('pre-processed cache name has multiple "#" charactor : ' . $name);
                }

                // validate '?'
                if (strpos($name, '?')) {
                    throw new Exception('pre-processed cache name has "?" charactor : ' . $name);
                }
            }
        } else {
            // segment name or event name
            $name = preg_replace('/[^0-9a-z\.\_\-]/i', '_', $name);
        }

        // save processed name to name cache
        $names[$name] = $name;
        if ($name != $name) {
            $names[$name] = $name;
        }

        return $name;
    }

    public function getFileName(Cache $cache): string
    {
        static $filenames = [];

        $key = $cache->getKey();
        if (array_key_exists($key, $filenames)) {
            return $filenames[$key];
        }

        switch (get_class($cache)) {
            case 'lzx\cache\PageCache':
                $filename = self::$path . '/page' . $key . '.html.gz';
                break;
            case 'lzx\cache\SegmentCache':
                $filename = self::$path . '/segment/' . $key . '.txt';
                break;
            default:
                throw new Exception('unsupport cache type: ' . get_class($cache));
        }

        $dir = dirname($filename);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $filenames[$key] = $filename;
        return $filename;
    }

    public function getID(string $name): int
    {
        static $ids = [];
        // found from cached id
        if (array_key_exists($name, $ids)) {
            return $ids[$name];
        }

        // found from database
        $res = $this->db->query('SELECT id FROM ' . $this->nameTable . ' WHERE name = :key', [':key' => $name]);
        switch (count($res)) {
            case 0:
                // add to database
                $this->db->query('INSERT INTO ' . $this->nameTable . ' (name) VALUEs (:key)', [':key' => $name]);
                // save to id cache
                $id = (int) $this->db->insertId();
                break;
            case 1:
                // save to id cache
                $id = (int) array_pop($res[0]);
                break;
            default:
                throw new Exception('multiple ID found for name: ' . $name);
        }
        // save to cache
        $ids[$name] = $id;

        return $id;
    }

    public function unlinkParents(int $id): void
    {
        $this->db->query('DELETE FROM ' . $this->treeTable . ' WHERE cid = :cid', [':cid' => $id]);
    }

    public function linkParents(int $id, array $parents): void
    {
        if ($parents) {
            array_unique($parents);

            $existing = array_column($this->db->query('SELECT DISTINCT(pid) AS id FROM ' . $this->treeTable . ' WHERE cid = :cid', [':cid' => $id]), 'id');
            $values = [];
            foreach ($parents as $key) {
                $pid = $this->getID($key);
                if (!in_array($pid, $existing)) {
                    $values[] = '(' . $pid . ',' . $id . ')';
                }
            }

            if ($values) {
                $this->db->query('INSERT INTO ' . $this->treeTable . ' VALUES ' . implode(',', $values));
            }
        }
    }

    public function getChildren(int $id): array
    {
        $children = $this->db->query('SELECT DISTINCT(c.id), c.name FROM ' . $this->nameTable . ' AS c JOIN ' . $this->treeTable . ' AS t ON c.id = t.cid WHERE t.pid = :pid', [':pid' => $id]);
        foreach ($children as $c) {
            $this->ids[$c['name']] = $c['id'];
        }

        return array_column($children, 'name');
    }

    public function unlinkEvents(int $id): void
    {
        $this->db->query('DELETE FROM ' . $this->eventTable . ' WHERE lid = :lid', [':lid' => $id]);
    }

    public function getEventListeners(int $eid, int $oid): array
    {
        $children = $this->db->query('SELECT DISTINCT(c.id), c.name FROM ' . $this->nameTable . ' AS c JOIN ' . $this->eventTable . ' AS e ON c.id = e.lid WHERE e.eid = :eid AND e.oid = :oid', [':eid' => $eid, ':oid' => $oid]);
        foreach ($children as $c) {
            $this->ids[$c['name']] = $c['id'];
        }

        return array_column($children, 'name');
    }

    public function addEventListeners(int $eid, int $oid, array $listeners): void
    {
        if ($listeners) {
            array_unique($listeners);

            $existing = array_column($this->db->query('SELECT DISTINCT(lid) AS id FROM ' . $this->eventTable . ' WHERE eid = :eid AND oid = :oid', [':eid' => $eid, ':oid' => $oid]), 'id');
            $values = [];
            foreach ($listeners as $key) {
                $lid = $this->getID($key);
                if (!in_array($lid, $existing)) {
                    $values[] = '(' . $eid . ',' . $oid . ',' . $lid . ')';
                }
            }

            if ($values) {
                $this->db->query('INSERT INTO ' . $this->eventTable . ' VALUES ' . implode(',', $values));
            }
        }
    }
}
