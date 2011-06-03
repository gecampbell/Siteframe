<?php
// hourly.php: hourly batch job
// $Id: hourly.php,v 1.7 2003/06/25 19:22:33 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this performs three functions:
// 1) it delivers current hourly notifications
// 2) if the time==SUBSCRIPTION_NOTIFY_TIME, it delivers daily notifications
// 3) if the day==SUBSCRIPTION_NOTIFY_DAY && (2), it delivers weekly notifications

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

message('%s:starting hourly process',$SITE_NAME);

// check to see if we're going to do daily & weekly runs
$RUN_DAILY = (date('H')==$SUBSCRIPTION_NOTIFY_TIME);
$RUN_WEEKLY = $RUN_DAILY && (date('w')==$SUBSCRIPTION_NOTIFY_DAY);

$SUBSCR_OWNERS = <<<ENDSUBSCROWNERS
SELECT DISTINCT subscr_owner_id
FROM
  notifications INNER JOIN subscriptions
    ON note_subscr_id=subscr_id
WHERE
  note_sent IS NULL AND
  subscr_notify_frequency='%s' AND
  subscr_notify_type='E'
ENDSUBSCROWNERS;

$NOTES = <<<ENDNOTES
SELECT note_id
FROM
  notifications INNER JOIN subscriptions
    ON note_subscr_id=subscr_id
WHERE
  note_sent IS NULL AND
  subscr_notify_frequency='%s' AND
  subscr_notify_type='E' AND
  note_user_id=%d
ORDER BY
  subscr_created
ENDNOTES;

// define the autoblock for frequency
$AUTOBLOCK['subscr_notifications'] = 'fcn_subscr_notifications';
function fcn_subscr_notifications($arg) {
  global $DB,$NOTES,$FREQ;
  $q = sprintf($NOTES,$FREQ,$arg);
  $r = $DB->read($q);
  if (!$r)
    siteframe_abort('error in fcn_subscr_notifications(%s),freq=%s',$arg,$FREQ);
  while(list($id) = $DB->fetch_array($r)) {
    $note = new subscrNotification($id);
    $out[] = $note->get_properties();
  }
  return $out;
}

// this generates all the e-mails for each user
function gen_notify($frequency) {
  global $FREQ,$PAGE,$SUBSCR_NOTIFY_FREQUENCIES,$SUBSCR_OWNERS,$PAGE,
         $SITE_NAME,$SITE_EMAIL,$DB,$TEMPLATES;

  // global variable required by autoblock
  $FREQ = $frequency;

  message('%s notifications',$SUBSCR_NOTIFY_FREQUENCIES[$FREQ]);

  // generate template for each user
  $PAGE->set_property('notify_frequency_display',
    $SUBSCR_NOTIFY_FREQUENCIES[$FREQ]);
  $r = $DB->read(sprintf($SUBSCR_OWNERS,$FREQ));
  if (!$r)
    siteframe_abort('error in SQL_HOUR: %s',$DB->error());
  while(list($uid) = $DB->fetch_array($r)) {
    ++$count;
    $USER = new User($uid);
    $PAGE->load_template('ascii',$TEMPLATES[Notify][ascii],TRUE);
    $PAGE->load_template('html',$TEMPLATES[Notify][html],TRUE);
    $PAGE->set_array($USER->get_properties());
    message('user %s',$USER->get_property('user_name'));
    $PAGE->set_property('user_id',$uid);
    $message = new Email();
    $message->set_property('email_from',sprintf('%s <%s>',$SITE_NAME,$SITE_EMAIL));
    $message->set_property('email_subject',$SITE_NAME.' notifications');
    $message->set_property('email_to',$USER->get_property('user_email'));
    $message->set_property('email_ascii',$PAGE->parse('ascii'));
    if (!$USER->get_property('no_html_email'))
      $message->set_property('email_html',$PAGE->parse('html'));
    $message->send();
    // clear notifications
    $arr = fcn_active_notifications($USER->get_property('user_id'));
    foreach($arr as $row) {
      $note = new subscrNotification($row['note_id']);
      $note->acknowledge();
    }
  }
  message('%d user(s) processed',$count);
}

// HOURLY NOTIFICATIONS---------------------------------------------------

gen_notify('H');


// DAILY NOTIFICATIONS----------------------------------------------------

if ($RUN_DAILY) {

  gen_notify('D');

} // if RUN_DAILY

// WEEKLY NOTIFICATIONS---------------------------------------------------

if ($RUN_WEEKLY) {

  gen_notify('W');

} // if RUN_WEEKLY

?>
