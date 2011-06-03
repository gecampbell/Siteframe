<?php
// editgroup.php
// $Id: editgroup.php,v 1.3 2003/06/01 06:18:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// used to create or edit groups

require "siteframe.php";

$PAGE->set_property('page_title',_TITLE_EDIT_GROUP);
if ($_GET['id'])
    $id = $_GET['id'];
else if ($_POST['group_id'])
    $id = $_POST['group_id'];

$gr = new Group($id);

if ($_POST['submitted']) {
    $gr->set_input_form_values($gr->input_form_values());
    if ($_POST['group_id']) {
        $gr->update();
    }
    else {
        $gr->add();
    }
    if ($gr->errcount())
        $PAGE->set_property('error',$gr->get_errors());
    else
        $PAGE->set_property('error',sprintf('%s successful',$id ? 'Update' : 'Add'));
}

$PAGE->set_property('form_name','editgroup');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('body',$gr->input_form_values());

$PAGE->pparse('page');

?>
