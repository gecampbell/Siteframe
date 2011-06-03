<?php
// reset.php
// $Id: reset.php,v 1.6 2004/09/14 13:47:09 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// assigns a new password and emails it

include "siteframe.php";

$PAGE->set_property(page_title,_TITLE_REMINDER);
$PAGE->set_property(doc_id,0);
$PAGE->set_property(doc_folder_id,0);

if ($_POST['submitted']) {
    $email = $_POST['email'];
    $r = $DB->read("SELECT user_id FROM users WHERE user_email='$email'");
    list($uid) = $DB->fetch_array($r);
    if (!$uid) {
        $PAGE->set_property(error,_ERR_NOEMAIL);
    }
    else {
        $u = new User($uid);
        $newpasswd = $u->get_property('user_firstname');
        $newpasswd = str_replace(' ','-',$newpasswd);
        $newpasswd = str_replace('e','3',$newpasswd);
        $newpasswd = str_replace('a','4',$newpasswd);
        $newpasswd = str_replace('g','G',$newpasswd);
        $newpasswd = sprintf("%s%04d0x0",$newpasswd,rand(1000,9999));
        $q = sprintf("UPDATE users SET user_passwd=%s('%s') WHERE user_id=%d",
                $ENCRYPTION,
                $newpasswd,
                $uid);
        $DB->write($q);
        mail($u->get_property(user_email),
             _REMINDER_EMAIL_SUBJ,
             sprintf(_REMINDER_EMAIL,$newpasswd),
             'From: '.$SITE_NAME.' <'.$SITE_EMAIL.'>');
        $PAGE->set_property(error,_MSG_REMINDED);
    }
    $PAGE->set_property(body,'');
}
else {
    $f = array(
            array(name => email,
                  type => text,
                  size => 250,
                  prompt => _PROMPT_USER_EMAIL)
         );
    $PAGE->set_property(form_name,'reminder');
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property(form_instructions,_REMINDER_INSTR);
    $PAGE->set_property(input_form_hidden,'');
    $PAGE->input_form(body,$f);
}

$PAGE->pparse(page);
?>