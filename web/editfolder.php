<?php
// editfolder.php
// $Id: editfolder.php,v 1.21 2004/07/24 23:06:28 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
//  add/edit a folder
//
include "siteframe.php";
restricted();

$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
$class = $_POST['class'] ? $_POST['class'] : $_GET['class'];

if (!$CURUSER) {
    header("Location: login.php?redirect=".htmlentities(urlencode("$PHP_SELF?id=$id&class=$class&parent=$parent")));
}

if ($_POST['submitted']) { // handle submission
    $folder = new $_POST['class']($id);
    $folder->set_input_form_values($folder->input_form_values());
    if ($id) {
        $folder->update();
    }
    else {
        $folder->set_property(folder_owner_id,$CURUSER->get_property(user_id));
        $folder->add();
    }
    if ($folder->errcount())
        $PAGE->set_property(error,$folder->get_errors());
    else {
        if ($id)
            $PAGE->set_property(error,_MSG_FOLDERUPDATED);
        else
            $PAGE->set_property(error,_MSG_FOLDERCREATED);
        $id = $folder->get_property(folder_id);
    }
}
$PAGE->set_property(doc_id,0);
if ($id) { // edit existing folder
    $r = $DB->read("SELECT folder_type FROM folders WHERE folder_id=$id");
    list($class) = $DB->fetch_array($r);
}
else {
    $PAGE->set_property(doc_folder_id,0);
    $parent = $_GET['parent'] ? $_GET['parent'] : $_POST['parent'];
    if (!$parent)
        $parent = $DEFAULT_FOLDER_PARENT+0;
}
if ($class) { // edit a defined class
    if (!$folder)
      $folder = new $class($id);
    $PAGE->set_property(folder_path,
        $folder->folder_path($FOLDER_PATH_SEP,$FOLDER_PATH_PREFIX,$FOLDER_PATH_SUFFIX));
    $PAGE->set_property(folder_path,$PAGE->parse(folder_path));
    $PAGE->set_property(folder_id,$folder->get_property(folder_id));
    $PAGE->set_property(folder_name,$folder->get_property(folder_name));
    if ($parent) {
        $folder->set_property(folder_parent_id,$parent);
    }
    $f = $folder->input_form_values();
    $f[] = array(name => 'class',
                 type => hidden,
                 value => $class);
    $f[] = array(name => 'id',
                 type => hidden,
                 value => $id);
    $PAGE->set_property(form_instructions,_EDIT_INSTR);
}
else { // no ID, no class
    $f = array(
        array(name => 'class',
              type => select,
              options => $FOLDERS,
              value => $DEFAULT_FOLDER_TYPE,
              prompt => _PROMPT_SELECTFCLASS),
        array(name => submitted,
              type => hidden,
              value => 0)
    );
    $f[] = array(name => 'parent',
                 type => hidden,
                 value => $parent);
    $PAGE->set_property(form_instructions,_EDIT_SELECTCLASS_INSTR);
    $PAGE->set_property(form_instructions,_EDIT_FOLDERCLASS_INSTR,true);
}

// establish form variables
$PAGE->set_property(form_name,'edit');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->input_form(body,$f);
$PAGE->set_property(page_title,sprintf(_TITLE_EDIT,$FOLDERS[$class]));
// display the page
$PAGE->pparse(page);
?>
