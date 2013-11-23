<?php

namespace site;

// only controller will handle all exceptions and local languages
// other classes will report status to controller
// controller set status back the WebApp object
// WebApp object will call Theme to display the content
use lzx\core\Controller as LzxCtrler;
use lzx\html\Template;
use site\dataobject\User;
use site\dataobject\Tag;

/**
 *
 * @property \lzx\Core\Cache $cache
 * @property \lzx\core\Logger $logger
 * @property \lzx\html\Template $html
 * @property \lzx\core\Request $request
 * @property Array $path
 * @property \lzx\core\Session $session
 * @property \lzx\core\Cookie $cookie
 * @property \lzx\core\Config $config
 *
 */
abstract class Controller extends LzxCtrler
{

   public function __construct( $lang, $lang_path )
   {
      parent::__construct( $lang, $lang_path );

      if ( !\array_key_exists( $this->class, self::$l ) )
      {
         $lang_file = $lang_path . \str_replace( '\\', '/', $this->class ) . '.' . $lang . '.php';
         if ( \is_file( $lang_file ) )
         {
            include $lang_file;
         }
         self::$l[$this->class] = isset( $language ) ? $language : array( );
      }
   }

   public function run()
   {
      $this->updateAccessInfo();
      $this->checkNewPMCount();
      $this->setNavbar();
      $this->setTitle();
   }

   public function checkAJAX()
   {
      $action = 'ajax';
      $args = $this->request->args;

      // ajax request : /<controller>/ajax
      if ( \sizeof( $args ) > 1 && $args[1] == $action )
      {
         $ref_args = $this->request->getURIargs( $this->request->referer );
         if ( !\in_array( $args[0], array( $ref_args[0], 'file' ) ) )
         {
            $this->request->pageForbidden( $this->l( 'ajax_access_error' ) );
         }

         try
         {
            $return = $this->$action();
         }
         catch ( \Exception $e )
         {
            $this->logger->error( $e->getMessage() );
            $return['error'] = $this->l( 'ajax_excution_error' );
         }

         // set default response data type
         $type = $this->request->get['type'];
         if ( !\in_array( $type, array( 'json', 'html', 'text' ) ) )
         {
            $type = 'json';
         }

         if ( $type == 'json' )
         {
            $return = \json_encode( $return );
            if ( $return === FALSE )
            {
               $return = array( 'error' => $this->l( 'ajax_json_encode_error' ) );
               $return = \json_encode( $return );
            }
         }
         else
         {
            if ( \is_array( $return ) )
            {
               if ( \sizeof( $return ) == 1 && \array_key_exists( 'error', $return ) )
               {
                  $return = $this->l( 'Error' ) . ' : ' . $return['error'];
               }
            }

            if ( !\is_string( $return ) )
            {
               $return = $this->l( 'Error' ) . ' : ' . $this->l( 'ajax_data_type_error' );
            }
         }

         $this->request->pageExit( $return );
      }
   }

   public function updateAccessInfo()
   {
      $uid = $this->request->uid;
      if ( $uid > 0 )
      {
         $user = new User( $uid, 'lastAccessIPInt' );
         $user->lastAccessTime = $this->request->timestamp;
         $ip = (int) \ip2long( $this->request->ip );
         if ( $ip != $user->lastAccessIPInt )
         {
            $user->lastAccessIPInt = $ip;
            $user->update( 'lastAccessTime,lastAccessIPInt' );
            $this->cache->delete( 'authorPanel' . $uid );
         }
         else
         {
            $user->update( 'lastAccessTime' );
         }
      }
   }

   public function checkNewPMCount()
   {
      if ( $this->request->uid > 0 )
      {
         $user = new User();
         $user->uid = $this->request->uid;
         $pmCount = \intval( $user->getNewPrivMsgsCount() );
         $this->cookie->pmCount = $pmCount;
      }
   }

   public function setNavbar()
   {
      $navbar = $this->cache->fetch( 'page_navbar' );
      if ( $navbar === FALSE )
      {
         $vars = array(
            'forumMenu' => Tag::createMenu( 'forum' ),
            'ypMenu' => Tag::createMenu( 'yp' ),
            'uid' => $this->request->uid
         );
         $navbar = new Template( 'page_navbar', $vars );
         $this->cache->store( 'page_navbar', $navbar );
      }
      $this->html->var['page_navbar'] = $navbar;
   }

   public function setTitle()
   {
      $this->html->var['head_description'] = '休斯顿 华人, 黄页, 移民, 周末活动, 旅游, 单身 交友, Houston Chinese, 休斯敦, 休士頓';
      $this->html->var['head_title'] = '缤纷休斯顿华人网';
   }

}

//__END_OF_FILE__
