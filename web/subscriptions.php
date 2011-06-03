<?php
// subscriptions.php
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// allows users to view and edit subscriptions

require "siteframe.php";

$PAGE->set_property('page_title','Subscriptions');
if (!$CURUSER) {
  $PAGE->set_property('error',_ERR_SUBSCR_NOTLOGGEDIN);
  $PAGE->pparse('page');
  exit;
}

// require the "id=" parameter (user ID)
if (!$_GET['id']) {
  $PAGE->set_property('error',_ERR_SUBSCR_NO_ID);
  $PAGE->pparse('page');
  exit;
}

if (($CURUSER->get_property('user_id') == $_GET['id']) ||
    isadmin())
  ; // ok
else {
  $PAGE->set_property('error',_ERR_SUBSCR_NOTAUTH);
  $PAGE->pparse('page');
  exit;
}

// handle deletions
if ($_POST['submitted']) {
  if (count($_POST['remove']))
    foreach($_POST['remove'] as $id) {
      $sub = new Subscription($id);
      $sub->delete();
    }
}

$PAGE->set_property('subscr_user_id',$_GET['id']);
$PAGE->load_template('_subscr_',$TEMPLATES[Subscriptions]);
$PAGE->set_property('body',$PAGE->parse('_subscr_'));
$PAGE->pparse('page');

?>
