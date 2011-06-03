<?php
// email.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: emailfolder.php,v 1.5 2003/06/07 01:27:23 glen Exp $
//
// Use this form to send a story or image via email

include "siteframe.php";
restricted();

$PAGE->set_property(page_title,'Email this folder');

$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];

if (!$id) {
    $PAGE->set_property(error,_NOID);
    $PAGE->set_property(body,'');
}
else if (!$CURUSER) {
    header("Location: login.php?redirect=".htmlentities(urlencode("$PHP_SELF?id=$id")));
    exit;
}
else if ($_POST['submitted']) {
    $class = foldertype($_POST['id']);
    if ($class == '')
        die("Invalid folder ID");
    $folder = new $class($_POST['id']);
    $PAGE->set_property('page_title',sprintf(_TITLE_EMAIL,$folder->get_property('folder_name')));
    $mail = new Email();
    $PAGE->set_array($folder->get_properties());
    $mail->set_array($PAGE->get_properties());
    $mail->set_property('email_from',
            sprintf('%s <%s>', $CURUSER->get_property('user_name'),
                $CURUSER->get_property('user_email')));
    $mail->add_address($CURUSER->get_property('user_email'),'cc');
    $mail->add_address($_POST['to'],'to');
    $mail->set_property('email_subject',$_POST['subject']);
    // generate body
    $PAGE->set_property('email_body',$_POST['body']);
    $PAGE->load_template('_email_ascii_',$TEMPLATES[ShareFolder][ascii]);
    $mail->set_property('email_ascii',$PAGE->parse('_email_ascii_'));
    if ($_POST['use_html']) {
        $PAGE->set_property('email_body',$_POST['body']);
        $PAGE->load_template('_email_html_',$TEMPLATES[ShareFolder][html]);
        $mail->set_property('email_html',$PAGE->parse('_email_html_'));
    }
    $mail->send();
    if (!$mail->errcount) {
        logmsg("Email sent from %s to %s",
            $CURUSER->get_property('user_name'),
            $_POST['to']);
        $PAGE->set_property(error,_MSG_MAILSENT);
        $PAGE->set_property(body,
            sprintf('<a href="%s/folder.php?id=%d">Click to return to folder</a>',
                $SITE_PATH,
                $folder->get_property('folder_id')));
        $num = $folder->get_property('folder_share_count');
        $folder->set_property('folder_share_count',$num+1);
        $folder->update();
    }
    else {
        logmsg("Mail was not sent");
        $PAGE->set_property(error, $mail->get_errors());
        $PAGE->set_property(body,'');
    }
}
else {
    $class = foldertype($id);
    if ($class == '')
        die("Invalid folder ID");
    $folder = new $class($id);
    $PAGE->set_property(page_title,sprintf(_TITLE_EMAIL,$folder->get_property('folder_name')));
    $mailform = array(
        array(name => id,
              type => hidden,
              value => $id),
        array(name => to,
              type => text,
              size => 250,
              prompt => "To"),
        array(name => subject,
              type => text,
              size => 250,
              prompt => _MSG_MAIL_SUBJECT),
        array(name => body,
              type => textarea,
              rows => 15,
              prompt => _MSG_MAIL_BODY),
        array(name => use_html,
              type => checkbox,
              rval => 1,
              value => 1,
              prompt => _MSG_MAIL_HTML)
    );
    $PAGE->set_property(form_name,'email');
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property(form_instructions,_MSG_EMAIL_INSTR,true);
    $PAGE->input_form(_mail_,$mailform);
    $PAGE->set_property(body,$PAGE->parse(_mail_));
}

$PAGE->pparse(page);
?>