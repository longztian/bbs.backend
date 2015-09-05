<?php

//!!!
//!!!  do not edit, generated by script/build_route.sh
//!!!

namespace site;

use site\ControllerFactory;

/**
 * Description of ControllerRouter
 *
 * @author ikki
 */
class ControllerRouter extends ControllerFactory
{

   protected static $_route = [
      'activity'                =>  'site\\controller\\activity\\ActivityCtrler',
      'ad'                      =>  'site\\controller\\ad\\ADCtrler',
      'app/ad'                  =>  'site\\controller\\app\\ADCtrler',
      'app/cache'               =>  'site\\controller\\app\\CacheCtrler',
      'app/user'                =>  'site\\controller\\app\\UserCtrler',
      'comment'                 =>  'site\\controller\\comment\\CommentCtrler',
      'comment/delete'          =>  'site\\controller\\comment\\DeleteCtrler',
      'comment/edit'            =>  'site\\controller\\comment\\EditCtrler',
      'forum'                   =>  'site\\controller\\forum\\ForumCtrler',
      'forum/node'              =>  'site\\controller\\forum\\NodeCtrler',
      'help'                    =>  'site\\controller\\help\\HelpCtrler',
      'home'                    =>  'site\\controller\\home\\HomeCtrler',
      'iostat'                  =>  'site\\controller\\iostat\\IOStatCtrler',
      'lottery'                 =>  'site\\controller\\lottery\\LotteryCtrler',
      'lottery/prize'           =>  'site\\controller\\lottery\\PrizeCtrler',
      'lottery/rank'            =>  'site\\controller\\lottery\\RankCtrler',
      'lottery/start'           =>  'site\\controller\\lottery\\StartCtrler',
      'lottery/try'             =>  'site\\controller\\lottery\\TryCtrler',
      'node'                    =>  'site\\controller\\node\\NodeCtrler',
      'node/activity'           =>  'site\\controller\\node\\ActivityCtrler',
      'node/bookmark'           =>  'site\\controller\\node\\BookmarkCtrler',
      'node/comment'            =>  'site\\controller\\node\\CommentCtrler',
      'node/delete'             =>  'site\\controller\\node\\DeleteCtrler',
      'node/edit'               =>  'site\\controller\\node\\EditCtrler',
      'node/tag'                =>  'site\\controller\\node\\TagCtrler',
      'phpinfo'                 =>  'site\\controller\\phpinfo\\PHPInfoCtrler',
      'schools'                 =>  'site\\controller\\schools\\SchoolsCtrler',
      'search'                  =>  'site\\controller\\search\\SearchCtrler',
      'sendmail'                =>  'site\\controller\\sendmail\\SendMailCtrler',
      'single'                  =>  'site\\controller\\single\\SingleCtrler',
      'single/activities'       =>  'site\\controller\\single\\ActivitiesCtrler',
      'single/ajax'             =>  'site\\controller\\single\\AJAXCtrler',
      'single/attendee'         =>  'site\\controller\\single\\AttendeeCtrler',
      'single/checkin'          =>  'site\\controller\\single\\CheckinCtrler',
      'single/info'             =>  'site\\controller\\single\\InfoCtrler',
      'single/list'             =>  'site\\controller\\single\\ListCtrler',
      'single/login'            =>  'site\\controller\\single\\LoginCtrler',
      'single/logout'           =>  'site\\controller\\single\\LogoutCtrler',
      'term'                    =>  'site\\controller\\term\\TermCtrler',
      'weather'                 =>  'site\\controller\\weather\\WeatherCtrler',
      'wedding'                 =>  'site\\controller\\wedding\\WeddingCtrler',
      'wedding/add'             =>  'site\\controller\\wedding\\AddCtrler',
      'wedding/checkin'         =>  'site\\controller\\wedding\\CheckinCtrler',
      'wedding/edit'            =>  'site\\controller\\wedding\\EditCtrler',
      'wedding/gift'            =>  'site\\controller\\wedding\\GiftCtrler',
      'wedding/join'            =>  'site\\controller\\wedding\\JoinCtrler',
      'wedding/listall'         =>  'site\\controller\\wedding\\ListAllCtrler',
      'wedding/login'           =>  'site\\controller\\wedding\\LoginCtrler',
      'wedding/logout'          =>  'site\\controller\\wedding\\LogoutCtrler',
      'yp'                      =>  'site\\controller\\yp\\YPCtrler',
      'yp/join'                 =>  'site\\controller\\yp\\JoinCtrler',
      'yp/node'                 =>  'site\\controller\\yp\\NodeCtrler',
      'api/ad'                  =>  'site\\api\\AdAPI',
      'api/adpayment'           =>  'site\\api\\AdPaymentAPI',
      'api/authentication'      =>  'site\\api\\AuthenticationAPI',
      'api/bookmark'            =>  'site\\api\\BookmarkAPI',
      'api/bug'                 =>  'site\\api\\BugAPI',
      'api/cache'               =>  'site\\api\\CacheAPI',
      'api/captcha'             =>  'site\\api\\CaptchaAPI',
      'api/file'                =>  'site\\api\\FileAPI',
      'api/identificationcode'  =>  'site\\api\\IdentificationCodeAPI',
      'api/message'             =>  'site\\api\\MessageAPI',
      'api/stat'                =>  'site\\api\\StatAPI',
      'api/user'                =>  'site\\api\\UserAPI',
      'api/viewcount'           =>  'site\\api\\ViewCountAPI',
   ];

}

//__END_OF_FILE__
