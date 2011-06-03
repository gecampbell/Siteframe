<?php
// edittheme.php
// $Id: edittheme.php,v 1.4 2003/06/09 16:09:14 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details

require "siteframe.php";
require "sftemplate.php";

$PAGE->set_property('page_title','Edit/Create Theme');
$PAGE->set_property('form_instructions','Press SUBMIT when complete');
$PAGE->set_property('form_action',$PHP_SELF);

$actions = <<<ENDACTIONS
<p class="action">
  <a href="themes.php">Themes</a> |
  <a href="edittemplate.php?theme={theme_id}">Add Template</a> |
  <a href="exporttheme.php?id={theme_id}">Export</a>
</p>
ENDACTIONS;

if ($_POST['submitted']) {
  $th = new sftheme();
  $th->set_input_form_values($th->input_form_values());
  if ($th->errcount())
    $PAGE->set_property('error',$th->get_errors());
  else {
    $th->add();
    $id = $th->get_property('theme_id');
    if (!$th->errcount()) {
      $PAGE->set_property('error','Theme created');
      // create the directory
      $themedir = str_replace(' ','_',$th->get_property('theme_name'));
      mkdir($LOCAL_PATH . THEMEPATH . $themedir);
      // create initial templates
      $tpl = new sftemplate();
      $tpl->set_property('tpl_name','page');
      $tpl->set_property('tpl_theme_id',$th->get_property('theme_id'));
      $tpl->add();
      $tpl = new sftemplate();
      $tpl->set_property('tpl_name','form');
      $tpl->set_property('tpl_theme_id',$th->get_property('theme_id'));
      $tpl->add();
      $tpl = new sftemplate();
      $tpl->set_property('tpl_name','popup');
      $tpl->set_property('tpl_theme_id',$th->get_property('theme_id'));
      $tpl->add();
    }
    else
      $PAGE->set_property('error',$th->get_errors());
  }
}
if ($_GET['id'] || $id) {
  $th = new sftheme($id ? $id : $_GET['id']);
  if (!$th->get_property('theme_id')) {
    $PAGE->set_property('error','No theme with that ID');
  }
  else {
    $PAGE->set_property(
      'page_title',
      sprintf('Editing theme "%s"',$th->get_property('theme_name'))
    );
    $PAGE->set_property('body',
      '<p>Select a theme template to edit:</p><ul>');
    $q = sprintf(
          'SELECT tpl_id FROM templates WHERE tpl_theme_id=%d ORDER BY tpl_name',
          $th->get_property('theme_id'));
    $r = $DB->read($q);
    while(list($id) = $DB->fetch_array($r)) {
      $tpl = new sftemplate($id);
      $PAGE->set_property(
        'body',
        sprintf('<li><a href="edittemplate.php?id=%d">%s</a></li>'."\n",
          $tpl->get_property('tpl_id'),$tpl->get_property('tpl_name')),
        TRUE
      );
    }
    $PAGE->set_property('body','</ul>',TRUE);
    $PAGE->set_property('theme_id',$th->get_property('theme_id'));
    $PAGE->set_property('_actions_',$actions);
    $PAGE->set_property('body',$PAGE->parse('_actions_'),TRUE);
  }
}
else {
  $th = new sftheme();
  $PAGE->set_property('page_title','Create new theme');
  $PAGE->input_form('body',$th->input_form_values());
}

$PAGE->pparse('page');

?>