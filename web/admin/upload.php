<?php
// upload.php
// $Id: upload.php,v 1.4 2003/06/05 05:37:35 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
// this allows an administrator to upload a file to the site (for logos, etc.)

require "siteframe.php";
require "uploadedfile.php";

if ($_POST['submitted']) {
    $uf = new uploadedfile();
    $uf->set_input_form_values($uf->input_form_values());
    if ($uf->errcount())
        $PAGE->set_property('error',$uf->get_errors());
    else
        $PAGE->set_property('error','File uploaded');
}

$uf = new uploadedfile();
$PAGE->set_property('page_title','Upload a file');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('body',$uf->input_form_values(),'','Upload');
$PAGE->pparse('page');

?>
