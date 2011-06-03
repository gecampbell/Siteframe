<?php
// Categories
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: categories.php,v 1.7 2006/05/12 13:45:44 glen Exp $
//
// Lets the site administrator define and edit categories

require "siteframe.php";

$doctypes[''] = 'Not restricted';
foreach($CLASSES as $type => $prompt)
  $doctypes[$type] = $prompt;

$form[] = array(
            name => new_cat,
            type => text,
            size => 250,
            prompt => "New Category"
          );

$form[] = array(
            name => new_cat_doc_type,
            type => select,
            options => $doctypes,
            prompt => "(Optional) restrict to documents of type"
          );

$form[] = array(
            name => new_desc,
            type => textarea,
            rows => 2,
            prompt => "Description"
          );

if ($_POST['submitted'] && ($_POST['new_cat']!='')) {
  $q = sprintf("INSERT INTO categories (cat_name,cat_doc_type,cat_description) ".
               "VALUES ('%s','%s','%s')",
               $_POST['new_cat'],
               $_POST['new_cat_doc_type'],
               $_POST['new_desc']);
  $DB->write($q);
  if ($DB->errcount())
    $PAGE->set_property(error,$DB->get_errors(),true);
  else
    $PAGE->set_property(error,sprintf("Added category '%s'<br/>",$_POST['new_cat']));
}

if ($_POST['submitted']) {
  $qw = "SELECT * FROM categories ORDER BY cat_name";
  $r = $DB->read($qw);
  while($arr = $DB->fetch_array($r)) {
    $id = $arr['cat_id'];
    if ($_POST["cat_del_$id"]+0) {
      $DB->write(sprintf("DELETE FROM categories WHERE cat_id=%d",$id));
      $PAGE->set_property(error,$DB->get_errors(),true);
      $DB->write(sprintf("DELETE FROM doc_categories WHERE doc_cat_id=%d",$id));
      logmsg("Deleted category %s",$arr['cat_name']);
    }
    else if ($_POST["cat_name_$id"]!='') {
      $DB->write(sprintf("UPDATE categories SET ".
                         " cat_name='%s',".
                         " cat_doc_type='%s',".
                         " cat_description='%s' ".
                         "WHERE cat_id=%d",
                         $_POST["cat_name_$id"],
                         $_POST["cat_doc_type_$id"],
                         $_POST["cat_desc_$id"],
                         $id));
      $PAGE->set_property(error,$DB->get_errors(),true);
    }
  }
}

$qr = "SELECT cat_id,cat_name,cat_doc_type,cat_description ".
      "FROM categories ORDER BY cat_name";
$r = $DB->read($qr);

while($arr = $DB->fetch_array($r)) {
  $id = $arr['cat_id'];
  $form[] = array(
              type => "ignore",
              prompt => sprintf("<hr/><h3>%s</h3>",$arr['cat_name'])
            );
  $form[] = array(
              name => "cat_name_$id",
              type => text,
              size => 250,
              value => $arr['cat_name'],
              prompt => "Name"
            );
  $form[] = array(
              name => "cat_doc_type_$id",
              type => select,
              options => $doctypes,
              value => $arr['cat_doc_type'],
              prompt => "(Optional) restrict to documents of type"
            );
  $form[] = array(
              name => "cat_desc_$id",
              type => textarea,
              rows => 2,
              value => $arr['cat_description'],
              prompt => "Description"
            );
  $form[] = array(
              name => "cat_del_$id",
              type => checkbox,
              value => 0,
              rval => 1,
              prompt => "Check to delete category"
            );
}

// form instructions
$instr = <<<ENDINSTR
Use this form to define categories for your site. You can optionally restrict
use of a category to a specific type of document; for example, you might want
"Landscapes" to be only used by "Image" documents (pictures). To delete a
category, check the "Check to delete category" box. Press <b>Submit</b>
when complete.
ENDINSTR;

$PAGE->set_property('page_title','Categories');
$PAGE->set_property(form_name,'categories');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->set_property(form_instructions,$instr);
$PAGE->set_property(input_form_hidden,'');
$PAGE->input_form(body,$form);

$PAGE->pparse('page');

?>
