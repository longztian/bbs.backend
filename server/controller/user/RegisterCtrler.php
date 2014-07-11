<?php

namespace site\controller\user;

use site\controller\User;
use site\dbobject\User as UserObject;
use lzx\html\Template;
use lzx\core\Mailer;

class RegisterCtrler extends User
{

   public function run()
   {
      if ( $this->request->uid != self::GUEST_UID )
      {
         $this->error( '错误：用户已经登录，不能注册新用户' );
      }

      if ( empty( $this->request->post ) )
      {

         $this->html->var[ 'content' ] = new Template( 'user_register', ['captcha' => '/captcha/' . \mt_rand() ] );
      }
      else
      {
         if ( \strtolower( $this->session->captcha ) != \strtolower( $this->request->post[ 'captcha' ] ) )
         {
            $this->error( '错误：图形验证码错误' );
         }
         unset( $this->session->captcha );

         // check username and email first
         if ( empty( $this->request->post[ 'username' ] ) )
         {
            $this->error( '请填写用户名' );
         }

         if ( !\filter_var( $this->request->post[ 'email' ], \FILTER_VALIDATE_EMAIL ) )
         {
            $this->error( '不合法的电子邮箱 : ' . $this->request->post[ 'email' ] );
         }

         if ( isset( $this->request->post[ 'submit' ] ) || $this->_isBot( $this->request->post[ 'email' ] ) )
         {
            $this->logger->info( 'STOP SPAMBOT : ' . $this->request->post[ 'email' ] );
            $this->error( '系统检测到可能存在的注册机器人。所以不能提交您的注册申请，如果您认为这是一个错误的判断，请与网站管理员联系。' );
         }

         $user = new UserObject();
         $user->username = $this->request->post[ 'username' ];
         $user->email = $this->request->post[ 'email' ];
         $user->createTime = $this->request->timestamp;
         $user->lastAccessIP = (int) \ip2long( $this->request->ip );
         try
         {
            $user->add();
         }
         catch (\PDOException $e)
         {
            $this->logger->error( $e->getMessage(), $e->getTrace() );
            $this->error( $e->errorInfo[ 2 ] );
         }
         // create user action and send out email
         $mailer = new Mailer();
         $mailer->to = $user->email;
         $mailer->subject = $user->username . ' 的HoustonBBS账户激活和设置密码链接';
         $contents = [
            'username' => $user->username,
            'uri' => $this->_createUser( $user->id, '/user/activate' ),
            'sitename' => 'HoustonBBS'
         ];
         $mailer->body = new Template( 'mail/newuser', $contents );

         if ( $mailer->send() === FALSE )
         {
            $this->error( 'sending new user activation email error: ' . $user->email );
         }
         $this->html->var[ 'content' ] = '感谢注册！账户激活email已经成功发送到您的注册邮箱 ' . $user->email . ' ，请检查email并且按其中提示激活账户。<br />如果您的收件箱内没有帐号激活的电子邮件，请检查电子邮件的垃圾箱，或者与网站管理员联系。';
      }
   }

}

//__END_OF_FILE__