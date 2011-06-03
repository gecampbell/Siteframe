<?php
/* register.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: edituser.php,v 1.19 2004/09/09 22:47:05 glen Exp $
**
** This page is used to register new users to the system. It creates a new
** user object and inserts it into the database. The global property
** edit_MODEL controls the registration process. It can have one of these
** possible values:
**
**   open - any information is provided, and the user is accepted immediately
**   confirm - register the user, but hold the registration pending email
**      confirmation. In this situation, the registration is accepted, but
**     the user_status variable contains the value "0" until the validation
**      email is received and the correct code is entered on the login/validate
**      page (login.php).
**   closed (actually, any other value) - no new registrations are accepted.
*/
include "siteframe.php";
restricted();

$PAGE->set_property('page_title',_TITLE_USEREDIT);

if ($_GET['id']) {
    $edit_user_id = $_GET['id'];
    $u = new User($edit_user_id);
}
else if ($_POST['submitted']) {
    $edit_user_id = $_POST['edit_user_id'];
    $edit_password1 = $_POST['edit_password1'];
    $u = new User($_POST['edit_user_id']);
    $u->set_input_form_values($u->input_form_values(),'edit_');
    if (isadmin())
        $u->set_property(user_status,$_POST['edit_user_status']);
    if (isadmin() || ($edit_password1 != '')) {
        $u->update($edit_password1);
        if ($u->errcount())
            $PAGE->set_property(error,$u->get_errors(),true);
        else
            $PAGE->set_property(error,_MSG_USEREDITED);
    }
    else
        $PAGE->set_property(error,_ERR_NOPASSWORD);
}
else {
    $PAGE->set_property(error,_ERR_NOUSER,true);
}

// establish input form

if (iseditor($edit_user_id)) {
    $PAGE->set_property(form_name,'edituser');
    $PAGE->set_property(form_action,$_SERVER['PHP_SELF']);
    $PAGE->set_property(form_instructions,_EDITUSER_INSTR);
    $a = $u->input_form_values();
    $a[] = array(name => 'password1',
                 prompt => _PROMPT_OLDPASSWORD,
                 type => 'password',
                 size => 250);
    if (isadmin()) {
        $a[] = array(name => user_status,
                     prompt => _PROMPT_USER_STATUS,
                     type => select,
                     options => array( USER_STATUS_HOLD => 'Pending',
                                       USER_STATUS_NORMAL => 'Normal',
                                       USER_STATUS_ADMIN => 'Administrator'),
                     value => $u->get_property(user_status));
    }
    $PAGE->input_form(body,$a,'edit_');
}
else {
    $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
    $PAGE->set_property(body,'');
}

// display output page
$PAGE->pparse('page');

?>
