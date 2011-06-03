<?php
// templates.php
// $Id: templates.php,v 1.10 2003/06/27 02:50:08 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// used for listing and editing content templates

require "siteframe.php";
require "sftemplate.php";

// this is an inline template for the page
$TPL = <<<ENDTPL
<p>Click on the name of a template to edit it, or use the Delete or Export
links to perform the requested function. Be very careful when you delete
a template; no confirmation is required, and deletion of certain templates
could render your site unusable.</p>
<p class="action">
 <a href="edittemplate.php">Create a new Template</a> |
 <a href="exporttheme.php?id=0">Export all content templates</a>
</p>
<form method="post" action="$PHP_SELF">
<table class="list">
{BEGIN:content_template_types}
<tr><th colspan="9">
{!case '{tpl_type_id}'
  0   'Generic'
  1   'Home Page'
  2   'Navigation'
  3   'Footer'
      'Custom'
!}</th></tr>
 {BEGIN:content_templates_by_type tpl_type_id}
 <tr class="{row_class}">
  <td><a href="edittemplate.php?id={tpl_id}">{tpl_name}</a></td>
  <td align="center"><input type="checkbox" name="tpl[]" value="{tpl_id}"/></td>
 </tr>
 {END:content_templates_by_type}
 <tr><td colspan="9">&nbsp;</td></tr>
{END:content_template_types}
</table>
<input type="hidden" name="submitted" value="1"/>
{!if '{lockdown}+0' ''
  '<input type="submit" name="delete" value="Delete Checked"/><br/>'!}
<input type="submit" name="export" value="Export Checked"/>
to package <input type="text" name="package" size="30" value="CONTENT"/>
ENDTPL;

// these autoblocks perform template functions
// this one creates a list of all template types
$AUTOBLOCK[content_template_types] = <<<ENDBLOCK1
SELECT DISTINCT tpl_type_id
FROM templates
WHERE tpl_theme_id=0
ORDER BY tpl_type_id
ENDBLOCK1;
// this one lists all of the templates of a given type
$AUTOBLOCK[content_templates_by_type] = <<<ENDBLOCK2
SELECT *
FROM templates
WHERE tpl_theme_id=0
AND tpl_type_id=%d
ORDER BY tpl_type_id,tpl_name
ENDBLOCK2;

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

function fcn_theme_templates($arg) {
  global $_POST;
  if (count($_POST['tpl']))
    foreach($_POST['tpl'] as $id) {
      $t = new sftemplate($id);
      $out[] = $t->get_properties();
    }
  return $out;
}

$PAGE->set_property('page_title','Content Templates');

if ($_POST['submitted']) {
  if ($_POST['export']) {
    $themename = $_POST['package'];
    $AUTOBLOCK['theme'] = "SELECT 0 AS theme_id, '$themename' AS theme_name";
    $AUTOBLOCK['theme_templates'] = 'fcn_theme_templates';
    $PAGE->set_property('xml',$EXPORTXML);
    $exportfile = $LOCAL_PATH . $FILEPATH .
      $themename . '.xml';
    $exportpath = $SITE_URL . '/' . $FILEPATH .
      $themename . '.xml';
    $fp = fopen($exportfile,'w');
    fwrite($fp,$PAGE->parse('xml'));
    fclose($fp);
    $PAGE->set_property('body',
      sprintf('<p>Files exported to <a href="%s">%s</a></p>',$exportpath,$exportfile));
    $PAGE->pparse('page');
    exit;
  }
  else if ($_POST['delete']) {
    if (count($_POST['tpl']))
      foreach($_POST['tpl'] as $id) {
        $tpl = new sftemplate($id);
        $tpl->delete();
      }
  }
}

$PAGE->set_property('_table_',$TPL);
$PAGE->set_property('body',$PAGE->parse('_table_'));
$PAGE->pparse('page');

?>