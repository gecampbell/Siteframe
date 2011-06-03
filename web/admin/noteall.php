<?php
// noteall.php
// $Id: noteall.php,v 1.1 2003/06/23 00:09:11 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this little file allows the administrator to send an online
// note to all the site users

require "siteframe.php";

$PAGE->set_property('page_title','Online Message to All Users');

// must be logged in
if (!$CURUSER) {
  $PAGE->set_property('error','You must be logged in to use this page');
  $PAGE->pparse('page');
  exit;
}
// handle submitted forms
else if ($_POST['submitted']) {
  $note = new subscrNotification();
  $note->set_property('note_message',$_POST['subject']);
  $note->set_property('note_body',$_POST['body']);
  $note->set_property('note_from',$CURUSER->title());
  $note->set_property('note_from_id',$CURUSER->get_property('user_id'));

  // all users
  $r = $DB->read('SELECT user_id FROM users');
  while(list($id) = $DB->fetch_array($r)) {
    $note->set_property('note_user_id',$id);
    $note->add();
  }
  if ($note->errcount())
    $PAGE->set_property('error',$note->get_errors());
  else {
    $PAGE->set_property('error','Your message has been sent. The recipients will receive it the next time they are online.');
    $PAGE->pparse('page');
    exit;
  }
}

$form = array(
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
The message will be delivered to the recipients the next time they are
online.</p>
ENDINSTRUCTIONS;
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->set_property('form_instructions',$instructions);
$PAGE->input_form('body',$form,'','Send');

$PAGE->pparse('page');

?>
