<?php
// leavegroup.php
// $Id: leavegroup.php,v 1.1 2003/05/11 05:56:51 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// removes a member from the group

require "siteframe.php";

$PAGE->set_property('page_title','Leave Group');

$gr = new Group($_GET['id'] ? $_GET['id'] : $_POST['group_id']);

if (!$gr->get_property('group_id')) {
    $PAGE->set_property('error','You must supply a group ID');
}
else if (!$CURUSER) {
    $PAGE->set_property('error','You must be logged in to access this page');
}
else if ($_POST['submitted']) {
    $gr->remove($CURUSER->get_property('user_id'),$_POST['reason']);
    if ($gr->errcount())
        $PAGE->set_property('error',$gr->get_errors());
    else
        $PAGE->set_property('error','You have left the group');
}
else {
    $f = array(
        array(
            name => group_id,
            type => hidden,
            value => $id
        ),
        array(
            name => reason,
            type => textarea,
            prompt => 'Reason',
            doc => 'You can optionally enter a reason for leaving the group. If provided, it will be logged.'
        )
    );
    $PAGE->set_property('page_title',sprintf('Leave group "%s"',$gr->get_property('group_name')));
    $PAGE->set_property('form_instructions','Press Submit to confirm and leave the group');
    $PAGE->set_property('form_action',$PHP_SELF);
    $PAGE->input_form('body',$f);
}

$PAGE->pparse('page');

?>
