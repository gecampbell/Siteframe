<?php
/* folder.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: slideshow.php,v 1.6 2003/05/27 03:42:55 glen Exp $
**
** displays a folder
*/
include "siteframe.php";

$PAGE->load_template(page,$TEMPLATES[Slideshow]);

if (!$_GET['id']) {
    $PAGE->set_property(page_title,'Error: No Folder');
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body,'');
}
else {
    $class = foldertype($_GET['id']);
    if ($class=='') {
        $PAGE->set_property(error,'Non-existent folder');
        $PAGE->set_property(body,'');
    }
    else {
        $folder = new $class($_GET['id']);
        if ($folder->get_property('folder_limit_type')!='Image') {
            siteframe_abort('Slideshow can only be used with image folders');
        }
        $PAGE->set_array($folder->get_properties());
    }
}
$PAGE->pparse(page);
?>
