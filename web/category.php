<?php
// category.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: category.php,v 1.5 2003/05/27 03:42:55 glen Exp $
//
// displays all documents of a requested category

include "siteframe.php";

$PAGE->set_property('page_title',_TITLE_CATEGORY);
$PAGE->set_property('error','');
$PAGE->set_property('body','');

$id = $_GET['id'];
$q = "SELECT * FROM categories WHERE cat_id='$id'";
$r = $DB->read($q);
list($id,$name,$type,$desc) = $DB->fetch_array($r);
if (trim($name)=='') {
  $PAGE->set_property(error,sprintf(_ERR_NOCATEGORY,$catname));
}
else {
  $PAGE->set_property('page_category_id',$id);
  $PAGE->set_property('category_id',$id);
  $PAGE->set_property('category_name',$name);
  $PAGE->set_property('page_title',$name);
  $PAGE->set_property('category_doc_type',$type);
  $PAGE->set_property('category_description',$desc);
  $PAGE->load_template('_category_',$TEMPLATES['Category'][$type]);
  $PAGE->set_property('body',$PAGE->parse('_category_'));
}

$PAGE->pparse('page');

?>
