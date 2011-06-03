<?php
// groups.php
// $Id: groups.php,v 1.3 2003/05/27 03:42:55 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// lists all groups

require "siteframe.php";

$PAGE->set_property('page_title',_TITLE_GROUPS);
$PAGE->load_template('_groups_',$TEMPLATES[Groups]);
$PAGE->set_property('body',$PAGE->parse('_groups_'));
$PAGE->pparse('page');

?>
