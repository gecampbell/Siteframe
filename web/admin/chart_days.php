<?php
// chart_days.php
// $Id: chart_days.php,v 1.1 2003/05/30 05:23:22 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.

require "siteframe.php";
$PAGE->set_property('page_title','Chart - Visits per Day');
$PAGE->set_path('.');
$PAGE->load_file('report','chart_days.ihtml');
$PAGE->set_property('body',$PAGE->parse('report'));
$PAGE->pparse('page');
?>
