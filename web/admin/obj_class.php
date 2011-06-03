<?php
// obj_class.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: obj_class.php,v 1.9 2003/05/11 05:55:52 glen Exp $
//
// use this as a template

require "siteframe.php";

// class definition
class ObjProperty extends Siteframe {

  // constructor
  function ObjProperty($id=0) {
    global $DB;
    if ($id) {
      $q = "SELECT * FROM obj_props WHERE obj_prop_id=$id";
      $r = $DB->read($q);
      $data = $DB->fetch_array($r);
      $this->set_property('obj_id',$data['obj_id']);
      $this->set_property('obj_prop_id',$data['obj_prop_id']);
      $this->set_property('obj_prop_name',stripslashes($data['obj_prop_name']));
      $this->set_property('obj_prop_seq',$data['obj_prop_seq']);
      $this->set_property('obj_prop_type',$data['obj_prop_type']);
      $this->set_property('obj_prop_size',$data['obj_prop_size']);
      $this->set_property('obj_prop_prompt',stripslashes($data['obj_prop_prompt']));
      $this->set_property('obj_prop_doc',stripslashes($data['obj_prop_doc']));
      $this->set_property('obj_prop_admin',$data['obj_prop_admin']);
      $this->set_property('obj_prop_options',stripslashes($data['obj_prop_options']));
    }
  }

  // set_property - check values
  function set_property($name,$value) {
    switch($name) {
      case 'obj_prop_name':
        $value = strtolower(clean($value));
        if ($value == '')
          $this->add_error(sprintf('invalid name "%s", id=%d',
                            $value,
                            $this->get_property('obj_prop_id')));
        if (strpos($value,' '))
          $this->add_error('property name cannot contain spaces');
        parent::set_property($name,clean($value));
        break;
      case 'obj_prop_prompt':
        $value = trim($value);
        if ($value == '')
          $this->add_error('prompt string cannot be blank');
        parent::set_property($name,$value);
        break;
      case 'obj_prop_size':
        switch($this->get_property('obj_prop_type')) {
          case 'text':
          case 'password':
            if ($value == 0)
              $this->add_error("Size is required for properties of this type");
            break;
        }
        parent::set_property($name,$value+0);
        break;
      default:
        parent::set_property($name,$value);
    }
  }

  // input_form_values - define input form
  function input_form_values($suffix=0) {
    $form = array (
              array(
                type => "ignore",
                prompt => !$this->get_property('obj_prop_id') ?
                            "<h2>Add a new property</h2>" :
                            sprintf("<a name=\"prop$suffix\"/><h2>%s</h2>",
                              $this->get_property('obj_prop_name'))
              ),
              array(
                name => "obj_prop_name_$suffix",
                type => "text",
                size => 250,
                value => $this->get_property('obj_prop_name'),
                doc => 'The property name cannot contain spaces',
                prompt => "Name"
              ),
              array(
                name => "obj_prop_seq_$suffix",
                type => "text",
                size => 3,
                value => $this->get_property('obj_prop_seq'),
                doc => 'This number determines the order in which the properties appear',
                prompt => "Sequence number"
              ),
              array(
                name => "obj_prop_type_$suffix",
                type => "select",
                options => array(
                            'text' => "Text",
                            'password' => "Password",
                            'textarea' => "Extended Text",
                            'date' => "Date",
                            'checkbox' => "Checkbox",
                            'select' => "Drop-down list"
                           ),
                value => $this->get_property('obj_prop_type'),
                prompt => "Data Type",
                doc => "Select the type of data used by this property"
              ),
              array(
                name => "obj_prop_size_$suffix",
                type => "text",
                size => 4,
                value => $this->get_property('obj_prop_size'),
                doc => 'For text and password fields, this determines the max number of characters allowed',
                prompt => "Size (optional)"
              ),
              array(
                name => "obj_prop_options_$suffix",
                type => "textarea",
                value => $this->get_property('obj_prop_options'),
                prompt => "Options (optional)",
                doc => "For drop-down lists, enter all options separated by semicolons. ".
                       "For checkbox items, use this field for a value to be displayed ".
                       "when checked."
              ),
              array(
                name => "obj_prop_prompt_$suffix",
                type => "text",
                size => 250,
                value => $this->get_property('obj_prop_prompt'),
                doc => 'The prompt string displayed on an input form for this property',
                prompt => "Prompt"
              ),
              array(
                name => "obj_prop_doc_$suffix",
                type => "textarea",
                value => $this->get_property('obj_prop_doc'),
                doc => 'The documentation paragraph supplied for the property (like this paragraph you\'re reading now',
                prompt => "Documentation"
              ),
              array(
                name => "obj_prop_admin_$suffix",
                type => "checkbox",
                rval => 1,
                value => $this->get_property('obj_prop_admin'),
                doc => 'If checked, this property will only be visible to site administrators',
                prompt => "Only visible to administrators"
              ),
            );
    if ($this->get_property('obj_prop_id')) {
      $form[] = array(
                  name => "delete_$suffix",
                  type => "checkbox",
                  value => 0,
                  rval => 1,
                  doc => 'Check this box to delete the property',
                  prompt => "Check to delete property"
                );
    }
    return $form;
  }

