<?php
// clears the session log
// $Id: clearlog.php,v 1.1 2003/05/31 06:29:55 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.

require "siteframe.php";

if (!$LOG_DAYS)
  $LOG_DAYS=7;

$q = sprintf(
      'DELETE FROM sessions WHERE session_date<DATE_SUB(NOW(),INTERVAL %d DAY)',
      $LOG_DAYS);
$DB->write($q);
print mysql_error();
$PAGE->set_property('page_title','Clear the session log');
$PAGE->set_property('error','Cleared');
$PAGE->set_property('body',
  '<a href="index.php">Click here to return to the Control Panel</a>');
$PAGE->pparse('page');

?>