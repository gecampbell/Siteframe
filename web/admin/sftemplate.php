<?php
// sftemplate.php
// $Id: sftemplate.php,v 1.18 2003/06/27 05:28:37 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
//
// defines two classes, sftheme and sftemplate (for themes and templates)

// template types
$SFTPLTYPE[0] = 'Generic';
$SFTPLTYPE[1] = 'Home Page';
$SFTPLTYPE[2] = 'Navigation';
$SFTPLTYPE[3] = 'Footer';
$SFTPLTYPE[4] = 'Macro';
$SFTPLTYPE[99]= 'Custom';

// returns only the name portion of a filepath
function fname_only($str) {
    $s = strrchr($str,'/');
    if ($s == '') return $str;
    return substr($s,1,strlen($s)-1);
}

// -----------------------------------------------------------------------

// sftheme is a Theme object
class sftheme extends Siteframe {

  // sftheme - create a new theme
  function sftheme($id=0) {
    global $DB;
    if ($id) {
      $r = $DB->read("SELECT * FROM themes WHERE theme_id=$id");
      $this->add_error($DB->error());
      $data = $DB->fetch_array($r);
      if (count($data)) {
        foreach($data as $name => $value) {
          // this prevents setting the numeric column numbers as properties
          if (substr($name,0,6)=='theme_')
            $this->set_property($name,$value);
        }
      }
    }
  }

  // add - add a new theme
  function add() {
    global $DB;
    $q = sprintf(
          'INSERT INTO themes (theme_created,theme_modified,theme_name) '.
          'VALUES (NOW(),NOW(),\'%s\')',
          $this->get_property('theme_name'));
    $DB->write($q);
    $this->add_error($DB->error());
    $this->set_property('theme_id',$DB->insert_id());
  }

  // update - modify existing theme
  function update() {
    global $DB;
    $q = sprintf('UPDATE themes SET theme_modified=NOW(),theme_name=\'%s\' '.
                 'WHERE theme_id=%d',addslashes($this->get_property('theme_id')));
    $DB->write($q);
    $this->add_error($DB->error());
  }

  // delete - delete a theme
  function delete() {
    global $DB,$LOCAL_PATH;
    // delete all the theme template files
    $q = sprintf('SELECT tpl_filename FROM templates WHERE tpl_theme_id=%d',
          $this->get_property('theme_id'));
    $r = $DB->read($q);
    while(list($fname) = $DB->fetch_array($r)) {
      @unlink($LOCAL_PATH.$fname);
    }
    // delete all the theme templates
    @$DB->write(sprintf('DELETE FROM templates WHERE tpl_theme_id=%d',
                  $this->get_property('theme_id')));
    // delete the theme itself
    @$DB->write(sprintf('DELETE FROM themes WHERE theme_id=%d',
                  $this->get_property('theme_id')));
  }

  // set_property - validate data
  function set_property($name,$value) {
    switch($name) {
    case 'theme_name':
      $value = preg_replace('/[^a-zA-Z0-9-_ ]+/','',$value);
      $value = preg_replace('/ +/',' ',$value);
      parent::set_property($name,$value);
      break;
    default:
      parent::set_property($name,$value);
    }
  }

  // input_form_values() - build an input form
  function input_form_values() {
    return array(
      array(
        name => 'theme_id',
        type => 'hidden',
        value => $this->get_property('theme_id')
      ),
      array(
        name => 'theme_name',
        type => text,
        size => 50,
        value => $this->get_property('theme_name'),
        prompt => 'Theme name',
        doc => 'Enter the name of the theme here (max 50 characters)'
      )
    );
  }

} // end sftheme

// -----------------------------------------------------------------------

// sftemplate is a Template object
class sftemplate extends Siteframe {

  // constructor - create a new Template
  function sftemplate($id=0) {
      global $DB;
      if ($id) {
          $r = $DB->read("SELECT * FROM templates WHERE tpl_id=$id");
          $this->add_error($DB->error());
          $data = $DB->fetch_array($r);
          if (count($data)) {
              foreach($data as $name => $value) {
                  if (substr($name,0,4)=='tpl_')
                      $this->set_property($name,$value);
              }
          }
      }
  }

