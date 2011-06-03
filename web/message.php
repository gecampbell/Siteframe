<?php
// message.php
// $Id: message.php,v 1.1 2003/06/21 23:40:29 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this little file allows a user to post a notification message
// to another user

require "siteframe.php";

$PAGE->set_property('page_title','Online Message');

// must be logged in
if (!$CURUSER) {
  $PAGE->set_property('error','You must be logged in to use this page');
  $PAGE->pparse('page');
  exit;
}
// handle submitted forms
else if ($_POST['submitted']) {
  $receiver = new User($_POST['id']);
  if (!$receiver->get_property('user_id')) {
    $PAGE->set_property('error','Invalid, erroneous user ID');
    $PAGE->pparse('page');
    exit;
  }
  $note = new subscrNotification();
  $note->set_property('note_user_id',$_POST['id']);
  $note->set_property('note_message',$_POST['subject']);
  $note->set_property('note_body',$_POST['body']);
  $note->set_property('note_from',$CURUSER->title());
  $note->set_property('note_from_id',$CURUSER->get_property('user_id'));
  $note->add();
  if ($note->errcount())
    $PAGE->set_property('error',$note->get_errors());
  else {
    $PAGE->set_property('error','Your message has been sent. The recipient will receive it the next time he or she is online.');
    $PAGE->pparse('page');
    exit;
  }
}
// error if no ID
else if (!$_GET['id']) {
  $PAGE->set_property('error','No user ID specified');
  $PAGE->pparse('page');
  exit;
}
// start fresh
else {
  $receiver = new User($_GET['id']);
  if (!$receiver->get_property('user_id')) {
    $PAGE->set_property('error','No user with that ID');
    $PAGE->pparse('page');
    exit;
  }
}

$PAGE->set_property('page_title','Message to '.$receiver->title());

$form = array(
  array(
    name => 'id',
    type => 'hidden',
    value => $receiver->get_property('user_id')
  ),
  array(
    name => 'subject',
    type => 'text',
    size => 250,
    value => $note ? $note->get_property('note_message') : '',
    prompt => 'Subject',
    doc => 'Enter a catchy subject line for your message'
  ),
  array(
    name => 'body',
    type => 'textarea',
    rows => 10,
    value => $note ? $note->get_property('note_body') : '',
    prompt => 'Message',
    doc => 'Enter the text of your message here'
  ),
);

$instructions = <<<ENDINSTRUCTIONS
<p>Enter the subject line and your message below, then press <b>Send</b>.
The message will be delivered to the recipient the next time they are
online.</p>
ENDINSTRUCTIONS;
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->set_property('form_instructions',$instructions);
$PAGE->input_form('body',$form,'','Send');

$PAGE->pparse('page');

?>
