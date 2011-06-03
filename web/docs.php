<?php
// docs.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
// $Id: docs.php,v 1.8 2003/06/19 05:35:21 glen Exp $
//
// lists documents (temporary)

include "siteframe.php";
restricted();

$PAGE->set_property(page_title,'Document List');
$PAGE->set_property(docs_list,'');
$PAGE->load_template(doclist,$TEMPLATES[Docs]);
$PAGE->set_property(body,$PAGE->parse(doclist));
$PAGE->pparse(page);
?>
