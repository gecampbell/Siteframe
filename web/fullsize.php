<?php
// fullsize.php
// $Id: fullsize.php,v 1.1 2003/06/01 18:41:38 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// displays an image full-sized.

require "siteframe.php";

$TPL = <<<ENDTPL
<html>
<head>
<title>{doc_title}</title>
<style type="text/css">
body { margin: 0; }
</style>
</head>
<body>
{error}
<img src="{doc_file}" width="{image_width}" height="{image_height}" alt="{doc_title}"/>
</body>
</html>
ENDTPL;

$id = $_GET['id'];
if (!$id) {
  $PAGE->set_property('error','No document with that ID');
}
else {
  $class = doctype($id);
  if ($class != "Image")
    $PAGE->set_property('error','The specified document is not an image');
  else {
    $doc = new Image($id);
    $PAGE->set_array($doc->get_properties());
    $PAGE->set_property('page',$TPL);
  }
}
$PAGE->pparse('page');
?>
