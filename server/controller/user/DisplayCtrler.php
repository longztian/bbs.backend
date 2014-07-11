<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;

class DisplayCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_displayLogin( $this->request->uri );
      }

      $uid = empty( $this->args ) ? $this->request->uid : (int) $this->args[ 0 ];
      // user are not allowed to view ADMIN's info
      if ( $uid == self::ADMIN_UID && $this->request->uid != self::ADMIN_UID )
      {
         $this->request->pageForbidden();
      }

      $user = new UserObject( $uid );
      if ( !$user->exists() )
      {
         $this->error( '错误：用户不存在' );
      }

      $sex = \is_null( $user->sex ) ? '未知' : ( $user->sex == 1 ? '男' : '女');
      if ( $user->birthday )
      {
         $birthday = \substr( \sprintf( '%08u', $user->birthday ), 4, 4 );
         $birthday = \substr( $birthday, 0, 2 ) . '/' . \substr( $birthday, 2, 2 );
      }
      else
      {
         $birthday = '未知';
      }

      $content = [
         'uid' => $uid,
         'username' => $user->username,
         'avator' => $user->avatar ? $user->avatar : '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg',
         'userLinks' => $this->_getUserLinks( $uid, '/user/display/' . $uid ),
         'pm' => $uid != $this->request->uid ? '/user/pm/' . $uid : '',
         'info' => [
            '微信' => $user->wechat,
            'QQ' => $user->qq,
            '个人网站' => $user->website,
            '性别' => $sex,
            '生日' => $birthday,
            '职业' => $user->occupation,
            '兴趣爱好' => $user->interests,
            '自我介绍' => $user->favoriteQuotation,
            '注册时间' => \date( 'm/d/Y H:i:s T', $user->createTime ),
            '上次登录时间' => \date( 'm/d/Y H:i:s T', $user->lastAccessTime ),
            '上次登录地点' => $this->request->getLocationFromIP( $user->lastAccessIP )
         ],
         'topics' => $user->getRecentNodes( 10 ),
         'comments' => $user->getRecentComments( 10 )
      ];

      $this->html->var[ 'content' ] = new Template( 'user_display', $content );
   }

}

//__END_OF_FILE__