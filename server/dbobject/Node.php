<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;
use site\dbobject\Tag;

class Node extends DBObject
{
    public $id;
    public $uid;
    public $tid;
    public $createTime;
    public $lastModifiedTime;
    public $title;
    public $viewCount;
    public $weight;
    public $status;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'nodes';
        parent::__construct($db, $table, $id, $properties);
    }

    public function getForumNodeList($cid, $tid, $limit = 25, $offset = 0)
    {
        return $this->call('get_tag_nodes_forum(' . $cid . ', ' . $tid . ', ' . $limit . ', ' . $offset . ')');
    }

    public function getForumNode($id, $useNewVersion = false)
    {
        $sp = $useNewVersion ? 'get_forum_node_2' : 'get_forum_node';
        $arr = $this->call($sp . '(' . $id . ')');

        if (sizeof($arr) > 0) {
            $node = $arr[0];
            $node['files'] = $this->call('get_node_images(' . $id . ')');
            return $node;
        } else {
            return null;
        }
    }

    public function getForumNodeComments($id, $limit, $offset, $useNewVersion = false)
    {
        $sp = $useNewVersion ? 'get_forum_node_comments_2' : 'get_forum_node_comments';
        $arr = $this->call($sp . '(' . $id . ', ' . $limit . ', ' . $offset . ')');

        foreach ($arr as $i => $r) {
            $arr[$i]['files'] = $this->call('get_comment_images(' . $r['id'] . ')');
        }

        return $arr;
    }

    public function getYellowPageNodeList($tids, $limit = false, $offset = false)
    {
        return $this->call('get_tag_nodes_yp("' . $tids . '",' . $limit . ',' . $offset . ')');
    }

    public function getYellowPageNode($id)
    {
        $arr = $this->call('get_yp_node(' . $id . ')');
        if (sizeof($arr) > 0) {
            return $arr[0];
        } else {
            return null;
        }
    }

    public function getYellowPageNodeComments($id, $limit = false, $offset = false)
    {
        $arr = $this->call('get_yp_node_comments(' . $id . ', ' . $limit . ', ' . $offset . ')');

        if ($offset == 0) {
            $arr[0]['files'] = $this->call('get_comment_images(' . $arr[0]['id'] . ')');
        }

        return $arr;
    }

    public function getViewCounts($nids)
    {
        return $this->call('get_node_view_count("' . implode(',', $nids) . '")');
    }

    public function getTags($nid)
    {
        static $tags = [];

        if (!array_key_exists($nid, $tags)) {
            $node = new Node($nid, 'tid');
            if ($node->exists()) {
                $tag = new Tag($node->tid, null);
                $tags[$nid] = $tag->getTagRoot();
            } else {
                $tags[$nid] = [];
            }
        }
        return $tags[$nid];
    }

    public function getLatestForumTopics($forumRootID, $count)
    {
        return $this->call('get_tag_recent_nodes("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getHotForumTopics($forumRootID, $count, $timestamp)
    {
        return $this->call('get_tag_hot_nodes("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $timestamp . ', ' . $count . ')');
    }

    public function getHotForumTopicNIDs($forumRootID, $count, $timestamp)
    {
        return array_column($this->getHotForumTopics($forumRootID, $count, $timestamp), 'nid');
    }

    public function getLatestYellowPages($ypRootID, $count)
    {
        return $this->call('get_tag_recent_nodes_yp("' . implode(',', (new Tag($ypRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getLatestForumTopicReplies($forumRootID, $count)
    {
        return $this->call('get_tag_recent_comments("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getLatestYellowPageReplies($ypRootID, $count)
    {
        return $this->call('get_tag_recent_comments_yp("' . implode(',', (new Tag($ypRootID, null))->getLeafTIDs()) . '", ' . $count . ')');
    }

    public function getNodeCount($tids)
    {
        return intval(array_pop(array_pop($this->call('get_tag_node_count("' . $tids . '")'))));
    }

    public function getNodeStat($forumRootID)
    {
        $today = strtotime(date("m/d/Y"));
        $stats = array_pop($this->call('get_node_stat("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $today . ')'));
        return [
            'nodeCount'            => $stats['node_count_total'],
            'nodeTodayCount'     => $stats['node_count_recent'],
            'commentTodayCount' => $stats['comment_count_recent'],
            'postCount'            => $stats['node_count_total'] + $stats['comment_count_total']
        ];
    }
}
