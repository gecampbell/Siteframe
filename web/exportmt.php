<?php
// NAME
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: exportmt.php,v 1.5 2003/05/27 03:42:55 glen Exp $
//
// DESCRIPTION

include "siteframe.php";

$PAGE->load_template(mt,"exportmt");
$PAGE->set_property(output,$PAGE->parse(mt));
$PAGE->pparse(output);

?>
