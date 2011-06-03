<?php
// newadmin.php
// $Id: newadmin.php,v 1.8 2003/06/25 02:33:25 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// creates a new Siteframe administrator
// normally only used during site installation, but can be run at other times

include "siteframe.php";

if (!$NUM_ADMINS) {
  $PAGE->set_property('error','You do not currently have an administrative user defined. You should create at least one administrative user, or else you will not be able to maintain your website.');
}

if ($_POST['submitted']) {
    $u = new User($_POST['user_id']);
    $p1 = trim($_POST['password1']);
    $p2 = trim($_POST['password2']);
    $err = 0;
    if ($p1 == '') {
        $PAGE->set_property('error','The password cannot be blank<br/>');
        $err++;
    }
    else if ($p1 != $p2) {
        $PAGE->set_property('error','The passwords do not match<br/>');
        $err++;
    }
    else {
        $u->set_input_form_values($u->input_form_values());
        $u->set_property('user_status',USER_STATUS_ADMIN);
        $u->set_property('user_passwd',$p1);
        $u->set_property('register_confirm','');
    }
    if (!($u->errcount()+$err)) {
        $u->add();
        if (!$u->errcount() && (!$NUM_ADMINS)) {
            header('Location: ../login.php?redirect='.urlencode($SITE_PATH.'/admin/register.php'));
            exit;
        }
        else
            $PAGE->set_property('error',$u->get_errors(),true);
    }
    else {
        $PAGE->set_property('error',$u->get_errors(),true);
    }
}

if (!$u) $u = new User();

$PAGE->set_property('page_title','Create administrative user');
$PAGE->set_property('form_instructions','Use this form to create a new administrative user');
$PAGE->set_property('form_action','newadmin.php');
$f = array_merge(
        $u->input_form_values(),
        array(
            array(
                name => password1,
                prompt => 'Password',
                type => 'password',
                size => 250,
                doc => 'Select a password for the admin user'
            ),
            array(
                name => password2,
                prompt => 'Password (repeat to confirm)',
                type => 'password',
                size => 250,
                doc => 'Repeat the password to ensure that you typed it correctly'
            )
        ));
$PAGE->input_form(body,$f);

$PAGE->pparse('page');

?>
