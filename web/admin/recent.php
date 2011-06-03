<?php
// Recent visitors report
// $Id: recent.php,v 1.4 2003/06/12 14:00:51 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.

require "siteframe.php";

$r = $DB->read('SELECT session_date as "Date", '.
               'CONCAT(user_firstname,\' \',user_lastname) AS "User", '.
               'remote_ip as "Remote IP",referer as "Referer"'.
               'FROM sessions LEFT OUTER JOIN users '.
               'ON (sessions.session_uid=users.user_id) '.
               'ORDER BY session_date DESC');

$PAGE->set_property('head_content',
  "<style type=\"text/css\">.Date,.User{white-space:nowrap;}</style>\n",true);
$PAGE->table('body',$r,$_GET['offset'],$PHP_SELF);

$PAGE->set_property('page_title','Recent Visitors');
$PAGE->pparse('page');

?>