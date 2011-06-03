<?php
// notifications.php
// $Id: notifications.php,v 1.3 2003/06/21 06:58:16 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this page generates a list of notifications

require "siteframe.php";

if (!$CURUSER) {
  $PAGE->set_property('error','You must be logged in to access this page');
  $PAGE->pparse('page');
  exit;
}

// require the "id=" parameter (user ID)
if (!$_GET['id']) {
  $PAGE->set_property('error',_ERR_NOTE_NO_ID);
  $PAGE->pparse('page');
  exit;
}

// verify that the user is permitted to view/edit notifications
if (($CURUSER->get_property('user_id') == $_GET['id']) ||
    isadmin())
  $PAGE->set_property('user_user_id',$_GET['id']);
else {
  $PAGE->set_property('error',_ERR_NOTE_NOTAUTH);
  $PAGE->pparse('page');
  exit;
}

// process a submitted form
if ($_POST['submitted']) {
  if (count($_POST['remove']))
    foreach($_POST['remove'] as $id) {
      $note = new subscrNotification($id);
      $note->delete();
      if ($note->errcount())
        $PAGE->set_property('error',$note->get_errors(),TRUE);
    }
}

// the notifications template generates the whole page; no theme
$PAGE->load_template('_notifications_',$TEMPLATES[Notifications]);
$PAGE->set_property('body',$PAGE->parse('_notifications_'));
$PAGE->set_property('page_title',_TITLE_NOTIFICATIONS);
$PAGE->pparse('page');
?>