  function add() {
      global $DB,$LOCAL_PATH;
      $q = sprintf(
          'INSERT INTO templates '.
          '(tpl_created,tpl_modified,tpl_theme_id,tpl_type_id,tpl_name,tpl_filename,tpl_body)'.
          'VALUES (NOW(),NOW(),      %d,         %d,\'%s\',  \'%s\',      \'%s\')',
          $this->get_property('tpl_theme_id'),
          $this->get_property('tpl_type_id'),
          addslashes($this->get_property('tpl_name')),
          addslashes($this->get_property('tpl_filename')),
          addslashes($this->get_property('tpl_body')));
      $DB->write($q);
      $file = trim($this->get_property('tpl_filename'));
      if ($file!='') {
        $fp = @fopen($LOCAL_PATH.$file,'w');
        if (!$fp)
          $this->add_error('Unable to open %s for writing',$file);
        else {
          fputs($fp,$this->get_property('tpl_body'));
          fclose($fp);
        }
      }
      $this->add_error($DB->error());
      $this->set_property('tpl_id',$DB->insert_id());
      logmsg('Added template "%s", id=%d',
        $this->get_property('tpl_name'),
          $this->get_property('tpl_id'));
  }

  function update() {
      global $DB;
      $q = sprintf(
          'UPDATE templates '.
          ' SET tpl_modified=NOW(),'.
          '     tpl_theme_id=%d,'.
          '     tpl_type_id=%d,'.
          '     tpl_name=\'%s\','.
          '     tpl_filename=\'%s\','.
          '     tpl_body=\'%s\''.
          'WHERE tpl_id=%d',
          $this->get_property('tpl_theme_id'),
          $this->get_property('tpl_type_id'),
          addslashes($this->get_property('tpl_name')),
          addslashes($this->get_property('tpl_filename')),
          addslashes($this->get_property('tpl_body')),
          $this->get_property('tpl_id'));
      $DB->write($q);
      $this->add_error($DB->error());
      logmsg('Updated template "%s", id=%d',
        $this->get_property('tpl_name'),
          $this->get_property('tpl_id'));
  }

  function delete() {
      global $DB;
      $DB->write(sprintf('DELETE FROM templates WHERE tpl_id=%d',
                  $this->get_property('tpl_id')));
      $this->add_error($DB->error());
      logmsg('Deleted template "%s", id=%d',
        $this->get_property('tpl_name'),
          $this->get_property('tpl_id'));
  }

  function get_property($name) {
    switch($name) {
    case 'tpl_filename_only':
      return fname_only($this->get_property('tpl_filename'));
    default:
      return parent::get_property($name);
    }
  }

  function get_properties() {
    $a = parent::get_properties();
    $a['tpl_filename_only'] = $this->get_property('tpl_filename_only');
    if ($this->get_property('tpl_theme_id')) {
      $th = new sftheme($this->get_property('tpl_theme_id'));
      $a = array_merge($a,$th->get_properties());
    }
    return $a;
  }

  function set_property($name,$value) {
      global $LOCAL_PATH;
      switch($name) {
      case 'tpl_name':
          parent::set_property($name,str_replace('.ihtml','',fname_only($value)));
          break;
      case 'tpl_filename':
          $value = str_replace('//','/',$value);
          $value = str_replace($LOCAL_PATH,'',$value);
          parent::set_property($name,$value);
          break;
      default:
          parent::set_property($name,$value);
      }
  }

  // return the complete template file's name
  function fullname() {
    global $LOCAL_PATH;
    return $LOCAL_PATH.$this->get_property('tpl_filename');
  }

  function input_form_values() {
    global $DB,$SFTPLTYPE;
    $a = array(
        array(
            name => 'tpl_theme_id',
            type => 'hidden',
            value => $this->get_property('tpl_theme_id')
        ),
        array(
            name => tpl_id,
            type => hidden,
            value => $this->get_property('tpl_id')
        ),
        array(
            name => tpl_name,
            type => text,
            size => 50,
            value => $this->get_property('tpl_name'),
            prompt => 'Name',
            doc => 'This is the name by which the template will be '.
                   'referred to.'
        ),
        array(
            name => tpl_filename,
            type => text,
            size => 50,
            value => $this->get_property('tpl_filename'),
            prompt => 'Filename',
            disabled => ($this->get_property('tpl_theme_id')==0),
            doc => 'The file where the template will reside on disk. '.
                   'You can edit either the template in the file or '.
                   'in the database, and the newer of the two will be '.
                   'loaded when editing.'
        ),
        array(
          name => 'tpl_type_id',
          type => 'select',
          options => $SFTPLTYPE,
          value => $this->get_property('tpl_type_id'),
          prompt => 'Template Type',
          doc => 'Select whether this is a generic template or is used '.
                 'for one of the special purposes shown.'
        ),
        array(
            name => tpl_body,
            type => textarea,
            rows => 30,
            cols => 100,
            value => $this->get_property('tpl_body'),
            prompt => 'Template Text',
            doc => 'Note that any HTML tags are allowable here '.
                   '(unlike most other Siteframe text fields).'
        )
    );
    return $a;
  }

} // end sftemplate

?>