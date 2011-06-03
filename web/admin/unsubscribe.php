<?php
// mailto.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: unsubscribe.php,v 1.4 2004/09/23 22:29:11 glen Exp $

require "siteframe.php";
restricted();
$PAGE->set_property(page_title,"Unsubscribe Users");

if (!$CURUSER) {
    header("Location: login.php?redirect=".htmlentities(urlencode("$PHP_SELF?id=$id")));
    exit;
}
else if (!isadmin()) {
    siteframe_abort("This page is restricted to site administrators");
}
else if ($_POST['submitted']) {
    ini_set(max_execution_time,0); // allow all time to execute
    $addrarr = split("\n", $_POST['addresses']);
    foreach($addrarr as $email)
    {
        $q = sprintf("SELECT user_id FROM users WHERE user_email='%s'", 
                trim($email));
        $r = $DB->read($q);
        list($uid) = $DB->fetch_array($r);
        $u = new User($uid);
        $u->set_property('user_subscribe', 0);
        $u->update();
        if (!$uid)
	    $PAGE->set_property('body',sprintf("%s NOT FOUND<br/>\n", $email),true);
        else
            $PAGE->set_property('body', $email."<br/>\n", true);
    }
}
else {
    $mailform = array(
        array(name => addresses,
              type => textarea,
              rows => 15,
              prompt => "Enter e-mail addresses to unsubscribe")
    );
    $PAGE->set_property(form_name,'unsubscribe');
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property(form_instructions, 
        "This page is used to unsubscribe users from the mailing list. ".
        "When you have a bounced e-mail, use this form to unsubscribe users. ".
        "Enter a list of e-mail addresses, one per line.");
    $PAGE->input_form(_mail_,$mailform,'','Unsubscribe');
    $PAGE->set_property(body,$PAGE->parse(_mail_));
}

$PAGE->pparse(page);

?>