  // add - add a new property
  function add() {
    global $DB;
    if ($this->errcount()) return;
    $q = sprintf(
          "INSERT INTO obj_props (obj_id,obj_prop_name,obj_prop_type,".
          "obj_prop_size,obj_prop_prompt,obj_prop_doc,obj_prop_admin,".
          "obj_prop_seq,obj_prop_options) VALUES (".
          "%d,'%s','%s',%d,'%s','%s',%d,%d,'%s')",
          $this->get_property('obj_id'),
          addslashes($this->get_property('obj_prop_name')),
          $this->get_property('obj_prop_type'),
          $this->get_property('obj_prop_size'),
          addslashes($this->get_property('obj_prop_prompt')),
          addslashes($this->get_property('obj_prop_doc')),
          $this->get_property('obj_prop_admin'),
          $this->get_property('obj_prop_seq'),
          addslashes($this->get_property('obj_prop_options'))
          );
    $r = $DB->write($q);
    $this->add_error(mysql_error());
  }

  // update - update an existing property
  function update() {
    global $DB;
    if (!$this->get_property('obj_prop_id'))
      $this->add_error('cannot update property with id=0');
    if ($this->errcount()) return;
    $q = sprintf(
          "UPDATE obj_props SET ".
          " obj_prop_name='%s',".
          " obj_prop_type='%s',".
          " obj_prop_size=%d,".
          " obj_prop_prompt='%s',".
          " obj_prop_doc='%s',".
          " obj_prop_admin=%d,".
          " obj_prop_seq=%d, ".
          " obj_prop_options='%s' ".
          "WHERE obj_prop_id=%d",
          addslashes($this->get_property('obj_prop_name')),
          $this->get_property('obj_prop_type'),
          $this->get_property('obj_prop_size'),
          addslashes($this->get_property('obj_prop_prompt')),
          addslashes($this->get_property('obj_prop_doc')),
          $this->get_property('obj_prop_admin'),
          $this->get_property('obj_prop_seq'),
          addslashes($this->get_property('obj_prop_options')),
          $this->get_property('obj_prop_id')
         );
    $r = $DB->write($q);
    $this->add_error(mysql_error());
  }

  // delete - delete a property
  function delete() {
    global $DB;
    if (!$this->get_property('obj_prop_id'))
      $this->add_error('cannot delete property with id=0');
    if ($this->errcount()) return;
    $q = sprintf("DELETE FROM obj_props WHERE obj_prop_id=%d",
          $this->get_property('obj_prop_id'));
    $r = $DB->write($q);
    $this->add_error(mysql_error());
  }

} // end of OptProperties class



// main processing begins here

$id = $_GET['class'] ? $_GET['class'] : $_POST['class'];

if ($id) {
  $q = "SELECT obj_active,obj_class,obj_class_file FROM objs WHERE obj_id=$id";
  $r = $DB->read($q);
  list ($active,$class,$classfile) = $DB->fetch_array($r);
  $PAGE->set_property('page_title',
    sprintf('Editing %s (%s)',
      $class, $active ? 'active' : 'inactive'));
}
else {
  $PAGE->set_property('page_title','No class');
  $PAGE->set_property('error',"This page must be invoked with the class=ID parameter\n");
  $PAGE->pparse('page');
  exit;
}

