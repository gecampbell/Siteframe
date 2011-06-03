<?php
/* folders.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: folders.php,v 1.8 2003/06/07 01:27:23 glen Exp $
**
** displays a list of folders
*/
include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_FOLDER_LIST);
$PAGE->set_property(folder_list,'');
$PAGE->load_template(_folder_,$TEMPLATES[FolderList]);
$PAGE->set_property(body,$PAGE->parse(_folder_));
$PAGE->pparse(page);

?>
