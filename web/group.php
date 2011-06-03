<?php
// group.php
// $Id: group.php,v 1.3 2003/05/27 03:42:55 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// displays a single group

require "siteframe.php";

if (!$_GET['id']) {
    $PAGE->set_property('page_title','Group Error');
    $PAGE->set_property('error','Group ID not specified');
}
else {
    $gr = new Group($_GET['id']);
    if ($gr->get_property('group_type')==GROUP_VIRTUAL) {
        $AUTOBLOCK[group] =
            $gr->get_property('group_sql');
    }
    $PAGE->set_array($gr->get_properties());
    $PAGE->set_property('page_title',$gr->get_property('group_name'));
    $PAGE->load_template('_group_',$TEMPLATES[Group]);
    $PAGE->set_property('body',$PAGE->parse('_group_'));
}
$PAGE->pparse('page');

?>
