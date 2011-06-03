<?php
// edittemplate.php
// $Id: edittemplate.php,v 1.16 2003/06/09 16:02:21 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
//
// used to edit templates

require "siteframe.php";
include "sftemplate.php";

$PAGE->set_property(page_title,'Edit/Create Template');

$id = $_GET['id'] ? $_GET['id'] : $_POST['tpl_id'];
$submitted = $_POST['submitted'];

if ($submitted) {
    $tpl = new sftemplate($id = $_POST['tpl_id']);
    $tpl->set_input_form_values($tpl->input_form_values());
    if ($id) {                          // update
        $tpl->update();
        if (!$tpl->errcount())
            $PAGE->set_property(error,'Update successful',true);
    }
    else {                              // add
        $tpl->add();
        $id = $tpl->get_property('tpl_id');
        if (!$tpl->errcount())
            $PAGE->set_property(error,'Insert successful',true);
    }
    logmsg('Saved template %s',$tpl->get_property('tpl_name'));
    if (!$tpl->errcount() && ($tpl->get_property('tpl_filename')!='')) {
        $fp = @fopen($tpl->fullname(),'wb');
        if ($fp) {
          fwrite($fp,$tpl->get_property(tpl_body));
          fclose($fp);
        }
    }
    $PAGE->set_property(error,$tpl->get_errors(),true);
}

$t = new sftemplate($id);
if ($_GET['theme']) {
  $themeid = $_GET['theme'];
  $th = new sftheme($themeid);
  if ($th->get_property('theme_id')) {
    $t->set_property('tpl_theme_id',$th->get_property('theme_id'));
    $t->set_property('theme_name',$th->get_property('theme_name'));
  }
  else
    $PAGE->set_property('error','No theme with that ID');
}

if ($id && ($t->get_property('tpl_filename')!='')) {
    // check dates on template and file, update as appropriate
    $tdate = strtotime($t->get_property(tpl_modified));
    $fp = @fopen($t->fullname(),'r');
    if ($fp) {
      $finfo = fstat($fp);
      $fdate = $finfo[9];
      if ($tdate < $fdate) {
          $t->set_property(tpl_body,file_get_contents($t->fullname()));
          logmsg('Loaded template %s from file %s',
            $t->get_property('tpl_name'),
            $t->fullname());
      }
    }
}

$instr = <<<ENDINSTR
<p>Use this page to edit your templates. Modify any necessary
data, and press <b>Save</b> to save.</p>
<p class="action">
{!if '{tpl_theme_id}'
  'Return to "<a href="edittheme.php?id={tpl_theme_id}">{theme_name}</a>"'
  'Return to <a href="templates.php">content templates</a>'
!}
</p>
ENDINSTR;

$PAGE->set_property(error,$t->get_errors(),true);
$PAGE->set_property(form_name,'edittpl');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->set_array($t->get_properties());
$PAGE->set_property('_instr_',$instr);
$PAGE->set_property(form_instructions,$PAGE->parse('_instr_'));
$PAGE->input_form(body,$t->input_form_values(),'','Save');

$PAGE->pparse(page);
?>
