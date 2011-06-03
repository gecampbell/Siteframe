<?php
// set_featured_image.php
// $Id: set_featured_image.php,v 1.1 2003/11/29 15:58:19 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// sets a featured image

require "siteframe.php";

if (!$_GET['id']) {
  $PAGE->set_property(page_title,'Error: No ID');
  $PAGE->set_property(error,_ERR_NOID);
  $PAGE->set_property(body);
}
else {
  $DB->write('DELETE FROM properties WHERE name="FEATURED_IMAGE_ID"');
  $DB->write(
    sprintf("INSERT INTO properties (name,value) VALUES ('%s','%d')",
      'FEATURED_IMAGE_ID',
      $_GET['id'])
  );
  header(sprintf('Location: document.php?id=%d',$_GET['id']));
}

$PAGE->pparse('page');

?>