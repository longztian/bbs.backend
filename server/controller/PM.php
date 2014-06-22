<?php

namespace site\controller;

use site\Controller;
use site\dbobject\PrivMsg;
use site\dbobject\User;
use lzx\core\Mailer;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Hidden;
use lzx\html\TextArea;

class PM extends Controller
{

   const PMS_PER_PAGE = 25;

   protected function _default()
   {
      
      $this->cache->setStatus( FALSE );

      if ( $this->request->uid == 0 )
      {
         $this->cookie->loginReferer = $this->request->uri;
         $this->request->redirect( '/user' );
      }
      // logged in user
      else
      {
         $msgID = (int) ($this->args[1]);
         if ( $msgID > 0 )
         {
            $action = sizeof( $this->args ) > 2 ? $this->args[2] : 'display';
            $this->run( $action );
         }
      }
   }

   public function display()
   {
      $msgID = \intval( $this->args[1] );

      $pm = new PrivMsg();
      $msgs = $pm->getPMConversation( $msgID, $this->request->uid );
      if ( \sizeof( $msgs ) == 0 )
      {
         $this->error( '错误：该条短信不存在。' );
         return;
      }

      $replyTo = $pm->getReplyTo( $msgID, $this->request->uid );

      $list = [];
      foreach ( $msgs as $m )
      {
         $avatar = new HTMLElement(
            'div', $this->html->link(
               new HTMLElement( 'img', NULL, $attr = [
               'alt' => $m['username'] . ' 的头像',
               'src' => $m['avatar'] ? $m['avatar'] : '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg',
               ] ), '/user/' . $m['uid'] ), ['class' => 'pm_avatar'] );

         $info = new HTMLElement(
            'div', [$this->html->link( $m['username'], '/user/' . $m['uid'] ), '<br />', \date( 'm/d/Y', $m['time'] ), '<br />', \date( 'H:i', $m['time'] )], ['class' => 'pm_info'] );

         $body = new HTMLElement(
            'div', \nl2br( $m['body'] ) . (new HTMLElement(
            'div', $this->html->link( ($m['id'] == $msgID ? '删除话题' : '删除' ), '/pm/' . $msgID . '/delete/' . $m['id'], ['class' => 'button'] ), ['class' => 'ed_actions'] )), ['class' => 'pm_body'] );
         $list[] = $avatar . $info . $body;
      }

      $messages = $this->html->ulist( $list, ['class' => 'pm_thread'] );

      $reply_form = new Form( array(
         'action' => '/pm/' . $msgID . '/reply',
         'id' => 'pm_reply'
         ) );
      $receipt = new Input( 'to', '收信人' );
      $receipt->attributes = ['readonly' => 'readonly'];
      $receipt->setValue( $replyTo['username'] );
      $message = new TextArea( 'body', '回复内容', '最少5个字母或3个汉字', TRUE );
      $toUID = new Hidden( 'toUID', $replyTo['id'] );
      $fromUID = new Hidden( 'fromUID', $this->request->uid );

      $reply_form->setData( array(
         $receipt->toHTMLElement(),
         $message->toHTMLElement(),
         $fromUID->toHTMLElement(),
         $toUID->toHTMLElement()
      ) );
      $reply_form->setButton( array('submit' => '发送') );

      $this->html->var['content'] = $link_tabs . $pager . $messages . $reply_form;
   }

   public function reply()
   {
      $msgID = \intval( $this->args[1] );

      if ( $this->request->uid != $this->request->post['fromUID'] )
      {
         $this->error( '错误，用户没有权限回复此条短信' );
      }

      if ( \strlen( $this->request->post['body'] ) < 5 )
      {
         $this->error( '错误：短信正文字数太少。' );
      }

      $user = new User( $this->request->post['toUID'], 'username,email' );

      if ( !$user->exists() )
      {
         $this->error( '错误：收信人用户不存在。' );
      }

      $pm = new PrivMsg();
      $pm->msgID = $msgID;
      $pm->fromUID = $this->request->uid;
      $pm->toUID = $user->id;
      $pm->body = $this->request->post['body'];
      $pm->time = $this->request->timestamp;
      $pm->add();

      $mailer = new Mailer();
      $mailer->to = $user->email;
      $mailer->subject = $user->username . ' 您有一封新的站内短信';
      $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $pm->msgID;
      if ( !$mailer->send() )
      {
         $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
      }

      $this->request->redirect( '/pm/' . $msgID );
   }

   public function delete()
   {
      $msgID = \intval( $this->args[1] );
      $messageID = \intval( $this->args[3] );

      $pm = new PrivMsg();
      $pm->id = $messageID;
      try
      {
         $pm->deleteByUser( $this->request->uid );
      }
      catch ( \Exception $e )
      {
         $this->error( 'failed to delete message ' . $messageID . ' as user ' . $this->request->uid );
      }

      $redirect_uri = $msgID == $messageID ? '/user/pm' : '/pm/' . $msgID;
      $this->request->redirect( $redirect_uri );
   }

}

//__END_OF_FILE__
