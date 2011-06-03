<?php
// contact.php
// $Id: contact.php,v 1.2 2003/06/22 06:04:21 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All right reserved.
// see LICENSE.txt for details.
//
// this simple script allows users to send a message to the
// site operations (using the SITE_EMAIL address)

require "siteframe.php";

$PAGE->set_property('page_title','Contact');

if ($_POST['submitted']) {

  if (trim($_POST['name']=='')) {
    $PAGE->set_property('error','The name field cannot be blank');
  }
  else {
    foreach($_POST as $name => $value) {
      if ($name!='submitted')
        $msg .= sprintf("[%s]\n%s\n\n",$name,$value);
    }
    mail($SITE_EMAIL,'Contact Message',$msg);
    $PAGE->set_property('error','Your message has been sent');
    logmsg('Contact message from %s',$_POST['name']);
  }
}

$form = array(
  array(
    name => 'name',
    type => 'text',
    size => 250,
    prompt => 'Your name',
    value => $CURUSER ? $CURUSER->title() : $_POST['name'],
    doc => 'Enter your name in the space provided'
  ),
  array(
    name => 'email',
    type => 'text',
    size => 250,
    prompt => 'E-mail address',
    value => $CURUSER ? $CURUSER->get_property('user_email') : $_POST['email'],
    doc => '(optional) You can provide an e-mail address so that the site operators can contact you if necessary.'
  ),
  array(
    name => 'message',
    type => 'textarea',
    rows => 15,
    value => $_POST['body'],
    prompt => 'Your message',
    doc => 'Enter your message and press Send when complete'
  )
);

$instr = <<<ENDINSTR
<p>This page is provided as a means for contacting the site operator(s).
Enter your name, e-mail address (optional), and message, then press Send.
Your message will be delivered via e-mail to the site administrator.</p>
ENDINSTR;

$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->set_property('form_instructions',$instr);
$PAGE->input_form('body',$form,'','Send');
$PAGE->pparse('page');

?>
