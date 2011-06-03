<?php
// month.php
// $Id: month.php,v 1.6 2003/06/07 01:27:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
//
// displays all events for a month

include "siteframe.php";

$y=$_GET['y'];
$m=$_GET['m'];

if (!$y)
    $y = date('Y');
if (!$m)
    $m = date('m');
$m = sprintf("%d",$m);
$PAGE->set_property(thisyear,date('Y'));
$PAGE->set_property(thismonth,date('m'));
$PAGE->set_property(thisday,date('d'));
$PAGE->set_property(page_title,sprintf("%s %d",$MONTH[$m],$y));
$PAGE->set_property(year,sprintf("%04d",$y));
$PAGE->set_property(month,sprintf("%02d",$m));
$PAGE->set_property(yearmon,sprintf("%04d%02d",$y,$m));
$PAGE->set_property(month_name,$MONTH[$m]);
$PAGE->load_template(_body_,$TEMPLATES[Month]);
$PAGE->set_property(body,$PAGE->parse(_body_));

$PAGE->pparse(page);

?>
