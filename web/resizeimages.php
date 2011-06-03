<?php
// $Id: resizeimages.php,v 1.6 2005/03/08 04:40:34 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// This program needs to be executed from the web root of a Siteframe website.
// It will go through all image documents in the site's database and create
// the necessary thumbnail images for them. This can take a while on most
// systems, so use this command:
//
//      php -q -d max_execution_time=0 resizeimages.php
//
// to keep from timing out

include "siteframe.php";

// can we tell if we're running in a browser?
// let's test the server browser string; it's not set when
// running from the command line
if (isset($_SERVER['HTTP_USER_AGENT']))
  die('Sorry: this script cannot be run from a browser');
  
die('This script is currently non-functional; it needs to be upgraded to work with Siteframe 3.0+');

if (!$IMAGE_QUALITY) {
    $IMAGE_QUALITY=80;
}

$q = "SELECT doc_id FROM docs WHERE doc_type='Image' ORDER BY doc_created DESC";
$r = $DB->read($q);
while(list($id) = $DB->fetch_array($r)) {
    $doc = new Image($id);
    printf("resizing %s, %s\n",$doc->get_property(doc_title),$doc->get_property(doc_file));
    $size = GetImageSize($doc->get_property(doc_file));
    $doc->set_property(image_width,$size[0]);
    $doc->set_property(image_height,$size[1]);
    foreach ($SUPPORTED_RESOLUTIONS as $res) {
        $doc->set_property("doc_file_$res",$doc->resizeJPEG($res,$doc->get_property(doc_file_mime_type)));
    }
    $doc->set_property("doc_file_center",$doc->resizeJPEG(100,
            $doc->get_property(doc_file_mime_type),true));
    $doc->update();
}

?>
