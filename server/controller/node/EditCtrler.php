<?php

namespace site\controller\node;

use site\controller\Node;
use lzx\html\Template;
use site\dbobject\Node as NodeObject;
use site\dbobject\NodeYellowPage;
use site\dbobject\Image;

class EditCtrler extends Node
{

   public function run()
   {
      $this->cache->setStatus( FALSE );
      list($nid, $type) = $this->_getNodeType();
      $method = '_edit' . $type;
      $this->$method( $nid );
   }

   private function _editForumTopic( $nid )
   {
      // edit existing comment
      $node = new NodeObject( $nid, 'uid,status' );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( \strlen( $this->request->post[ 'body' ] ) < 5 || \strlen( $this->request->post[ 'title' ] ) < 5 )
      {
         $this->error( 'Topic title or body is too short.' );
      }

      if ( $this->request->uid != 1 && $this->request->uid != $node->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      $node->title = $this->request->post[ 'title' ];
      $node->body = $this->request->post[ 'body' ];
      $node->lastModifiedTime = $this->request->timestamp;

      try
      {
         $node->update();
      }
      catch (\Exception $e)
      {
         $this->error( $e->getMessage(), TRUE );
      }

      $files = \is_array( $this->request->post[ 'files' ] ) ? $this->request->post[ 'files' ] : [ ];
      $file = new Image();
      $file->updateFileList( $files, $this->config->path[ 'file' ], $nid );
      $this->cache->delete( 'imageSlider' );

      $this->cache->delete( '/node/' . $nid );

      $this->request->redirect( $this->request->referer );
      // refresh node content cache
   }

   private function _editYellowPage( $nid )
   {
      if ( $this->request->uid != self::ADMIN_UID )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      if ( empty( $this->request->post ) )
      {
         // display edit interface
         $nodeObj = new NodeObject();
         $contents = $nodeObj->getYellowPageNode( $nid );

         $this->html->var[ 'content' ] = new Template( 'editor_bbcode_yp', $contents );
      }
      else
      {
         // save modification
         $node = new NodeObject( $nid, 'tid' );
         if ( $node->exists() )
         {
            $node->title = $this->request->post[ 'title' ];
            $node->body = $this->request->post[ 'body' ];
            $node->lastModifiedTime = $this->request->timestamp;
            $node->update();

            $node_yp = new NodeYellowPage( $nid, 'nid' );
            $keys = ['address', 'phone', 'email', 'website', 'fax' ];
            foreach ( $keys as $k )
            {
               $node_yp->$k = \strlen( $this->request->post[ $k ] ) ? $this->request->post[ $k ] : NULL;
            }

            $node_yp->update();
         }

         $files = \is_array( $this->request->post[ 'files' ] ) ? $this->request->post[ 'files' ] : [ ];
         $file = new Image();
         $file->updateFileList( $files, $this->config->path[ 'file' ], $nid );

         $this->cache->delete( '/node/' . $nid );
         $this->cache->delete( '/yp/' . $node->tid );

         $this->request->redirect( '/node/' . $nid );
      }
      // refresh node content cache
   }

}

//__END_OF_FILE__