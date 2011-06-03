<?php
// adredirect.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: redirect.php,v 1.3 2003/05/06 22:16:50 glen Exp $
//
// redirects advertisement links; counts click-throughs

include "siteframe.php";

$id = $_GET['id'];
if (!$id) {
  $PAGE->set_property(error, 'Invalid ID specification');
  $PAGE->set_property(body,'');
  $PAGE->pparse();
}
else {
  $ad = new Ad($id);
  $url = trim($ad->get_property('ad_url'));
  if ($url=='') {
    $PAGE->set_property(error, 'Advertisement has no link');
    $PAGE->pparse();
  }
  else {
    $ad->set_property('ad_clicks',$ad->get_property('ad_clicks')+1);
    $ad->update();
    header(sprintf("Location: %s",$url));
  }
}

?>
