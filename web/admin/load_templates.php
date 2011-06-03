<?php
// load_templates.php
// $Id: load_templates.php,v 1.5 2003/05/22 01:38:27 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
//
// This loads all existing templates into the templates table

require "siteframe.php";
include "sftemplate.php";

$PAGE->set_property(page_title,'Import Templates');

$themes = get_sorted_dir($LOCAL_PATH.THEMEPATH);
foreach ($themes as $themename) {
    $PAGE->set_property(body,sprintf('Loading %s<br/>',$themename),true);
    $files = get_sorted_dir($LOCAL_PATH.THEMEPATH.$themename);
    foreach ($files as $filename) {
        $PAGE->set_property(body,sprintf('--File: %s<br/>',$filename),true);
        $body = file_get_contents($filename);
        $tpl = new sftemplate();
        $tpl->set_property(tpl_theme,   $themename);
        $tpl->set_property(tpl_name,    $filename);
        $tpl->set_property(tpl_filename,fname_only($filename));
        $tpl->set_property(tpl_body,    $body);
        $tpl->add();
        $PAGE->set_property(error,$tpl->get_errors(),true);
    }
}

$templates = get_sorted_dir($LOCAL_PATH.$SITE_TEMPLATES);
foreach ($templates as $filename) {
    $PAGE->set_property(body,sprintf('Loading %s<br/>',$filename),true);
    $body = file_get_contents($filename);
    $tpl = new sftemplate();
    $tpl->set_property(tpl_theme,       '');
    $tpl->set_property(tpl_name,        $filename);
    $tpl->set_property(tpl_filename,    '');
    $tpl->set_property(tpl_body,        $body);
    $tpl->add();
    $PAGE->set_property(error,$tpl->get_errors(),true);
}

$PAGE->pparse(page);
?>