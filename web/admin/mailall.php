<?php
// mailto.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
// $Id: mailall.php,v 1.9 2004/09/28 20:05:47 glen Exp $

require "siteframe.php";
restricted();
$PAGE->set_property(page_title,sprintf(_TITLE_MAILTO,"All Users"));

if (!$CURUSER) {
    header("Location: login.php?redirect=".htmlentities(urlencode("$PHP_SELF?id=$id")));
    exit;
}
else if (!isadmin()) {
    siteframe_abort("This page is restricted to site administrators");
}
else if ($_POST['submitted']) {
    ini_set(max_execution_time,0); // allow all time to execute
    $mail = new Email();
    $mail->set_property('email_from',
        sprintf("%s <%s>",$SITE_NAME,$SITE_EMAIL));
    $mail->set_property('email_subject',stripslashes($subject));
    $mail->set_property('email_ascii',stripslashes($body));
    $mail->add_address($SITE_EMAIL,'to');
    $r = $DB->read("SELECT user_email FROM users");
    while(list($em) = $DB->fetch_array($r)) {
        $mail->add_address($em,'bcc');
    }
    $mail->send();
    logmsg("Mail sent from %s to %s",
        $CURUSER->get_property(user_name),
        "All Users");
    $PAGE->set_property(error,_MSG_MAILSENT);
    $PAGE->set_property(body,$bcc);
}
else {
    $mailform = array(
        array(name => subject,
              type => text,
              size => 250,
              prompt => _MSG_MAIL_SUBJECT),
        array(name => body,
              type => textarea,
              rows => 15,
              prompt => _MSG_MAIL_BODY)
    );
    $PAGE->set_property(form_name,'mail');
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property(form_instructions,_MSG_MAIL_INSTR);
    $PAGE->set_property(form_instructions,
        ' Please be aware that sending email to all site users may '.
        ' take several minutes to complete; do not cancel this '.
        ' in the middle.', true);
    $PAGE->input_form(_mail_,$mailform,'','Send');
    $PAGE->set_property(body,$PAGE->parse(_mail_));
}

$PAGE->pparse(page);

?>
