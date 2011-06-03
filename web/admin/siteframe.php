<?php
// siteframe.php
// $Id: siteframe.php,v 1.11 2003/06/16 19:08:26 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
$LOCAL_PATH = '../';
$ADMIN_PAGE = true;
include "${LOCAL_PATH}siteframe.php";
$PAGE->set_path('.');
$PAGE->load_file('page','page.ihtml');
$PAGE->load_file('_in_form','form.ihtml');
$PAGE->set_path('../templates');
// if we don't have an administrative user, make one
$r = $DB->read(sprintf("SELECT COUNT(*) FROM users WHERE user_status=%d",
                USER_STATUS_ADMIN));
list($NUM_ADMINS) = $DB->fetch_array($r);
if (($SITE_NAME != '') && (!isadmin()) && ($NUM_ADMINS)) {
    $PAGE->set_property('page_title','Error');
    $PAGE->set_property('body',
        'You cannot access this page unless you are logged in as a site administrator');
    $PAGE->pparse('page');
    exit;
}
if (file_exists('autoload.php'))
  require('autoload.php');
?>
