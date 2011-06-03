<?php
/* folders.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: notices.php,v 1.4 2003/05/27 03:42:55 glen Exp $
**
** displays a list of folders
*/
include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_NOTICE_LIST);
$PAGE->load_template(_folder_,$TEMPLATES[NoticeList]);
$PAGE->set_property(body,$PAGE->parse(_folder_));
$PAGE->pparse(page);

?>
