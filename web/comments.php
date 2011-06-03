<?php
// comments.php
// $Id: comments.php,v 1.1 2003/06/25 05:21:37 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// lists documents (temporary)

include "siteframe.php";

$doc = new Document($_GET['id']);

if (!$_GET['id']) {
  $PAGE->set_property('page_title','Error');
  $PAGE->set_property('error','No ID specified');
}
else if (!$doc->get_property('doc_id')) {
  $PAGE->set_property('page_title','Error');
  $PAGE->set_property('error','No document with that ID');
}
else {
  $PAGE->set_array($doc->get_properties());
  $PAGE->set_property('page_title',sprintf('Comments on "%s"',$doc->title()));
  $PAGE->load_template('_comments_',$TEMPLATES[Comments]);
  $PAGE->set_property('body',$PAGE->parse('_comments_'));
}
$PAGE->pparse('popup');
?>
