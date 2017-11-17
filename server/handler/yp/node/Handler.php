<?php declare(strict_types=1);

namespace site\handler\yp\node;

use site\Controller;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use site\dbobject\NodeYellowPage;
use site\dbobject\Image;
use site\dbobject\AD;
use site\dbobject\Comment;

class Handler extends Controller
{
    public function run()
    {
        if ($this->request->uid != 1 && $this->request->uid != 8831 && $this->request->uid != 3) {
            $this->pageForbidden();
        }

        $tid = $this->args ? (int) $this->args[0] : 0;
        if ($tid <= 0) {
            $this->pageNotFound();
        }

        $tag = new Tag();
        $tag->parent = $tid;
        if ($tag->getCount() > 0) {
            $this->error('错误：您不能在该类别中添加黄页，请到它的子类别中添加。');
        }

        if (empty($this->request->post)) {
            $ad = new AD();
            $this->var['content'] = new Template('editor_bbcode_yp', ['ads' => $ad->getList('name')]);
        } else {
            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->request->uid;
            $node->title = $this->request->post['title'];
            $node->createTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();

            $comment = new Comment();
            $comment->nid = $node->id;
            $comment->tid = $tid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();

            $nodeYP = new NodeYellowPage();
            $nodeYP->nid = $node->id;
            $nodeYP->adId = $this->request->post['aid'];
            foreach (array_diff($nodeYP->getProperties(), ['nid']) as $key) {
                $nodeYP->$key = $this->request->post[$key];
            }
            $nodeYP->add();

            if (isset($this->request->post['files'])) {
                $file = new Image();
                $file->cityId = self::$city->id;
                $file->updateFileList($this->request->post['files'], $this->config->path['file'], $node->id, $comment->id);
            }

            $tag = new Tag($tid, 'parent');

            foreach (['latestYellowPages', '/yp/' . $tid, '/yp/' . $tag->parent, '/'] as $key) {
                $this->getIndependentCache($key)->delete();
            }

            $this->pageRedirect('/node/' . $node->id);
        }
    }
}
