<?php
// objs.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
// $Id: objs.php,v 1.7 2003/06/12 14:00:51 glen Exp $
//
// defines active and inactive document classes

require "siteframe.php";

if ($_POST['submitted']) {
  // process updates
  $q = "SELECT obj_id,obj_class FROM objs";
  $r = $DB->read($q);
  while(list($id,$class) = $DB->fetch_array($r)) {
    $q = sprintf("UPDATE objs SET obj_active=%d WHERE obj_id=%d",
          $_POST["activate_${id}"],$id);
    $DB->write($q);
  }
  $PAGE->set_property('error','Updated');
}

$q = "SELECT obj_id,obj_active,obj_class FROM objs";
$r = $DB->read($q);
while(list($id,$act,$class)=$DB->fetch_array($r)) {
  $form[] = array(
              name => "activate_${id}",
              type => "checkbox",
              rval => 1,
              value => $act,
              prompt => $class,
            );
}

$instr = <<<ENDINSTR
Use this form to select the document types that will be available
(active) on your site. If the box is checked, the item is active;
otherwise, it will not be available. <b>Please note</b> that this
will not affect any <i>existing</i> documents of the specified
type(s); it will only inhibit the creation of <i>new</i> documents.
ENDINSTR;

$PAGE->set_property('page_title','Document Types');
$PAGE->set_property(form_name,'doc_classes');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->set_property(form_instructions,$instr);
$PAGE->set_property(input_form_hidden,'');
$PAGE->input_form(body,$form);
$PAGE->pparse('page');

?>
