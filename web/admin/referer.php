<?php
// referers report
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
// $Id: referer.php,v 1.7 2003/06/12 14:00:51 glen Exp $
//
// use this as a template

require "siteframe.php";

$r = $DB->read('SELECT referer AS "Referer",COUNT(*) AS "Count" FROM sessions GROUP BY referer ORDER BY "Count" DESC');

$PAGE->set_property('head_content',"<style type=\"text/css\">.Count{text-align:right;}</style>\n",true);
$PAGE->table('body',$r,$_GET['offset'],$PHP_SELF);
$PAGE->set_property('body',
    "<a href=\"$SITE_PATH/admin\">Admin</a>",
    true);

$PAGE->set_property('page_title','Referers');
$PAGE->pparse('page');

?>
