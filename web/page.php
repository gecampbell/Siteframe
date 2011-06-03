<?php
/* docs.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: page.php,v 1.6 2003/06/07 01:27:23 glen Exp $
**
** lists documents (temporary)
*/
include "siteframe.php";

$page = $_GET['page'];
$id = $_GET['id'];

if ($page) {
    $PAGE->load_template(_page_,$page);
}
else if ($id) {
    $class = doctype($id);
    $d = new $class($id);
    $PAGE->set_property(_page_,$d->get_property(doc_body));
}
else
    $PAGE->set_property(error,_ERR_NODOC);
$PAGE->set_property(body,$PAGE->parse(_page_));
$PAGE->pparse(page);
?>
