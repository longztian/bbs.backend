<?php declare(strict_types=1);

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $username
 * @property $password
 * @property $email
 * @property $wechat
 * @property $qq
 * @property $website
 * @property $firstname
 * @property $lastname
 * @property $sex
 * @property $birthday
 * @property $location
 * @property $occupation
 * @property $interests
 * @property $favoriteQuotation
 * @property $relationship
 * @property $signature
 * @property $createTime
 * @property $lastAccessTime
 * @property $lastAccessIP
 * @property $status
 * @property $timezone
 * @property $avatar
 * @property $type
 * @property $role
 * @property $badge
 * @property $points
 * @property $cid
 */
class User extends DBObject
{
    private $isSpammer = false;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'users';
        $fields = [
            'id' => 'id',
            'username' => 'username',
            'password' => 'password',
            'email' => 'email',
            'wechat' => 'wechat',
            'qq' => 'qq',
            'website' => 'website',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'sex' => 'sex',
            'birthday' => 'birthday',
            'location' => 'location',
            'occupation' => 'occupation',
            'interests' => 'interests',
            'favoriteQuotation' => 'favorite_quotation',
            'relationship' => 'relationship',
            'signature' => 'signature',
            'createTime' => 'create_time',
            'lastAccessTime' => 'last_access_time',
            'lastAccessIP' => 'last_access_ip',
            'status' => 'status',
            'timezone' => 'timezone',
            'avatar' => 'avatar',
            'type' => 'type',
            'role' => 'role',
            'badge' => 'badge',
            'points' => 'points',
            'cid' => 'cid'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }

    public function hashPW($password)
    {
        return \md5('Alex' . $password . 'Tian');
    }

    public function randomPW()
    {
        $chars = 'aABCdEeFfGHiKLMmNPRrSTWXY23456789@#$=';
        $salt = substr(str_shuffle($chars), 0, 3);
        return $salt . substr(str_shuffle($chars), 0, 7); // will send generated password to email
    }

    public function isSuperUser($uid, $cid)
    {
        return in_array($uid, [1]);
    }

    public function login($username, $password)
    {
        $this->username = $username;
        $this->load('id,status,password');
        if ($this->exists() && $this->status == 1) {
            return ($this->password === $this->hashPW($password));
        }

        return false;
    }

    public function loginWithEmail($email, $password)
    {
        $this->email = $email;
        $this->load('id,username,status,password');
        if ($this->exists() && $this->status == 1) {
            return ($this->password === $this->hashPW($password));
        }

        return false;
    }

    public function getUserGroup()
    {
        if ($this->id) {
            return array_column($this->call('get_user_group(' . $this->id . ')'), 'name');
        }
    }

    /*
     * delete nodes and return the node ids whose cache need to be deleted
     */

    public function delete()
    {
        if ($this->id > 1) {
            $this->call('delete_user(' . $this->id . ')');
        }
    }

    public function getAllNodeIDs()
    {
        return $this->id > 1 ? array_column($this->call('get_user_node_ids(' . $this->id . ')'), 'nid') : [];
    }

