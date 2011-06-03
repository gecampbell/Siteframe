<?php
// daily.php: daily batch job
// $Id: daily.php,v 1.41 2007/09/10 19:43:37 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this script is intended to be run once per day, from the
// command line. It performs the following functions:
// 1. if MIN_RATING is set, it deletes images that meet
//    the deletion criteria
// 2. it deletes unconfirmed users more than 4 days old
// 3. for other unconfirmed users, it sends a warning message
// 4. it checks for new items on the site and, if REPORT_DAYS
//    have passed, generates the site's newsletter
// 5. it deletes expired notices
// 6. it deletes expired advertisements
// 7. it clears the log
require "siteframe.php";

// can we tell if we're running in a browser?
// let's test the server browser string; it's not set when
// running from the command line
if (isset($_SERVER['HTTP_USER_AGENT']))
  die('Sorry: this script cannot be run from a browser');

function message($s,$a='',$b='',$c='',$d='',$e='',$f='') {
  global $SITE_NAME;
  $s = strtolower($s);
  logmsg($s,$a,$b,$c,$d,$e,$f);
  printf('%s:'.$s."\n",date('YmdHis'),$a,$b,$c,$d,$e,$f);
}

message('%s:starting daily process',$SITE_NAME);
set_time_limit(0);

// delete unvalidated users after four days

message('Checking for unvalidated users to delete');
set_time_limit(0);
$r = $DB->read("SELECT user_id FROM users WHERE user_status=0 AND ".
               "user_created<DATE_SUB(NOW(),INTERVAL 4 DAY)");
while(list($uid) = $DB->fetch_array($r)) {
    $user = new User($uid);
    $confirm = trim(str_replace('done','',$user->get_property(register_confirm)));
    if ($confirm!='') {
        $mail = new Email();
        $mail->set_property('email_from',
            sprintf("%s <%s>", $SITE_NAME, $SITE_EMAIL));
        $mail->add_address($user->get_property('user_email'),'to');
        $mail->set_property('email_subject',"$SITE_NAME unconfirmed account deleted");
        $mail->set_property('email_ascii',
            wordwrap("Because your registration at $SITE_NAME has not been confirmed, ".
                "your account has been deleted.\n",64));
        $mail->send();
        message("deleting unconfirmed user %s",$user->get_property('user_name'));
        $user->delete();
    }
}

// warning message to unconfirmed users

$WARNING = <<<ENDWARNING
Your account has not yet been validated at $SITE_NAME.
If it is not confirmed shortly, it will be deleted.
To confirm your account, click on the following link:

$SITE_URL/login.php?confirm=%s&id=%d

(Depending upon your email program, you may need to cut the link
out and paste it into your browser.)

Sent from: $SITE_NAME ($SITE_URL)
ENDWARNING;
message('Sending warning message to unconfirmed users');
$r = $DB->read("SELECT user_id FROM users WHERE user_status=0 AND ".
               "user_created<DATE_SUB(NOW(),INTERVAL 1 DAY)");
while(list($uid) = $DB->fetch_array($r)) {
    $user = new User($uid);
    if ($user->get_property(register_confirm)!='') {
        /*
        mail($user->get_property('user_email'),
            "$SITE_NAME unconfirmed account deletion",
            sprintf($WARNING,
                $user->get_property('register_confirm'),
                $user->get_property('user_id')),
            sprintf("From: %s <%s>",$SITE_NAME,$SITE_EMAIL));
        */
        $mail = new Email();
        $mail->set_property('email_from',
            sprintf("%s <%s>",$SITE_NAME,$SITE_EMAIL));
        $mail->add_address($user->get_property('user_email'),'to');
        $mail->set_property('email_subject',"$SITE_NAME unconfirmed account deletion");
        $mail->set_property('email_ascii',
            wordwrap(sprintf($WARNING,
                $user->get_property('register_confirm'),
                $user->get_property('user_id'))));
        $mail->send();
        message("Warning message to unconfrmed user id=%d, %s",
            $user->get_property('user_id'),
            $user->get_property('user_name'));
    }
}

// determine the number of new users, documents, and folders on the site

$q = sprintf("SELECT COUNT(*) FROM users WHERE user_created>DATE_SUB(NOW(),INTERVAL %d DAY)",
      $REPORT_DAYS);
$r = $DB->read($q);
list($num) = $DB->fetch_array($r);

$total = $num;

$q = sprintf("SELECT COUNT(*) FROM docs WHERE doc_hidden=0 AND ".
             " doc_created>DATE_SUB(NOW(),INTERVAL %d DAY)",
      $REPORT_DAYS);
$r = $DB->read($q);
list($num) = $DB->fetch_array($r);

$total += $num;

$q = sprintf("SELECT COUNT(*) FROM folders WHERE created>DATE_SUB(NOW(),INTERVAL %d DAY)",
      $REPORT_DAYS);
$r = $DB->read($q);
list($num) = $DB->fetch_array($r);

$total += $num;

set_time_limit(0);
// has it been at least REPORT_DAYS since the last newsletter?
// global property LAST_REPORT_TIME contains date (unixtime) of last run
$secsperday = 60*60*24;
// we'll gie it an hour of overlap
$report_days_secs = ($REPORT_DAYS * $secsperday) - (60*60);
$time_to_run = (time() - $LAST_REPORT_TIME) > $report_days_secs;

