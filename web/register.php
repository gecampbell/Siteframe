<?php
// register.php
// $Id: register.php,v 1.15 2006/12/07 10:47:18 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This page is used to register new users to the system. It creates a new
// user object and inserts it into the database. The global property
// REGISTER_MODEL controls the registration process. It can have one of these
// possible values:
//
//   open - any information is provided, and the user is accepted immediately
//   confirm - register the user, but hold the registration pending email
//      confirmation. In this situation, the registration is accepted, but
//      the user_status variable contains the value "0" until the validation
//      email is received and the correct code is entered on the login/validate
//      page (login.php).
//   closed (actually, any other value) - no new registrations are accepted.

include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_REGISTER);

switch($REGISTER_MODEL) {
case 'open':
    $default_status = USER_STATUS_NORMAL;
    break;
case 'confirm':
    $default_status = USER_STATUS_HOLD;
    break;
case 'closed':
    if (!isadmin())
        die("Sorry; registration is not permitted");
}
$u = new User();

if (($default_status!=USER_STATUS_NORMAL)&&($default_status!=USER_STATUS_HOLD)) {
    $PAGE->set_property(error,_ERR_NOREGISTER);
    $PAGE->set_property(body,'');
}
else {
    if ($_POST['submitted']) {
		// validate captcha
		if ($REGISTER_CAPTCHA && (strtolower($_POST[register_captcha]) != strtolower($REGISTER_CAPTCHA)))
			$u->add_error('Sorry, you must be a bot');
        // validate passwords
        if ($_POST[register_password1] != $_POST[register_password2]) {
            $u->add_error(_ERR_NOPASSWORDMATCH);
        }
        else {
            $u->set_input_form_values($u->input_form_values(),'register_');
            $u->set_property('user_passwd',$_POST[register_password1]);
            $u->set_property('user_status',$default_status);
        }
        // set user status code based on REGISTER_MODEL global property
        // create errors, if any
        $PAGE->set_property('error',$u->get_errors());

        // if no errors, add the user
        if ($u->errcount() == 0) {
            // everything ok, we can add the user
            $u->add();
            if ($u->errcount() == 0) {
                if ($REGISTER_MODEL == 'confirm') {
                    $PAGE->set_property('error',_MSG_CONFIRM);
                    mail($u->get_property(user_email),
                         _REGISTER_EMAIL_SUBJECT,
                         sprintf(_REGISTER_EMAIL_MSG,
                            $u->get_property(register_confirm),
                            $u->get_property(user_id)),
                         "From: $SITE_NAME <$SITE_EMAIL>");
                    logmsg(sprintf("Registered: %s, awaiting confirmation",
                            $u->get_property(user_email)));
                }
                else {
                    $PAGE->set_property('error',_MSG_USERADDED);
                    logmsg(sprintf("Registered: %s",$u->get_property(user_email)));
                }
            }
            else {
                $PAGE->set_property('error',$u->get_errors());
                logmsg(sprintf("Registration failure: %s",$register_user_email));
            }
        }
    }
    else {
        $PAGE->set_property('error','');
    }

    // define input form
    $PAGE->set_property(form_name,"register");
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property('form_instructions',_REGISTER_INSTR .
        ($REGISTER_MODEL=='confirm' ? _REGISTER_INSTR_CONFIRM : ''));
    $a = $u->input_form_values();
	if ($REGISTER_CAPTCHA)
	{
		$CAPTCHA = '';
		for($i=0; $i<strlen($REGISTER_CAPTCHA); $i++)
			$CAPTCHA .= '&#'.ord(substr($REGISTER_CAPTCHA, $i, 1)).';';
		$a[] = array('name' => 'captcha',
					 'prompt' => 'Verification',
					 'type' => 'text',
					 'size' => 250,
					 'doc' => "To confirm that you are not a computer, please enter <i>$CAPTCHA</i> in this field",
		);
	}
    $a[] = array(name => 'password1',
                 prompt => _REGISTER_PASSWORD,
                 type => 'password',
                 help => 'password',
                 size => 250);
    $a[] = array(name => 'password2',
                 prompt => _REGISTER_PASSWORD2,
                 type => 'password',
                 size => 250);
    $PAGE->input_form(body,$a,'register_');
}
$PAGE->pparse(page);

?>
