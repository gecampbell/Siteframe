<?php
// exporttheme.php
// $Id: exporttheme.php,v 1.5 2003/06/06 05:56:41 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this exports a theme in a standard XML format
// requires id= (theme_id) for operation

require "siteframe.php";
require "sftemplate.php";

$EXPORTXML = <<<ENDEXPORTXML
<?xml version="1.0" encoding="{charset}"?>
{BEGIN:theme theme_id}
<theme>
  <theme_name>{theme_name}</theme_name>
  {BEGIN:theme_templates theme_id}
  <template>
    <template_name>{tpl_name}</template_name>
    <template_file>{tpl_filename}</template_file>
    <template_type>{tpl_type_id}</template_type>
    <date_modified>{tpl_modified}</date_modified>
    <template_body><![CDATA[{tpl_body}]]></template_body>
  </template>
  {END:theme_templates}
</theme>
{END:theme}
ENDEXPORTXML;

if ($_GET['template']=='') {
$AUTOBLOCK['theme_templates'] = <<<ENDBLOCK1
SELECT *
FROM templates
WHERE tpl_theme_id=%d
ENDBLOCK1;
}
else {
$name = $_GET['template'];
$AUTOBLOCK['theme_templates'] = <<<ENDBLOCK2
SELECT *
FROM templates
WHERE tpl_theme_id=%d AND tpl_name='$name'
ENDBLOCK2;
}

$PAGE->set_property('page_title','Export Theme');

if (($id=$_GET['id'])!='') {     // handle form
  $th = new sftheme($id);
  if ($id && (!$th->get_property('theme_id'))) {
    $PAGE->set_property('error','No theme with specified ID');
  }
  else {
    if ($id) {
      $AUTOBLOCK['theme'] = 'SELECT * FROM themes WHERE theme_id=%d';
    }
    else if ($_GET['template']=='') {
      $AUTOBLOCK['theme'] = 'SELECT 0 AS theme_id, \'CONTENT\' AS theme_name';
      $th->set_property('theme_name','CONTENT');
    }
    else {
      $themename = sprintf('CONTENT-%s',$_GET['template']);
      $AUTOBLOCK['theme'] = "SELECT 0 AS theme_id, '$themename' AS theme_name";
      $th->set_property('theme_name',$themename);
    }
    $PAGE->set_property('theme_id',$id);
    $PAGE->set_property('xml',$EXPORTXML);
    $exportfile = $LOCAL_PATH . $FILEPATH .
      $th->get_property('theme_name') . '.theme';
    $exportpath = $SITE_URL . '/' . $FILEPATH .
      $th->get_property('theme_name') . '.theme';
    $fp = fopen($exportfile,'w');
    fwrite($fp,$PAGE->parse('xml'));
    fclose($fp);
    logmsg('Exported theme %s to %s',
      $th->get_property('theme_name'),
      $exportfile);
    $PAGE->set_property('body',
      sprintf('<p>Click to download <a href="%s">%s</a>.</p>',
        $exportpath,
        $exportpath)
    );
  }
}
else { // error
  $PAGE->set_property('error','No theme ID specified');
}

$PAGE->pparse('page');
?>
