<?php
// importtheme.php
// $Id: importtheme.php,v 1.7 2003/06/29 01:55:43 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// imports a .theme (XML) file to a theme, with optional new name

require "siteframe.php";
require "sftemplate.php";
require "uploadedfile.php";

$PAGE->set_property('page_title','Import Theme');

$inform = array(
  array(
    name => 'destination',
    type => 'hidden',
    value => $LOCAL_PATH . $FILEPATH
  ),
  array(
    name => 'userfile',
    type => 'file',
    prompt => 'Select source theme file',
    doc => 'Choose a theme file (usually .theme or .xml) from your local system'
  ),
  array(
    name => 'newname',
    type => 'text',
    size => 50,
    prompt => '(optional) New theme name',
    doc => 'You can optionally choose a new name for the imported theme'
  ),
);

// handle the uploads
if ($_POST['submitted']) {
  $themefile = new uploadedfile();
  $themefile->set_input_form_values($inform);
  $xmlstring = file_get_contents($themefile->get_property('userfile'));
  if (preg_match('/<theme_name>(.*)<\/theme_name>/',$xmlstring,$matches))
    $defaultname = $matches[1];
  if ($_POST['newname']!='') {
    $xmlstring = str_replace($defaultname,$_POST['newname'],$xmlstring);
  }
  // parse it
  $xml = xml_parser_create();
  xml_parse_into_struct($xml,$xmlstring,$values,$index);
  $errors = 0;
  foreach($values as $item) {
    $tag = strtolower($item['tag']);
    $value = $item['value'];
    switch($tag) {
      case 'theme_name':
        $th = new sftheme();
        if (substr($value,0,7) != 'CONTENT') {
          @mkdir($LOCAL_PATH.THEMEPATH.$value,0777);
          $th->set_property('theme_name',$value);
          // backup theme
          $backup = sprintf('%s.BAK',$value);
          // delete old .BAK
          @$DB->write(sprintf('DELETE FROM themes WHERE theme_name=\'%s\'',$backup));
          // create new .BAK
          @$DB->write(sprintf('UPDATE themes SET theme_name=\'%s\' WHERE theme_name=\'%s\'',
                        $backup, $value));
          // add the new theme
          $th->add();
          $PAGE->set_property('error',$th->get_errors(),TRUE);
          $errors += $th->errcount();
        }
        break;
      case 'template_name':
        $tpl = new sftemplate();
        $tpl->set_property('tpl_theme_id',$th->get_property('theme_id'));
        $tpl->set_property('tpl_name',$value);
        break;
      case 'template_type':
        $tpl->set_property('tpl_type_id',$value);
        break;
      case 'template_file':
        $tpl->set_property('tpl_filename',$value);
        break;
      case 'template_body':
        $value = trim($value);
        $tpl->set_property('tpl_body',$value);
        // compare with existing version
        $r = $DB->read(sprintf(
          'SELECT tpl_body FROM templates WHERE tpl_theme_id=%d AND '.
          'tpl_name=\'%s\'',
          $tpl->get_property('tpl_theme_id'),
          $tpl->get_property('tpl_name')));
        list($oldbody) = $DB->fetch_array($r);
        // only save if the new one is different
        if ($oldbody != $value) {
          // create backup name
          $backup = sprintf('%s.BAK',$tpl->get_property('tpl_name'));
          // delete existing backup version
          @$DB->write(
            sprintf('DELETE FROM templates WHERE tpl_name=\'%s\' AND '.
                    'tpl_theme_id=%d',
              $backup,
              $tpl->get_property('tpl_theme_id'))
          );
          // rename existing template to .BAK
          @$DB->write(
            sprintf('UPDATE templates SET tpl_name=\'%s\' WHERE tpl_name=\'%s\' AND '.
                    'tpl_theme_id=%d',
              $backup,
              $tpl->get_property('tpl_name'),
              $tpl->get_property('tpl_theme_id'))
          );
          $DB->write(
            sprintf('DELETE FROM templates WHERE tpl_name=\'%s\' AND '.
                    'tpl_theme_id=0',addslashes($tpl->get_property('tpl_name')))
          );
          $tpl->add();
          if ($tpl->get_property('tpl_filename')!='') {
            $fp = fopen($LOCAL_PATH.$tpl->get_property('tpl_filename'),'w');
            if (!$fp)
              $tpl->add_error('Unable to open %s for writing',
                $tpl->get_property('tpl_filename'));
            else {
              fwrite($fp,$tpl->get_property('tpl_body'));
              fclose($fp);
            }
          }
          $PAGE->set_property('error',$tpl->get_errors(),TRUE);
          $errors += $tpl->errcount();
        }
        break;
    }
  }
  xml_parser_free($xml);
  if (!$errors) {
    $PAGE->set_property('error','Import successful');
  }
}

$PAGE->set_property('form_instructions',
  'Select the theme file from your local system and optionally provide a new '.
  'name for the theme, then press <b>Submit</b> to import.');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('body',$inform);

$PAGE->pparse('page');
?>
