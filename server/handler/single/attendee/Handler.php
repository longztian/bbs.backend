<?php declare(strict_types=1);

namespace site\handler\single\attendee;

use site\handler\single\Single;
use lzx\html\Template;
use site\dbobject\FFAttendee;
use site\dbobject\FFQuestion;

/**
 * @property \lzx\db\DB $db database object
 */
class Handler extends Single
{
    // private attendee info
    public function run()
    {
        $uid = (int) $this->request->get['u'];
        // not a user request
        if ($uid == 0) {
            // login first
            if (!$this->session->loginStatus) {
                $this->displayLogin();
                return;
            }
        } else {
            //verify user's access code
            $code = $this->request->get['c'];

            $act = array_pop($this->db->query('CALL get_latest_single_activity()'));
            $atd = new FFAttendee();
            $atd->aid = (int) $act['id'];
            $atd->id = $uid;
            $atd->status = 1;
            $atd->load('id');

            if (!$atd->exists() || $code != $this->getCode($uid)) {
                $this->pageForbidden();
            }
        }

        // logged in or from a valid user link
        if (true) {//$this->request->timestamp < strtotime("09/16/2013 22:00:00 CDT"))
            $act = array_pop($this->db->query('CALL get_latest_single_activity()'));
            $atd = new FFAttendee();
            $atd->aid = (int) $act['id'];
            $atd->status = 1;

            $confirmed_groups = [[], []];
            $atd->where('checkin', 0, '>');
            $atd->order('checkin');
            $question = new FFQuestion();
            foreach ($atd->getList('id,name,sex,email,info') as $attendee) {
                $question->aid = $attendee['id'];

                $attendee['questions'] = array_slice(array_column($question->getList('body'), 'body'), -3);
                array_walk($attendee['questions'], function (&$q) {
                    $q = ' - ' . $q;
                });
                $confirmed_groups[(int) $attendee['sex']][] = $attendee;
            }

            $this->var['content'] = new Template('attendees', ['groups' => $confirmed_groups]);
        } else {
            $this->var['content'] = "<p>ERROR: The page you request is not available anymore</p>";
        }
    }
}