if ($REPORT_DAYS && $time_to_run && $total) {
    message("There are %d new items",$total);
    message("Mailing report");
    $PAGE->load_template('_report_',$TEMPLATES[Daily][ascii]);
    $PAGE->load_template('_report_html_',$TEMPLATES[Daily][html]);
    $PAGE->set_property('report_days',$REPORT_DAYS);
    
    if ($SITE_NEWSLETTER_EMAIL)
    {
        $mail = new Email();
        $mail->set_property('email_from',sprintf("%s <%s>",$SITE_NAME,$SITE_EMAIL));
        $mail->set_property('email_subject', "$SITE_NAME Newsletter");
        $mail->set_property('email_html',$PAGE->parse('_report_html_'));
        $mail->add_address($SITE_NEWSLETTER_EMAIL,'to');
        $mail->send();
    }
    else
    {
        $r = $DB->read("SELECT user_id FROM users WHERE user_status!=0 ORDER BY user_email");
        while(list($uid) = $DB->fetch_array($r)) {
            $user = new User($uid);
            if ($user->get_property('user_subscribe')) {
                $mail = new Email();
                $mail->set_property('email_from',sprintf("%s <%s>",$SITE_NAME,$SITE_EMAIL));
                $mail->add_address($user->get_property('user_email'),'to');
                $mail->set_property('email_subject', "$SITE_NAME Newsletter");
                $mail->set_property('email_ascii',$PAGE->parse('_report_'));
                if (!$user->get_property('no_html_email')) {
                    $mail->set_property('email_html',$PAGE->parse('_report_html_'));
                }
                $mail->send();
            }
        }
    }
    set_global('LAST_REPORT_TIME',time());
}

// delete old (older than 7 days) notices
$q = "SELECT * FROM docs WHERE doc_type='Notice'";
$r = $DB->read($q);
$cutoff = time() - (7*24*60*60); // today minus 7 days
while($dbrow = $DB->fetch_array($r)) {
  $notice = new Notice(0,$dbrow);
  if (strtotime($notice->get_property('notice_end_date')) < $cutoff) {
    message("Deleting Notice %s",$notice->get_property('doc_title'));
    $notice->delete();
  }
}

// delete old classifieds
$q = "SELECT * FROM docs WHERE doc_type='Ad'";
$r = $DB->read($q);
while($dbrow = $DB->fetch_array($r)) {
  $advert = new Ad(0,$dbrow);
  if (strtotime($advert->get_property('notice_end_date')) < time()) {
    message("Deleting Ad %s",$advert->get_property('doc_title'));
    $advert->delete();
  }
}

set_time_limit(0);
// clear the log
$q = sprintf(
      "DELETE FROM activity WHERE event_date<DATE_SUB(NOW(),INTERVAL %d DAY)",
      $LOG_DAYS ? $LOG_DAYS : 7);
$DB->write($q);
message("Trimmed the activity log %s",$DB->error());
$q = sprintf(
      "DELETE FROM sessions WHERE session_date<DATE_SUB(NOW(),INTERVAL %d DAY)",
      $LOG_DAYS ? $LOG_DAYS : 7);
$DB->write($q);
message("Trimmed the session log %s",$DB->error());

// generate user statistics
if ($USER_STATISTICS) {
  message("Computing user statistics");
  require "update_user.php";
}

// delete subscription notifications older than 14 days, whether delivered or not
message('Deleting old subscription notifications');
$DB->write('DELETE FROM notifications WHERE note_created<DATE_SUB(NOW(),INTERVAL 14 DAY)');
message('%d notification(s) deleted',$DB->affected_rows());
message('Deleting sent subscription notifications');
$DB->write('DELETE FROM notifications WHERE note_sent<DATE_SUB(NOW(),INTERVAL 1 DAY)');
print mysql_error();
message('%s notification(s) deleted',$DB->affected_rows());

// if MIN_RATING is set, then delete all rated images older
// than 60 days with an average rating less than MIN_RATING
$DOC_DELETED = <<<ENDEMAIL
Your image, "%s", has been deleted from $SITE_NAME because it has
not achieved the minimum rating of %.2f required for retention longer
than 60 days. Thanks very much for your continued participation
at $SITE_NAME, and we hope to see more submissions from you
in the future.
ENDEMAIL;
if ($MIN_RATING) {
    message("Deleting documents rated less than $MIN_RATING");
    $r = $DB->read("SELECT doc_id FROM docs WHERE doc_created < DATE_SUB(NOW(),INTERVAL 60 DAY)");
    while (list($id) = $DB->fetch_array($r)) {
        $class = doctype($id);
if (trim($class)=='')
  siteframe_abort('Oops - doc ID %d has problems',$id);
        $doc = new $class($id);
        $a = $doc->get_properties();
        if (($a[doc_rating_count] > 1) &&
            ($a[doc_rating] < $MIN_RATING)) {
            $mail = new Email();
            $mail->add_address($a['doc_user_email'],'to');
            $mail->set_property('email_from',
                sprintf("%s <%s>", $SITE_NAME, $SITE_EMAIL));
            $mail->set_property('email_subject',
                sprintf('Document %s deleted',$doc->get_property('doc_title')));
            $mail->set_property('email_ascii',
                wordwrap(sprintf($DOC_DELETED,
                            $doc->get_property('doc_title'),
                            $MIN_RATING), 64, "\n"));
            $user = new User($a['doc_user_id']);
            $mail->add_address($user->get_property('user_email'),'to');
            $mail->send();
            message('Deleting document "%s"',$doc->get_property(doc_title));
            $doc->delete();
        }
    }
    message("Finished deleting documents");
}

message("%s:Finished daily process",$SITE_NAME);
?>
