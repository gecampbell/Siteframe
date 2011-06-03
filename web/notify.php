<?php
// notifications.php
// $Id: notify.php,v 1.1 2003/06/21 00:58:11 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this page generates the popup window for online notifications

require "siteframe.php";

if (!$CURUSER)
  siteframe_abort('This page cannot be opened by a user who is not logged in');

// if we receive the "Acknowledge", then clear everything and close the window
if ($_POST['submitted']) {
  $arr = fcn_active_notifications($CURUSER->get_property('user_id'));
  foreach($arr as $row) {
    $note = new subscrNotification($row['note_id']);
    $note->acknowledge();
  }
  printf('<html><body><script language="javascript">self.close();</script></body></html>');
  exit;
}

// the notifications template generates the whole page; no theme
$PAGE->load_template('page',$TEMPLATES[Notify][online]);
$PAGE->set_property('page_title',_TITLE_NOTIFY);
$PAGE->pparse('page');
?>