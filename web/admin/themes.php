<?php
// themes.php
// $Id: themes.php,v 1.6 2003/06/09 15:29:00 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// used for listing and editing themes

require "siteframe.php";
require "sftemplate.php";

$AUTOBLOCK[all_themes] = "SELECT * FROM themes ORDER BY theme_name";
$themeform = <<<ENDTHEMEFORM
<p>Click on the theme name to edit the theme, or check the box to
delete it.</p>
<p class="action"><a href="edittheme.php">Create new theme</a></p>
<form method="post" action="$PHP_SELF" enctype="multipart/form-data">
<table>
<tr>
  <th>Theme </th>
  <th>Delete </th>
</tr>
  {BEGIN:all_themes}
  <tr class="{row_class}">
    <td><a href="edittheme.php?id={theme_id}">
    {!if '"{theme_name}"=="{theme}"'
      '<b>{theme_name}</b> (Current)'
      '{theme_name}'
    !}</a></td>
    <td align="center"><input type="checkbox" name="del[]" value="{theme_id}"/>
        <input type="hidden" name="id" value="{theme_id}"/></td>
  </tr>
  {END:all_themes}
</table>
<input type="hidden" name="submitted" value="1"/>
<input type="submit" value="Submit"/>
</form>
ENDTHEMEFORM;

if ($_POST['submitted']) {
  if (is_array($_POST['del'])) {
    foreach($_POST['del'] as $val) {
      $th = new sftheme($val);
      if ($th->get_property('theme_name')=='default') {
        $PAGE->set_property('error',"The \"default\" theme cannot be deleted<br/>\n",TRUE);
      }
      else {
        $th->delete();
      }
    }
  }
}

$PAGE->set_property('page_title','Themes');
$PAGE->set_property('body',
  ''."\n",TRUE);
$PAGE->set_property('_themes_',$themeform);
$PAGE->set_property('body',$PAGE->parse('_themes_'),TRUE);
$PAGE->pparse('page');

?>
