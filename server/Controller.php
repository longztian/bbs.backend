<?php

namespace site;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content
use lzx\core\Controller as LzxCtrler;
use lzx\core\Request;
use lzx\html\Template;
use site\Config;
use lzx\core\Logger;
use lzx\core\Session;
use lzx\core\Cookie;
use site\ControllerRouter;
use site\dbobject\User;
use site\dbobject\Tag;
use site\dbobject\SecureLink;
use site\Cache;
use site\CacheEvent;
use lzx\db\DB;

/**
 *
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 * @property \site\Config $config
 * @property \site\dbobject\User $user
 * @property \site\PageCache $cache
 * @property \site\Cache[]  $_independentCacheList
 * @property \site\CacheEvent[] $_cacheEvents
 *
 */
abstract class Controller extends LzxCtrler
{

   const GUEST_UID = 0;
   const ADMIN_UID = 1;

   public $user = NULL;
   public $args;
   public $id;
   public $config;
   public $cache;
   protected $_independentCacheList = [ ];
   protected $_cacheEvents = [ ];

   /**
    * public methods
    */
   public function __construct( Request $req, Template $html, Config $config, Logger $logger, Session $session, Cookie $cookie )
   {
      parent::__construct( $req, $html, $logger, $session, $cookie );
      $this->config = $config;
      if ( !\array_key_exists( $this->class, self::$l ) )
      {
         $lang_file = $lang_path . \str_replace( '\\', '/', $this->_class ) . '.' . Template::$language . '.php';
         if ( \is_file( $lang_file ) )
         {
            include $lang_file;
         }
         self::$l[ $this->_class ] = isset( $language ) ? $language : [ ];
      }

      // register this controller as an observer of the HTML template
      $this->html->attach( $this );
   }

   public function __destruct()
   {
      if ( $this->config->cache )
      {
         if ( $this->cache && Template::getStatus() === TRUE )
         {
            $this->cache->store( $this->html );
            $this->cache->flush();
         }

         foreach ( $this->_independentCacheList as $s )
         {
            $s->flush();
         }

         foreach ( $this->_cacheEvents as $e )
         {
            $e->flush();
         }
      }
   }

   /**
    * Observer design pattern interface
    */
   public function update( Template $html )
   {
      // update user info
      if ( $this->request->uid > 0 )
      {
         $this->user = new User( $this->request->uid, NULL );
         // update access info
         $this->user->call( 'update_access_info(' . $this->request->uid . ',' . $this->request->timestamp . ',' . \ip2long( $this->request->ip ) . ')' );
         // check new pm message
         $this->cookie->pmCount = \intval( $this->user->getPrivMsgsCount( 'new' ) );
         $updateUserInfo = FALSE;
      }

      // set navbar
      $navbarCache = $this->_getIndependentCache( 'page_navbar' );
      $navbar = $navbarCache->fetch();
      if ( !$navbar )
      {
         $vars = [
            'forumMenu' => $this->_createMenu( Tag::FORUM_ID ),
            'ypMenu' => $this->_createMenu( Tag::YP_ID ),
            'uid' => $this->request->uid
         ];
         $navbar = new Template( 'page_navbar', $vars );
         $navbarCache->store( $navbar );
      }
      $html->var[ 'page_navbar' ] = $navbar;

      // set headers
      $html->var[ 'head_description' ] = '休斯顿 华人, 黄页, 移民, 周末活动, 旅游, 单身 交友, Houston Chinese, 休斯敦, 休士頓';
      $html->var[ 'head_title' ] = '缤纷休斯顿华人网';

      // remove this controller from the subject's observer list
      $html->detach( $this );
   }

   protected function ajax( $return )
   {
      $return = \json_encode( $return );
      if ( $return === FALSE )
      {
         $return = [ 'error' => 'ajax json encode error' ];
         $return = \json_encode( $return );
      }
      $this->html = $return;

      if ( DB::$debug )
      {
         $this->logger->debug( DB::getInstance()->queries );
         DB::$debug = FALSE;
      }
   }

   /**
    * 
    * @return \site\Cache
    */
   protected function _getIndependentCache( $key )
   {
      $cache = Cache::create( $key );
      if ( \array_key_exists( $cache->getKey(), $this->_independentCacheList ) )
      {
         return $this->_independentCacheList[ $cache->getKey() ];
      }
      else
      {
         $this->_independentCacheList[ $cache->getKey() ] = $cache;
         return $cache;
      }
   }

   /**
    * 
    * @return \site\CacheEvent
    */
   protected function _getCacheEvent( $name )
   {
      $event = new CacheEvent( $name );
      if ( \array_key_exists( $event->getName(), $this->_cacheEvents ) )
      {
         return $this->_cacheEvents[ $event->getName() ];
      }
      else
      {
         $this->_cacheEvents[ $event->getName() ] = $event;
         return $event;
      }
   }

