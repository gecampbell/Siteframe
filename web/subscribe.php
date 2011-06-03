<?php
// subscribe.php
// $Id: subscribe.php,v 1.8 2003/06/21 23:40:12 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
//
// This page handles subscriptions to a folder, group, or document.
// A "subscription" is a request to be notified when something
// changes. What's a change?
// For a document, a change is whenever it is modified by edit.php.
// It is *NOT* whenever the document is updated; for example, a DocFile
// document gets updated every time someone downloads the file
// attachment (to count the number of downloads). Only when an editor
// actually modifies the file does a "change" event occur.
// A change also occurs when the document is deleted.
// For a folder, a change occurs when:
// - a document is added to the folder
// - a document is removed from the folder (or deleted)
// It does *NOT* occur when the folder is updated; this is merely
// useless information
// For a group, a change occurs when:
// - a user is added to the group
// - a user is removed from the group (or deleted)

define(_ERR_SUBSCR_INVALID_PARAMETERS,'This script has been invoked with invalid parameters. Check the URL and try again.');

require "siteframe.php";

// get_object
function get_object($type,$id) {
  global $DB;
  switch($type) {
  case 'D':
    if ($id)
      $class = doctype($id);
    else
      $class = 'Document';
    break;
  case 'F':
    if ($id)
      $class = foldertype($id);
    else
      $class = 'Folder';
    break;
  case 'G':
    $class = 'Group';
    break;
  case 'U':
    $class = 'User';
    break;
  default:
    siteframe_abort('get_object() invoked with invalid object type %s',$type);
  }
  if ($class == '')
    return FALSE;
  $object = new $class($id);
  return $object;
}

$PAGE->set_property('page_title','Subscription');
if (!$CURUSER) {
  $PAGE->set_property('error',_ERR_SUBSCR_NOTLOGGEDIN);
  $PAGE->pparse('page');
  exit;
}

// this page must be invoked with either
//  id=N          to edit an existing subscription
// or
//  obj=N&type=C  to create a new subscription
//                on object ID "obj" of type "type"

if ($_POST['submitted']) { // handle submitted form
  $sub = new Subscription($_POST['subscr_id']);
  $sub->set_input_form_values($sub->input_form_values());
  if ($sub->errcount()) {
    $PAGE->set_property('error',$sub->get_errors());
  }
  else if ($_POST['subscr_id']) {
    $sub->update();
    if (!$sub->errcount())
      $PAGE->set_property('error',_MSG_SUBSCR_UPDATED);
    else
      $PAGE->set_property('error',$sub->get_errors());
  }
  else {
    $sub->add();
    if (!$sub->errcount())
      $PAGE->set_property('error',_MSG_SUBSCR_ADDED);
    else
      $PAGE->set_property('error',$sub->get_errors());
  }
  if (!$sub->errcount()) {
    $object = get_object($sub->get_property('subscr_obj_type'),
                      $sub->get_property('subscr_obj_id'));
  }
}
else if ($_GET['id']) {
  $sub = new Subscription($_GET['id']);
  $object = get_object($sub->get_property('subscr_obj_type'),
                    $sub->get_property('subscr_obj_id'));
}
else if ($_GET['type']!='') {
  // make sure that obj and type is a valid object
  $object = get_object($_GET['type'],$_GET['obj']);
  if (!$object)
    $PAGE->set_property(_ERR_INVALID_OBJ,$_GET['type'],$_GET['obj']);
  else {
    // check to see if we already have a subscription to that object
    $q = sprintf('SELECT subscr_id FROM subscriptions WHERE '.
                'subscr_owner_id=%d AND subscr_obj_type=\'%s\' AND '.
                'subscr_obj_id=%d',
                $CURUSER->get_property('user_id'),
                $_GET['type'],
                $_GET['obj']);
    $r = $DB->read($q);
    list($id) = $DB->fetch_array($r);
    $sub = new Subscription($id);
    $sub->set_property('subscr_owner_id',$CURUSER->get_property('user_id'));
    $sub->set_property('subscr_obj_type',$_GET['type']);
    $sub->set_property('subscr_obj_id',$_GET['obj']);
  }
}
else {
  $PAGE->set_property('error',_ERR_SUBSCR_INVALID_PARAMETERS);
  $PAGE->pparse('page');
  exit;
}

// set PAGE doc_id,folder_id,doc_folder_id, etc. for form navigation

if ($object) {
  $title = trim($object->title());
  if ($title=='')
    $title = 'All '.get_class($object).'s';
  else
    $title = '"'.$title.'"';
  if ($sub->get_property('subscr_id'))
    $PAGE->set_property('page_title',
      sprintf(_TITLE_SUBSCR_EDIT,$title));
  else
    $PAGE->set_property('page_title',
      sprintf(_TITLE_SUBSCR_NEW,$title));
}
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->set_property('form_instructions',_PROMPT_SUBSCR_INSTRUCTIONS);
$PAGE->set_property('form_instructions',
  sprintf('<p class="action"><a href="%s/subscriptions.php?id=%d">'.
          'All subscriptions</a></p>',
          $SITE_PATH,$sub->get_property('subscr_owner_id')),
  TRUE);
$PAGE->input_form('body',$sub->input_form_values(),'',_PROMPT_SUBSCR_SUBMIT);

$PAGE->pparse('page');
?>
