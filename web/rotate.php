<?php
// rotate.php
// $Id: rotate.php,v 1.2 2003/07/04 15:14:21 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// rotates an image: rotate.php?id=N&rot=L
//  where N is the doc ID and rot=L (counter-clockwise) or R (clockwise)

require "siteframe.php";

ini_set("memory_limit", "64M");

$PAGE->set_property('page_title','Rotate Image');

if (!$_GET['id']) {
  $PAGE->set_property('error','No ID specified');
}
else if (!$_GET['rot']) {
  $PAGE->set_property('error','No ROT specified');
}
else {
  $class = doctype($_GET['id']);
  if ($class!='Image') {
    $PAGE->set_property('error',sprintf('Document %d is not an image',$_GET['id']));
  }
  else {
    $doc = new Image($_GET['id']);
    $tempfile = sprintf('/tmp/%s.jpg',sha1(time()));
    $filename = $doc->get_property('doc_file');
    $outfile = preg_replace('/\.([^\.]+)$/',
                sprintf('%s.\1',strtolower($_GET['rot'])),
                $filename);
    $img_size = getImageSize($filename);
    $x = $img_size[0];
    $y = $img_size[1];
    if ($x > $y) {
     $newd = $x;
    }
    else {
     $newd = $y;
    }

    $src_img = ImageCreateFromJPEG($filename);
    $final_img = ImageCreateTrueColor($y,$x);

    $degrees = $_GET['rot']=='L' ? 90 : -90;
    $rotated_img = ImageRotate($src_img,$degrees,0);
    ImageCopyResampled($final_img,$rotated_img,0,0,0,0,$y,$x,$y,$x);
    ImageJPEG($final_img,$outfile);
    $doc->set_property('doc_file',$outfile);
    $doc->update();
    header(sprintf('Location: document.php?id=%d',$_GET['id']));
  }
}
$PAGE->pparse('page');
?>