   protected function _forward( $uri )
   {
      $newReq = $this->request;
      $newReq->uri = $uri;
      $ctrler = ControllerRouter::create( $newReq, $this->html, $this->config, $this->logger, $this->cache, $this->session, $this->cookie );
      $ctrler->run();
   }

   protected function _setLoginRedirect( $uri )
   {
      $this->cookie->loginRedirect = $uri;
   }

   protected function _getLoginRedirect()
   {
      $uri = $this->cookie->loginRedirect;
      unset( $this->cookie->loginRedirect );
      return $uri;
   }

   protected function _displayLogin( $redirect = NULL )
   {
      if ( $redirect )
      {
         $this->_setLoginRedirect( $redirect );
      }
      $this->html->var[ 'content' ] = new Template( 'user_login', ['userLinks' => $this->_getUserLinks( '/user/login' ) ] );
   }

   protected function _createSecureLink( $uid, $uri )
   {
      $slink = new SecureLink();
      $slink->uid = $uid;
      $slink->time = $this->request->timestamp;
      $slink->code = \mt_rand();
      $slink->uri = $uri;
      $slink->add();
      return $slink;
   }

   protected function _getSecureLink( $uri )
   {
      $arr = \explode( '?', $uri );
      if ( \sizeof( $arr ) == 2 )
      {
         $l = new SecureLink();

         $l->uri = $arr[ 0 ];
         \parse_str( $arr[ 1 ], $get );

         if ( isset( $get[ 'r' ] ) && isset( $get[ 'u' ] ) && isset( $get[ 'c' ] ) && isset( $get[ 't' ] ) )
         {
            $l->id = $get[ 'r' ];
            $l->uid = $get[ 'u' ];
            $l->code = $get[ 'c' ];
            $l->time = $get[ 't' ];
            $l->load( 'id' );
            if ( $l->exists() )
            {
               return $l;
            }
         }
      }

      return NULL;
   }

   /*
    * create menu tree for root tags
    */

   protected function _createMenu( $tid )
   {
      $tag = new Tag( $tid, NULL );
      $tree = $tag->getTagTree();
      $type = 'tag';
      $root_id = \array_shift( \array_keys( $tag->getTagRoot() ) );
      if ( Tag::FORUM_ID == $root_id )
      {
         $type = 'forum';
      }
      else if ( Tag::YP_ID == $root_id )
      {
         $type = 'yp';
      }
      $liMenu = '';

      if ( \sizeof( $tree ) > 0 )
      {
         foreach ( $tree[ $tid ][ 'children' ] as $branch_id )
         {
            $branch = $tree[ $branch_id ];
            $liMenu .= '<li><a title="' . $branch[ 'name' ] . '" href="/' . $type . '/' . $branch[ 'id' ] . '">' . $branch[ 'name' ] . '</a>';
            if ( \sizeof( $branch[ 'children' ] ) )
            {
               $liMenu .= '<ul style="display: none;">';
               foreach ( $branch[ 'children' ] as $leaf_id )
               {
                  $leaf = $tree[ $leaf_id ];
                  $liMenu .= '<li><a title="' . $leaf[ 'name' ] . '" href="/' . $type . '/' . $leaf[ 'id' ] . '">' . $leaf[ 'name' ] . '</a></li>';
               }
               $liMenu .= '</ul>';
            }
            $liMenu .= '</li>';
         }
      }

      return $liMenu;
   }

   protected function _getPagerInfo( $nTotal, $nPerPage )
   {
      if ( $nPerPage <= 0 )
      {
         throw new \Exception( 'invalid value for number of items per page: ' . $nPerPage );
      }

      $pageNo = $this->request->get[ 'page' ] ? (int) $this->request->get[ 'page' ] : 1;
      $pageCount = $nTotal > 0 ? \ceil( $nTotal / $nPerPage ) : 1;
      if ( $pageNo < 1 || $pageNo > $pageCount )
      {
         $pageNo = $pageCount;
      }

      return [$pageNo, $pageCount ];
   }

   protected function _getUserLinks( $activeLink )
   {
      if ( $this->request->uid )
      {
         // user
         return $this->html->linkList( [
               '/user/' . $this->request->uid => '用户首页',
               '/pm/mailbox' => '站内短信',
               '/user/' . $this->request->uid . '/edit' => '编辑个人资料',
               '/password/' . $this->request->uid . '/change' => '更改密码'
               ], $activeLink
         );
      }
      else
      {
         // guest
         return $this->html->linkList( [
               '/user/login' => '登录',
               '/user/register' => '创建新帐号',
               '/password/forget' => '忘记密码',
               '/user/username' => '忘记用户名'
               ], $activeLink
         );
      }
   }

}

//__END_OF_FILE__
