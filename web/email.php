<?php
// email.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: email.php,v 1.19 2003/06/07 01:27:23 glen Exp $
//
// Use this form to send a story or image via email

include "siteframe.php";
restricted();

$PAGE->set_property(page_title,'Email this document');

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
    $class = doctype($_POST['id']);
    if ($class == '')
        die("Invalid document ID");
    $doc = new $class($_POST['id']);
    $PAGE->set_property('page_title',sprintf(_TITLE_EMAIL,$doc->get_property(doc_title)));
    $mail = new Email();
    $PAGE->set_array($doc->get_properties());
    $mail->set_array($PAGE->get_properties());
    $mail->set_property('email_from',
            sprintf('%s <%s>', $CURUSER->get_property('user_name'),
                $CURUSER->get_property('user_email')));
    $mail->add_address($CURUSER->get_property('user_email'),'cc');
    $mail->add_address($_POST['to'],'to');
    $mail->set_property('email_subject',$_POST['subject']);
    // generate body
    $PAGE->set_property('email_body',$_POST['body']);
    $PAGE->load_template('_email_ascii_',$TEMPLATES[Share][ascii]);
    $mail->set_property('email_ascii',$PAGE->parse('_email_ascii_'));
    if ($_POST['use_html']) {
        $PAGE->set_property('email_body',$_POST['body']);
        $PAGE->load_template('_email_html_',$TEMPLATES[Share][html]);
        $mail->set_property('email_html',$PAGE->parse('_email_html_'));
    }
    $mail->send();
    if (!$mail->errcount) {
        logmsg("Email sent from %s to %s",
            $CURUSER->get_property('user_name'),
            $_POST['to']);
        $PAGE->set_property(error,_MSG_MAILSENT);
        $PAGE->set_property(body,
            sprintf('<a href="%s/document.php?id=%d">Click to return to document</a>',
                $SITE_PATH,
                $doc->get_property('doc_id')));
        $num = $doc->get_property('doc_share_count');
        $doc->set_property('doc_share_count',$num+1);
        $doc->update();
    }
    else {
        logmsg("Mail was not sent");
        $PAGE->set_property(error, $mail->get_errors());
        $PAGE->set_property(body,'');
    }
}
else {
    $class = doctype($id);
    if ($class == '')
        die("Invalid document ID");
    $doc = new $class($id);
    $PAGE->set_property(page_title,sprintf(_TITLE_EMAIL,$doc->get_property(doc_title)));
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
    if ($class == "Image") {
        $picture = sprintf('<img src="%s" alt="%s" border="0"/><br/>',
                    $doc->get_property('doc_file_200'),
                    $doc->get_property('doc_title'));
        $PAGE->set_property(form_instructions,$picture);
    }
    $PAGE->set_property(form_instructions,_MSG_EMAIL_INSTR,true);
    $PAGE->input_form(_mail_,$mailform);
    $PAGE->set_property(body,$PAGE->parse(_mail_));
}

$PAGE->pparse(page);
?>