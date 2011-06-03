<?php
// download.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: download.php,v 1.4 2003/05/06 22:16:49 glen Exp $
//
// tracks file downloads

include "siteframe.php";

$id = $_GET['id'];
$doc = new DocFile($id);
$file = $doc->get_property('doc_file');
$doc->set_property('doc_file_download_count',
    $doc->get_property('doc_file_download_count')+1);
$doc->update();

logmsg("Downloading %d=%s by %s",
  $doc->get_property('doc_id'),
  $doc->get_property('doc_title'),
  $_SERVER['REMOTE_ADDR']);

header("Location: ".$file);

?>
