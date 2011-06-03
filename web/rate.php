<?php
// rate.php
// $Id: rate.php,v 1.10 2003/06/24 02:30:19 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// set ratings
// this file is intended to be called from other scripts as a POST
// the variable "return_location" should be set to indicate where to
// return after a successful update. If the update is not successful, 
// this script will halt and display an error message.

include "siteframe.php";

$PAGE->set_property('page_title','Rating');

if (!$CURUSER) {
  $PAGE->set_property('error','You are not logged in');
  $PAGE->pparse('page');
  exit;
}

if (!$_POST['id']) {
  $PAGE->set_property('error','Invalid rating request');
  $PAGE->pparse('page');
  exit;
}

$comment = new Comment(0,$_POST['id']);
$comment->set_property('rating',$_POST['rating']);
$comment->set_property('comment_owner_id',$CURUSER->get_property('user_id'));
$comment->add();

if (!$comment->errcount()) {
  header(sprintf("Location: %s",$_POST['return_location']));
}
else {
  $PAGE->set_property('error',$comment->get_errors());
  $PAGE->pparse('page');
}

?>