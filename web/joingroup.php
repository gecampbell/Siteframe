<?php
// joingroup.php
// $Id: joingroup.php,v 1.2 2003/06/11 19:09:52 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// this page allows a user to join a group

require "siteframe.php";

$PAGE->set_property('page_title','Join Group');

if (!$CURUSER) {
    $PAGE->set_property('error','You cannot join a group unless you are logged in');
}
else if ($_GET['id']) {
    $gr = new Group($_GET['id']);
    $gr->join($CURUSER->get_property('user_id'));
    if (!$gr->errcount()) {
        header(sprintf('Location: %s/group.php?id=%d',$SITE_PATH,$_GET['id']));
        exit;
    }
    else {
        $PAGE->set_property('error',$gr->get_errors());
    }
}
else {
    $PAGE->set_property('error','No group ID specified');
}

$PAGE->pparse('page');

?>
