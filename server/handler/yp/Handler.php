<?php

namespace site\handler\yp;

use site\Controller;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use lzx\cache\PageCache;

class Handler extends Controller
{
    const NODES_PER_PAGE = 25;

    public function run()
    {
        $this->cache = new PageCache($this->request->uri);

        $tid = $this->args ? (int) $this->args[0] : 0;
        if ($tid <= 0) {
            $this->ypHome();
        } else {
            $this->nodeList($tid);
        }
    }

// $yp, $groups, $boards are arrays of category id
    protected function ypHome()
    {
        $tag = new Tag(self::$city->YPRootID, null);
        $yp = $tag->getTagTree();
        $this->var['content'] = new Template('yp_home', ['tid' => $tag->id, 'yp' => $yp]);
    }

    protected function nodeList($tid)
    {
        $tag = new Tag($tid, null);
        $tagRoot = $tag->getTagRoot();
        $tids = implode(',', $tag->getLeafTIDs());

        $breadcrumb = [];
        foreach ($tagRoot as $i => $t) {
            $breadcrumb[$t['name']] = ($i === self::$city->YPRootID ? '/yp' : ('/yp/' . $i));
        }

        $node = new Node();
        $nodeCount = $node->getNodeCount($tids);
        list($pageNo, $pageCount) = $this->getPagerInfo($node->getNodeCount($tid), self::NODES_PER_PAGE);
        $pager = Template::pager($pageNo, $pageCount, '/yp/' . $tid);

        $nodes = $node->getYellowPageNodeList($tids, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);

        $nids = array_column($nodes, 'id');

        $contents = [
            'tid' => $tid,
            'cateName' => $tag->name,
            'cateDescription' => $tag->description,
            'breadcrumb' => Template::breadcrumb($breadcrumb),
            'pager' => $pager,
            'nodes' => (empty($nodes) ? null : $nodes),
            'ajaxURI' => '/api/viewcount/' . implode(',', $nids)
        ];
        $this->var['content'] = new Template('yp_list', $contents);
    }
}
