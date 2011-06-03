<?php
/* index.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: my.php,v 1.5 2003/05/27 03:42:55 glen Exp $
**
** Main page for Siteframe.
*/
include "siteframe.php";

if ($SITE_NAME=='')
    header("Location: install.php");
$PAGE->set_property(page_title,$SITE_NAME);
$PAGE->load_template(index,$TEMPLATES[Index]);
$PAGE->set_property(body,$PAGE->parse(index));
$PAGE->pparse(page);
?>