    public function getRecentNodes($forumRootID, $limit)
    {
        $fields = [
            'nid' => 'nid',
            'title' => 'title',
            'createTime' => 'create_time'
        ];

        return $this->convertFields($this->call('get_user_recent_nodes("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $this->id . ', 10)'), $fields);
    }

    public function getRecentComments($forumRootID, $limit)
    {
        $fields = [
            'nid' => 'nid',
            'title' => 'title',
            'createTime' => 'create_time'
        ];

        return $this->convertFields($this->call('get_user_recent_comments("' . implode(',', (new Tag($forumRootID, null))->getLeafTIDs()) . '", ' . $this->id . ', 10)'), $fields);
    }

    public function getPrivMsgsCount($mailbox = 'inbox')
    {
        if ($mailbox == 'new') {
            return intval(array_pop(array_pop($this->call('get_pm_count_new(' . $this->id . ')'))));
        } elseif ($mailbox == 'inbox') {
            return intval(array_pop(array_pop($this->call('get_pm_count_inbox(' . $this->id . ')'))));
        } elseif ($mailbox == 'sent') {
            return intval(array_pop(array_pop($this->call('get_pm_count_sent(' . $this->id . ')'))));
        } else {
            throw new \Exception('mailbox not found: ' . $mailbox);
        }
    }

    public function getPrivMsgs($type, $limit, $offset = 0)
    {
        $fields = [
            'mid' => 'msg_id',
            'body' => 'body',
            'uid' => 'uid',
            'user' => 'user',
            'time' => 'time'
        ];

        if ($type == 'sent') {
            return $this->convertFields($this->call('get_pm_list_sent_2(' . $this->id . ',' . $limit . ',' . $offset . ')'), $fields);
        } else {
            $fields['isNew'] = 'is_new';
            return $this->convertFields($this->call('get_pm_list_inbox_2(' . $this->id . ',' . $limit . ',' . $offset . ')'), $fields);
        }
    }

    public function validatePost($ip, $timestamp, $text, $title = null)
    {
        // CHECK USER
        if ($this->status != 1) {
            throw new \Exception('This user account cannot post message.');
        }

        $days = (int) (($timestamp - $this->createTime) / 86400);
        // registered less than 30 days
        if ($days < 30) {
            // check spams
            $spamwords = new SpamWord();
            $list = $spamwords->getList();

            if ($title) {
                $cleanTitle = preg_replace('/([^(\p{Nd}|\p{Han}|\p{Latin}) $]|\|)+/u', '', $title);

                foreach ($list as $w) {
                    if ($w['title']) {
                        if (mb_strpos($cleanTitle, $w['word']) !== false) {
                            // delete user
                            $this->isSpammer = true;
                            throw new \Exception('User is blocked! You cannot post any message!');
                        }
                    }
                }
            }

            $cleanBody = preg_replace('/([^(\p{Nd}|\p{Han}|\p{Latin}) $\r\n]|\|)+/u', '', $text);

            foreach ($list as $w) {
                if (mb_strpos($cleanBody, $w['word']) !== false) {
                    // delete user
                    $this->isSpammer = true;
                    throw new \Exception('User is blocked! You cannot post any message!');
                }
            }

            // still good?
            // not mark as spammer, but notify admin as a non-valid post, if there are too many noice charactors
            if ($title && mb_strlen($title) - mb_strlen($cleanTitle) > 4) {
                throw new \Exception('Title is not valid!');
            }

            $textLen = mb_strlen($text);
            if ($textLen > 35 && ($textLen - mb_strlen($cleanBody)) / $textLen > 0.4) {
                throw new \Exception('Body text is not valid!');
            }

            // check post counts
            if ($days < 10) {
                $geo = geoip_record_by_name(is_numeric($ip) ? \long2ip($ip) : $ip);
                // from Nanning
                if ($geo && $geo['city'] === 'Nanning') {
                    $this->isSpammer = true;
                    throw new \Exception('User is blocked! You cannot post any message!');
                }
                // not from Texas
                if (!$geo || $geo['region'] != 'TX') {
                    $oneday = (int) ($timestamp - 86400);
                    $count = array_pop(array_pop($this->call('get_user_post_count(' . $this->id . ',' . $oneday . ')')));
                    if ($count >= $days) {
                        throw new \Exception('Quota limitation reached for non-Texas user!<br>Your account is ' . $days . ' days old, so you can only post ' . $days . ' messages within 24 hours.<br>You already have ' . $count . ' message posted in last 24 hours. Please wait for several hours to get more quota.');
                    }
                }
            }
        }
    }

    public function isSpammer()
    {
        return $this->isSpammer;
    }

    public function getUserStat($timestamp, $cid)
    {
        $stats = array_pop($this->call('get_user_stat(' . strtotime(date("m/d/Y")) . ',' . $cid . ')'));

        $onlines = $this->call('get_user_online(' . $timestamp . ',' . $cid . ')');

        $users = [];
        $guestCount = 0;
        if (isset($onlines)) {
            foreach ($onlines as $u) {
                if ($u['uid'] > 0) {
                    $users[] = $u['username'];
                } else {
                    $guestCount++;
                }
            }
        }

        return [
            'userCount' => $stats['user_count_total'],
            'userTodayCount' => $stats['user_count_recent'],
            'latestUser' => $stats['latest_user'],
            'onlineUsers' => implode(', ', $users),
            'onlineUserCount' => sizeof($users),
            'onlineGuestCount' => $guestCount,
            'onlineCount' => sizeof($users) + $guestCount
        ];
    }

    public function addBookmark($nid)
    {
        $this->call('bookmark_add(' . $this->id . ',' . $nid . ')');
    }

    public function deleteBookmark($nid)
    {
        $this->call('bookmark_delete(' . $this->id . ',' . $nid . ')');
    }

    public function listBookmark($limit, $offset)
    {
        return $this->call('bookmark_list(' . $this->id . ',' . $limit . ',' . $offset . ')');
    }

    public function countBookmark()
    {
        return array_pop(array_pop($this->call('bookmark_count(' . $this->id . ')')));
    }
}
