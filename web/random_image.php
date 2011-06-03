<?php
// random_image.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: random_image.php,v 1.4 2005/03/08 04:40:34 glen Exp $
//
// This file returns a random image from the database
// It should be used from within a HTML page like this:
// <img src="/random_image.php?size=N" etc.../>

include "siteframe.php";

if (!$_GET['size']) {
  die("Must specify image size for random image");
}
else {
  $size = $_GET['size'];
}

$r = $DB->read("SELECT MIN(doc_id),MAX(doc_id) FROM docs");
list($min,$max) = $DB->fetch_array($r);
$try = 0;
$val = 0;
while(!$val && ($try++ < 20)) {
  $randnum = rand($min,$max);
  $r = $DB->read(sprintf("SELECT doc_id FROM docs ".
                          "WHERE doc_type='Image' AND doc_id>=%d ".
                          "ORDER BY doc_created",
                          $randnum));
  list($val) = $DB->fetch_array($r);
  // logmsg("debug: random number=%d, val=%d",$randnum,$val);
}

// at this point, die if $val doesn't have a value
if (!$val) {
  die("Unable to determine a random image");
}
$class = doctype($val);
$doc = new $class($val);
header(sprintf("Content-type: %s",$doc->get_property('doc_file_mime_type')));
$fp = fopen($doc->get_property("doc_file_$size"),'r');
fpassthru($fp);
fclose($fp);

?>
