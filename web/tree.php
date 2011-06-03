<?php
// tree.php
// $Id: tree.php,v 1.11 2003/06/07 01:27:24 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// displays a tree-structured list of folders

include "siteframe.php";

function folder_children($id=0) {
    global $SITE_PATH,$DB,$PUBLIC_FOLDER_PREFIX,$PUBLIC_FOLDER_SUFFIX;
    $r = $DB->read("SELECT * ".
                    "FROM folders ".
                    "WHERE folder_parent_id=$id ".
                    "ORDER BY folder_name");
    $out = "<ul>\n";
    while($dbrow = $DB->fetch_array($r)) {
        $class = $dbrow['folder_type'];
        $folder = new $class(0, $dbrow);
        $out .= sprintf("<li><a href=\"%s/folder.php?id=%d\">%s</a></li>\n",
                    $SITE_PATH,
                    $folder->get_property('folder_id'),
                    $folder->get_property('folder_name_display'));
        $out .= folder_children($folder->get_property('folder_id'));
    }
    $out .= "</ul>\n";
    return $out;
}

$PAGE->set_property(page_title,$TREE_TITLE!="" ? $TREE_TITLE : "Site Folder Map");
$PAGE->set_property(body,folder_children());
$PAGE->pparse(page);

?>