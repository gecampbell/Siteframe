<?php
/* login.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: login.php,v 1.25 2004/05/17 19:43:47 glen Exp $
**
** Performs login function
*/
$ADMIN_PAGE = true; // keeps login from being shut down in MAINTENANCE_MODE
include "siteframe.php";

$PAGE->set_property('page_title',_TITLE_LOGIN);
$PAGE->set_property('error','');
$PAGE->set_property('doc_id',0);
$PAGE->set_property('doc_folder_id',0);

setcookie(COOKIENAME);  // log out before anything else
setcookie('SESSION');   // also session cookie

$id = $_GET['id'];
$confirm = $_GET['confirm'];

if ($id) { // registration confirmation validation
    $u = new User($id);
    if ($u->get_property(register_confirm) == $confirm) {
        $u->set_property(register_confirm,'');
        $u->set_property(user_status,USER_STATUS_NORMAL); // valid now
        $u->update();
        logmsg(sprintf("Confirmed: id=%d, %s",$u->get_property(user_id),
                $u->get_property(user_name)));
        $status = _MSG_CONFIRMED;
    }
    else if ($u->get_property(register_confirm) == 'done') {
        $status = _MSG_ALREADYCONFIRMED;
    }
    else {
        logmsg(sprintf("Confirmation failure: id=%d",$id));
        $status = _ERR_NOCONFIRM;
    }
    $PAGE->set_property('error',$status);
}
else if ($_POST['submitted']) { // regular login
    $email = $_POST['login_user_email'];
    $login_password = $_POST['login_password'];
    $r = $DB->read("SELECT user_id,user_status FROM users WHERE user_email='$email' ".
                    "  AND user_passwd=${ENCRYPTION}('$login_password')");
    list($uid,$ustatus) = $DB->fetch_array($r);
    $found = $DB->num_rows($r);
    if ($found && (!$ALLOW_UNCONFIRMED) && ($ustatus == USER_STATUS_HOLD)) {
        $status = _ERR_LOGIN_UNCONFIRMED;
    }
    else if ($uid > 0) {
        $u = new User($uid);
        $cookie = $u->get_property('user_cookie');
        if ($_POST['login_remember']) {
            if (!$COOKIE_DAYS)
                $COOKIE_DAYS=1;
            //setcookie(COOKIENAME,$cookie,time()+(60*60*24*$COOKIE_DAYS),$SITE_PATH);
            setcookie(COOKIENAME,$cookie,time()+(60*60*24*$COOKIE_DAYS));
        }
        else {
            // setcookie(COOKIENAME,$cookie,0,$SITE_PATH);
            setcookie(COOKIENAME,$cookie,0);
        }
        logmsg(sprintf("Login: id=%d, %s",$u->get_property(user_id),
                                      $u->get_property(user_name)));
        $u->set_property('user_last_login',date('Y-m-d H:i'));
        $u->update();
        if (substr($login_password,-3,3)=='0x0') {
            header("Location: ${SITE_PATH}/chpasswd.php?change=yes");
            exit;
        }
        else if ($_POST['login_redirect']!='') {
            //$new_location = ereg_replace('.*/','',$_POST['login_redirect']);
            //header("Location: $new_location");
            header('Location: '.$_POST['login_redirect']);
            exit;
        }
        else {
            header("Location: ".$SITE_PATH."/");
        }
    }
    else {
        $status = _ERR_BADLOGIN;
        logmsg(sprintf("Login failure: %s",$login_user_email));
    }
    $PAGE->set_property('error',$status);
}

/* create prompts */
$f = array(
        array(name => user_email,
              type => text,
              size => 250,
              help => email,
              focus => 1,
              prompt => _PROMPT_USER_EMAIL),
        array(name => password,
              type => password,
              size => 250,
              prompt => _LOGIN_PASSWORD),
        array(name => remember,
              type => checkbox,
              prompt => _LOGIN_REMEMBER,
              doc => _DOC_LOGIN_REMEMBER,
              help => 'cookie',
              size => 1,
              rval => 1),
        array(name => redirect,
              type => hidden,
              value => $_GET['redirect'])
    );

$PAGE->set_property(form_name,'login');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->set_property(form_instructions,_LOGIN_INSTR);
$PAGE->input_form(body,$f,'login_','Login');
if ($redirect!='')
    $PAGE->set_property(error,_MSG_MUSTLOGIN);

/* print the damn page */
$PAGE->pparse('page');

?>
