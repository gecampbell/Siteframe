<?php
// mailto.php
// $Id: mailto.php,v 1.19 2004/07/23 05:16:43 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this script allows a user to send e-mail to another user or group

include "siteframe.php";
restricted();

$id = ($_GET['id']) ? $_GET['id'] : $_POST['id'];
$group = ($_GET['group']) ? $_GET['group'] : $_POST['group'];

if (!($id||$group)) {
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body,'');
}
else if (!$CURUSER) {
    header("Location: login.php?redirect=".htmlentities(urlencode("$PHP_SELF?id=$id")));
    exit;
}
else if ((!$id) && (!ismember($group))) {
    $PAGE->set_property('error','You are not a member of that group');
    $PAGE->pparse('page');
    exit;
}
else if ($_POST['submitted']) {
    if ($_POST['id']) {
        $u = new User($_POST['id']);
        $PAGE->set_property(page_title,sprintf(_TITLE_MAILTO,$u->get_property(user_name)));
    }
    else {
        $g = new Group($_POST['group']);
        $PAGE->set_property(page_title,sprintf(_TITLE_MAILTO,$g->get_property(group_name)));
    }
    /*
    $rc = mail($u->get_property(user_email),
        stripslashes($subject),
        sprintf("%s\n\n%s\n$SITE_URL/user.php?id=%d\n\n_____\nMessage sent from $SITE_NAME ($SITE_URL)",
            stripslashes($body),
            $CURUSER->get_property(user_name),
            $CURUSER->get_property(user_id)),
        sprintf("From: %s <%s>",
            $CURUSER->get_property(user_name),
            $CURUSER->get_property(user_email)));
    */

    $PAGE->load_template('_email_ascii_',$TEMPLATES[Mailto][ascii]);
    $PAGE->load_template('_email_html_',$TEMPLATES[Mailto][html]);
    $PAGE->set_property('email_body',stripslashes($_POST['body']));
    $mail = new Email();
    $mail->set_property('email_from',
        sprintf("%s <%s>",
            $CURUSER->get_property('user_name'),
            $CURUSER->get_property('user_email')));
    if ($_POST['id']) {
        $mail->add_address($u->get_property('user_email'),'bcc');
        if (!$u->get_property('no_html_email')) {
            $PAGE->set_property('email_body',wordwrap(nl2br(stripslashes($_POST['body']))));
            $mail->set_property('email_html',$PAGE->parse('_email_html_'));
        }
    }
    else {
        foreach($g->get_members() as $uid) {
            $u = new User($uid);
            $mail->add_address($u->get_property('user_email'),'bcc');
        }
        // only send ASCII e-mail to group members
        $PAGE->set_property('email_body',wordwrap(nl2br(stripslashes($_POST['body']))));
    }
    $mail->add_address($CURUSER->get_property('user_email'),'bcc');
    $mail->set_property('email_subject',stripslashes($_POST['subject']));
    $mail->set_property('email_ascii',$PAGE->parse('_email_ascii_'));
    $mail->send();
    if (!$mail->errcount) {
        logmsg("Mail sent from %s to %s",
            $CURUSER->get_property(user_name),
            $_POST['id'] ? $u->get_property(user_name) : $g->get_property('group_name'));
        $PAGE->set_property(error,_MSG_MAILSENT);
    }
    else {
        logmsg("Mail was not sent");
        $PAGE->set_property(error, $mail->get_errors());
    }
    if ($_POST['id']) {
        $PAGE->set_property('body',
            sprintf('<p><a href="%s/user.php?id=%d">Click to return to user</a></p>',
                $SITE_PATH,
                $u->get_property('user_id')));
    }
    else {
        $PAGE->set_property('body',
            sprintf('<p><a href="%s/group.php?id=%d">Click to return to group</a></p>',
                $SITE_PATH,
                $g->get_property('group_id')));
    }
}
else {
    if ($id) {
        $u = new User($id);
        $PAGE->set_property(page_title,sprintf(_TITLE_MAILTO,$u->get_property(user_name)));
    }
    else {
        $g = new Group($group);
        $PAGE->set_property(page_title,sprintf(_TITLE_MAILTO,$g->get_property(group_name)));
    }
    $mailform = array(
        array(name => id,
              type => hidden,
              value => $id),
        array(name => group,
              type => hidden,
              value => $group),
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
    $PAGE->input_form(_mail_,$mailform);
    $PAGE->set_property(body,$PAGE->parse(_mail_));
}

$PAGE->pparse(page);

?>
