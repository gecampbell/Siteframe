<?php
// NAME
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: deleterating.php,v 1.9 2003/05/06 22:16:49 glen Exp $
//
// DESCRIPTION

include "siteframe.php";

$PAGE->set_property(body);

$id = $_GET['id']+0;

if (!$id) {
    $PAGE->set_property(page_title,"No ID");
    $PAGE->set_property(error,"You must supply and id= value to delete a rating");
}
else if (!$CURUSER) {
    $PAGE->set_property(page_title,"Not logged in");
    $PAGE->set_property(error,"You must be logged in to delete a rating");
}
else {
    $q = sprintf("DELETE FROM ratings WHERE doc_id=%d AND user_id=%d",
            $id,
            $CURUSER->get_property('user_id'));
    $r = $DB->write($q);
    header("Location: document.php?id=$id");
    exit;
}
$PAGE->pparse(page);
?>
