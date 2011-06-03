<?php
// agents report
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: agent.php,v 1.6 2003/05/06 22:16:50 glen Exp $
//
// use this as a template

require "siteframe.php";

$r = $DB->read('SELECT agent AS "User Agent",COUNT(*) AS "Count" FROM sessions GROUP BY agent ORDER BY "Count" DESC');

$PAGE->set_property('head_content',"<style type=\"text/css\">.Count{text-align:right;}</style>\n",true);
$PAGE->table('body',$r,$_GET['offset'],$PHP_SELF);
$PAGE->set_property('body',
    "<a href=\"$SITE_PATH/admin\">Admin</a>",
    true);

$PAGE->set_property('page_title','User Agents');
$PAGE->pparse('page');

?>
