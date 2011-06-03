<?php
// feedback.php
// $Id: feedback.php,v 1.2 2003/06/05 05:37:35 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this page allows the site administrator to submit feedback on Siteframe

require "siteframe.php";

$PAGE->set_property('page_title','Feedback');

$form = array(
    array(
        name => 'feedback',
        type => 'textarea',
        prompt => 'Comments',
        doc => 'Enter your comments in the text box.'
    ),
    array(
        name => 'user_name',
        type => 'text',
        size => 250,
        value => $CURUSER->get_property('user_name'),
        prompt => 'Your name',
        doc => 'Not required, but helpful.'
    ),
    array(
        name => 'sender',
        type => 'text',
        size => 250,
        value => $CURUSER->get_property('user_email'),
        prompt => 'E-mail address',
        doc => 'We will use this only to contact you about this feedback. '.
               'It will not be sold or used for any other purpose without '.
               'your permission.'
    ),
    array(
        name => 'siteframe_version',
        type => 'hidden',
        value => SITEFRAME_VERSION
    ),
    array(
        name => 'date',
        type => 'hidden',
        value => date('Y-m-d H:iT')
    ),
);

if ($_POST['submitted']) {
    foreach($_POST as $name => $value)
        if ($name != 'submitted')
            $msg .= sprintf("[%s] %s\n",$name,$value);
    mail('feedback@siteframe.org,'.$SITE_EMAIL,
        'Siteframe Feedback',
        $msg,
        'From: '.$SITE_EMAIL);
    $PAGE->set_property('error','Your message has been sent');
}

$PAGE->set_property('form_instructions',
    'You can use this form to submit feedback on Siteframe to the developers. '.
    'Feedback can include bug reports, enhancement requests, tips and tricks '.
    'you\'ve discovered, or merely gratuitous praise. This form will generate '.
    'an e-mail and will send it to <b>feedback@siteframe.org</b>; it will also '.
    'send a copy to <b>'.$SITE_EMAIL.'</b>. '.
    'Add your comments in the box and press <b>Submit</b>.');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('body',$form,'','Send feedback');

$PAGE->pparse('page');
?>
