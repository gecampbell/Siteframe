<?php
// error.php
// $Id: error.php,v 1.2 2003/05/27 03:42:55 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// this document handles Apache errors

$ADMIN_PAGE=true;   // don't lock out on maintenance mode
require "siteframe.php";

$PAGE->set_property('page_title','Error');

if (!$_GET['code']) {
    $PAGE->set_property('error','No error code defined');
}
else {
    $PAGE->set_property('errorcode',$_GET['code']);
    foreach($_SERVER as $name => $value) {
        $PAGE->set_property('error_'.$name,$value);
        $server .= $name.': '.$value."\n";
    }
    $PAGE->load_template('errorpage',$TEMPLATES['Error']);
    $PAGE->set_property('server',$server);
    $PAGE->set_property('body',$PAGE->parse('errorpage'));
}

$PAGE->pparse('page');

?>
