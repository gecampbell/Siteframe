<?php
// index.php
// $Id: index.php,v 1.24 2003/06/22 02:54:50 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// Main page for Siteframe.

$page_start = microtime();
include "siteframe.php";

// check for optional command-line variables
if ($_GET['category'])
  $PAGE->set_property('category',$_GET['category']+0);

// if the site name is not specified, then the site has not been
// configured properly
if ($SITE_NAME=='')
  header("Location: admin/globals.php");

$PAGE->set_property('page_title',$SITE_NAME);

if ($HOME_PAGE!='') {
  $PAGE->set_property('_index_', parse_text($PAGE->get_template_body($HOME_PAGE)));
}
else {
  $PAGE->load_template('_index_',$TEMPLATES[Index]);
}

// handle online notifications (popups!!)
if ($SUBSCRIPTION_ENABLE && $CURUSER) {
  $q = sprintf('SELECT COUNT(*) FROM notifications LEFT OUTER JOIN subscriptions '.
               'ON note_subscr_id=subscr_id '.
               'WHERE note_user_id=%d AND note_sent IS NULL',
        $CURUSER->get_property('user_id'));
  $r = $DB->read($q);
  if (!$r)
    siteframe_abort('unexpected error in index.php: %s',$DB->error());
  list($num_notes) = $DB->fetch_array($r);
  $PAGE->set_property('num_notifications',$num_notes);
  if ($num_notes) {
    $index = $PAGE->get_property('page');
    $index = preg_replace(
      '/<body/i',
      sprintf('<body onload="javascript:window.open(\'%s/notify.php\',\'notify\',\'width=%d,height=%d,toolbar=no,scrollbars=yes,resizable=yes\');" ',
        $SITE_PATH,
        $NOTIFY_WIDTH ? $NOTIFY_WIDTH : 300,
        $NOTIFY_HEIGHT ? $NOTIFY_HEIGHT : 200),
      $index
    );
    $PAGE->set_property('page',$index);
    if ($SUBSCR_NAV_NOTIFY!='0') {
      $nav = $PAGE->get_property('navigation');
      $msg = sprintf(_MSG_NOTE_WAITING,$num_notes,$CURUSER->get_property('user_id'));
      if ($SUBSCR_NAV_NOTIFY=='top')
        $nav = $msg . "\n" . $nav;
      else
        $nav = $nav . "\n" . $msg;
      $PAGE->set_property('navigation',$nav);
    }
  }
}

$PAGE->set_property('index',$PAGE->parse('_index_'));
$PAGE->set_property('body',$PAGE->parse('index'));
$PAGE->pparse('page');
/*
logmsg("DEBUG:start=%s,end=%s",$page_start,microtime());
logmsg("DEBUG:num_reads=%d,num_writes=%d",$DB->num_reads,$DB->num_writes);
printf('<!-- reads(%s) -->',print_r($DB->reads,TRUE));
*/
?>
