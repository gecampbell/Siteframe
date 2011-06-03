<?php
// users.php
// $Id: users.php,v 1.9 2006/07/15 02:33:16 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// Generates a list of users

include 'siteframe.php';
restricted();

$r = $DB->read("SELECT user_id FROM users WHERE user_status>0 ORDER BY user_lastname,user_firstname");

// create output page
$PAGE->set_property(page_title,_TITLE_USERS);
$PAGE->load_template(userlist,$TEMPLATES[UserList]);
$PAGE->set_property(body,$PAGE->parse(userlist));

// output the page
$PAGE->pparse(page);

?>
