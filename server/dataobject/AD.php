<?php

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\MySQL;

/**
 * @property $id
 * @property $name
 * @property $type_id
 * @property $exp_time
 * @property $email
 */
class AD extends DataObject
{

   public function __construct( $load_id = null, $fields = '' )
   {
      $db = MySQL::getInstance();
      parent::__construct( $db, 'ads', $load_id, $fields );
   }

   public function getAllAds( $from_time = 0 )
   {
      $where = $from_time > 0 ? ('WHERE exp_time > ' . $from_time) : '';
      return $this->_db->select( 'SELECT * FROM ads ' . $where . ' ORDER BY exp_time' );
   }

   public function getAllAdPayment( $from_time = 0 )
   {
      $where = 'WHERE adp.id in ( SELECT max(adp.id) from ad_payments adp JOIN ads on adp.ad_id = ads.id ' . ( $from_time > 0 ? ('WHERE ads.exp_time > ' . $from_time) : '' )  . ' GROUP BY ad_id )';
      return $this->_db->select( 'SELECT adp.id, ads.name, adp.amount, adp.time AS pay_time, ads.exp_time, adp.comment FROM ad_payments adp LEFT JOIN ads ON adp.ad_id = ads.id ' . $where . ' ORDER BY adp.time DESC' );
   }

}

?>