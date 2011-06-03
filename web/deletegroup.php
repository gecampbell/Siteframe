<?php
// deletegroup.php
// $Id: deletegroup.php,v 1.2 2003/05/11 05:55:52 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// deletes a group

require "siteframe.php";

$id = $_GET['id'] ? $_GET['id'] : $_POST['group_id'];
$gr = new Group($id);

if ($_POST['submitted']) {
    $gr->delete($_POST['delete_reason']);
    if ($gr->errcount()) {
        $PAGE->set_property('page_title','Error');
        $PAGE->set_property('error',$gr->get_errors());
    }
    else {
        $PAGE->set_property('page_title','Deleted');
        $PAGE->set_property('error','The group has been deleted');
    }
}
else if (!$id) {
    $PAGE->set_property('page_title','Error');
    $PAGE->set_property('error','You must supply a group ID');
}
else {
    $PAGE->set_property('page_title',
        sprintf('Delete group "%s"',$gr->get_property('group_name')));
    $PAGE->set_property('form_instructions','Press Submit to remove this group.');
    $f = array(
        array(
            name => delete_reason,
            type => textarea,
            prompt => 'Reason',
            doc => 'You can enter a reason for deleting the group here. It will be logged for future reference.'
        ),
        array(
            name => group_id,
            value => $_GET['id'],
            type => hidden
        )
    );
    $PAGE->set_property('form_action',$PHP_SELF);
    $PAGE->input_form('body',$f,'','DELETE');
}

$PAGE->pparse('page');

?>
