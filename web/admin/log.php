<?php
// log.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: log.php,v 1.10 2003/06/24 02:30:19 glen Exp $
//
// displays the activity log

require "siteframe.php";

if ($_GET['clear']) {
    $DB->write("DELETE FROM activity");
    logmsg("activity log cleared");
    $PAGE->set_property(error,"The activity log has been cleared");
}
else {
    $r = $DB->read("SELECT event_date,message FROM activity ORDER BY event_id");
    $PAGE->table(body,$r,$_GET['offset'],$PHP_SELF);
}

$PAGE->set_property(page_title,_TITLE_LOG);
$PAGE->pparse(page);
?>
