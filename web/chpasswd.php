<?php
// chpasswd.php
// $Id: chpasswd.php,v 1.6 2003/06/07 01:27:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
//
// change the user's password

include "siteframe.php";

$PAGE->set_property(page_title,'Change Password');
$PAGE->set_property(doc_id,0);
$PAGE->set_property(doc_folder_id,0);

if ($_POST['submitted'] && $CURUSER) {
    $old = $_POST['oldpasswd'];
    $new1 = $_POST['newpasswd1'];
    $new2 = $_POST['newpasswd2'];
    if ($new1 != $new2)
        $PAGE->set_property(error,'New passwords do not match');
    else if ($new1 == '')
        $PAGE->set_property(error,'New password cannot be blank');
    else if (substr($new1,-3,3) == '0x0')
        $PAGE->set_property(error,'Invalid password; please choose a different one');
    else {
        $q = sprintf("SELECT user_id FROM users ".
                     "WHERE user_id=%d AND user_passwd=%s('%s')",
                     $CURUSER->get_property('user_id'),
                     $ENCRYPTION,
                     $old);
        $r = $DB->read($q);
        list($usid) = $DB->fetch_array($r);
        if ($usid != $CURUSER->get_property('user_id'))
            $PAGE->set_property('Sorry, that was not a correct password');
        else {
            $q = sprintf("UPDATE users SET user_passwd=%s('%s') ".
                         "WHERE user_id=%d AND user_passwd=%s('%s')",
                         $ENCRYPTION,
                         $new1,
                         $usid,
                         $ENCRYPTION,
                         $old);
            $r = $DB->write($q);
            if ($DB->affected_rows()!=1)
                $PAGE->set_property('error','Your password was not changed; it is possible that your new password is not different from the old one.');
            else {
                $PAGE->set_property('error','Your password has been changed; <a href="login.php">click here to login with your new password</a>');
                setcookie(COOKIENAME);
            }
        }
    }
    $PAGE->set_property(body,'');
}
else if (!$CURUSER) {
    $PAGE->set_property('error','You must be logged in to change your password');
}
else {
    if ($_GET['change'] == 'yes')
        $PAGE->set_property('error','You are required to change your password');
    $f = array(
            array(name => oldpasswd,
                  type => 'password',
                  size => 250,
                  doc => "Enter your login password here",
                  prompt => _LOGIN_PASSWORD),
            array(name => newpasswd1,
                  type => 'password',
                  size => 250,
                  doc => "Enter your desired new password here",
                  prompt => _PROMPT_NEWPASSWORD),
            array(name => newpasswd2,
                  type => 'password',
                  size => 250,
                  doc => "Repeat your new password for confirmation",
                  prompt => 'Repeat '._PROMPT_NEWPASSWORD)
         );
    $PAGE->set_property(form_name,'reminder');
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property(form_instructions,
        'Enter your existing password plus your new password twice (for confirmation).');
    $PAGE->set_property(input_form_hidden,'');
    $PAGE->input_form(body,$f);
}

$PAGE->pparse(page);

?>