// if submitted, process entries
if ($_POST['submitted']) {
  // check for new properties
  $newprop = new ObjProperty(0);
  $newprop->set_property('obj_id',$_POST['class']);
  if (trim($_POST['obj_prop_name_0']!='')) {
    $newprop->set_property('obj_prop_name',$_POST['obj_prop_name_0']);
    $newprop->set_property('obj_prop_type',$_POST['obj_prop_type_0']);
    $newprop->set_property('obj_prop_size',$_POST['obj_prop_size_0']);
    $newprop->set_property('obj_prop_prompt',$_POST['obj_prop_prompt_0']);
    $newprop->set_property('obj_prop_doc',$_POST['obj_prop_doc_0']);
    $newprop->set_property('obj_prop_admin',$_POST['obj_prop_admin_0']);
    $newprop->set_property('obj_prop_seq',$_POST['obj_prop_seq_0']);
    $newprop->set_property('obj_prop_options',$_POST['obj_prop_options_0']);
    if (!$newprop->errcount()) {
      $newprop->add();
    }
    else {
      $PAGE->set_property('error',$newprop->get_errors()." ",true);
    }
  }
  // update/delete existing properties
  $q = "SELECT obj_prop_id FROM obj_props WHERE obj_id=$id ".
       "ORDER BY obj_prop_seq,obj_prop_name";
  $r = $DB->read($q);
  while(list($pid) = $DB->fetch_array($r)) {
    $prop = new ObjProperty($pid);
    if ($_POST["delete_$pid"]) {
      $prop->delete();
      if (!$prop->errcount()) {
        $PAGE->set_property(
          'error',
          sprintf("Deleted property %s",$prop->get_property('obj_prop_name')),
          true);
      }
    }
    else if ($_POST["obj_prop_name_$pid"]!='') {
      $prop->set_property('obj_prop_name',$_POST["obj_prop_name_$pid"]);
      $prop->set_property('obj_prop_type',$_POST["obj_prop_type_$pid"]);
      $prop->set_property('obj_prop_size',$_POST["obj_prop_size_$pid"]);
      $prop->set_property('obj_prop_prompt',$_POST["obj_prop_prompt_$pid"]);
      $prop->set_property('obj_prop_doc',$_POST["obj_prop_doc_$pid"]);
      $prop->set_property('obj_prop_admin',$_POST["obj_prop_admin_$pid"]);
      $prop->set_property('obj_prop_seq',$_POST["obj_prop_seq_$pid"]);
      $prop->set_property('obj_prop_options',$_POST["obj_prop_options_$pid"]);
      $prop->update();
    }
    $PAGE->set_property('error',$prop->get_errors()." ",true);
  }
}

// establish new property form
$newprop = new ObjProperty();
$form = $newprop->input_form_values();

// establish form values for existing properties
$q = "SELECT obj_prop_id FROM obj_props WHERE obj_id=$id ORDER BY obj_prop_seq,obj_prop_name";
$r = $DB->read($q);
while(list($pid) = $DB->fetch_array($r)) {
  $prop = new ObjProperty($pid);
  $jumplist .= sprintf('<li><a href="#prop%d">%s</a></li>',
                $pid,
                $prop->get_property('obj_prop_name'));
  $iform = $prop->input_form_values($pid);
  foreach ($iform as $va) {
    $form[] = $va;
  }
}

$form[] = array(
            name => "class",
            type => "hidden",
            value => $id
          );

// add instructions to the page
$instr = <<<ENDINSTR
<p>Use this form to edit or delete any of the optional object properties
for the class "$class".
These properties are used to extend the default set of properties for
an object; however, if you inadvertently duplicate the name of an
existing property, you can cause all sorts of nasty problems for
yourself. Since this is an adminstrative function, I'm leaving it
all up to you to implement properly. Don't come whining to me
if you mess up.</p>
<p>Click here to jump directly to a property:</p>
<ul>
$jumplist
</ul>
ENDINSTR;

// define input form etc.
$PAGE->set_property(form_name,'obj_properties');
$PAGE->set_property(form_action,"$PHP_SELF?class=$id");
$PAGE->set_property(form_instructions,$instr);
$PAGE->input_form(body,$form);

// display the page
$PAGE->pparse('page');
?>
