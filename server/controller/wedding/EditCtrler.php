<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller\wedding;

use site\controller\Wedding;
use lzx\html\Template;
use site\dbobject\Wedding as WeddingAttendee;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class EditCtrler extends Wedding
{

   public function run()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->_displayLogin();
         return;
      }

      $this->html->var[ 'navbar' ] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      if ( $this->request->post )
      {
         // save changes for one guest
         foreach ( $this->request->post as $k => $v )
         {
            $a->$k = $v;
         }
         $a->update();
         $this->html->var[ 'body' ] = $a->name . '的更新信息已经被保存';
      }
      else
      {
         if ( $this->args && (int) $this->args[ 0 ] > 0 )
         {
            // edit one guest
            $a->id = $this->args[ 0 ];
            $this->html->var[ 'body' ] = new Template( 'edit', \array_pop( $a->getList() ) );
         }
         else
         {
            // all guests in a list;
            $a->order( 'tid' );
            $this->html->var[ 'body' ] = new Template( 'edit_list', ['attendees' => $a->getList( 'name' ) ] );
         }
      }
   }

}