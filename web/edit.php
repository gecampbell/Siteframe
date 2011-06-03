<?php
// edit.php
// $Id: edit.php,v 1.21 2007/09/17 00:12:53 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// edit a document
//
include "siteframe.php";
restricted();

$id = ($_POST['id'] ? $_POST['id'] : $_GET['id']);
$class = $_POST['class']!='' ? $_POST['class'] : $_GET['class'];

if (!$CURUSER) {
    header("Location: login.php?redirect=".htmlentities(urlencode("$PHP_SELF?id=$id&class=$class&parent=$parent")));
}

if ($_POST['submitted']) { // handle submission
    $doc = new $class($id);
    $doc->set_input_form_values($doc->input_form_values());
    if ($id && !iseditor($doc->get_property(doc_owner_id))) {
        $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
        $PAGE->set_property(body,'');
        $PAGE->set_property(page_title,sprintf(_TITLE_EDIT,$CLASSES[$class]));
        $PAGE->pparse(page);
        exit;
    }
    else {
        if ($id) {
            $doc->update();
        }
        else {
            $doc->set_property(doc_owner_id,$CURUSER->get_property(user_id));
            $doc->add();
        }
        if ($doc->errcount())
            $PAGE->set_property(error,$doc->get_errors());
        else {
        
        	// removing this; redirect to page to view after successful edit
        	/*
            if ($id)
                $PAGE->set_property(error,_MSG_DOCUPDATED);
            else
                $PAGE->set_property(error,_MSG_DOCCREATED);
            */
            $id = $doc->get_property(doc_id);
            header("Location: $SITE_URL/document.php?id=$id");
            exit;
        }
    }
}
if ($id) { // edit existing document
    $class = doctype($id);
}
else {
    $PAGE->set_property(doc_id,0);
    $PAGE->set_property(doc_folder_id,0);
}
if ($class == "none") {
    $PAGE->set_property('page_title',"Error");
    $PAGE->set_property('error',_ERR_NOFOLDERDOC);
    $PAGE->pparse(page);
    exit;
}
//else if (($class!='')&&($CLASSES[$class]=='')) {
//    $PAGE->set_property(error,
//      sprintf("[%s] is an invalid document class",$class));
//    $PAGE->set_property(body);
//    $PAGE->pparse(page);
//    exit;
//}
else if ($class) { // edit a defined class
    if (!$doc) {
      $doc = new $class($id);
      $folderid = $_GET['folder'] ? $_GET['folder'] : $_POST['doc_folder_id'];
      if ($folderid)
        $doc->set_property('doc_folder_id',$folderid);
    }
    if ($doc->get_property(doc_folder_id)) {
        $fx = new Folder($doc->get_property('doc_folder_id'));
        $PAGE->set_property(folder_path,
            $fx->folder_path($FOLDER_PATH_SEP,
                 $FOLDER_PATH_PREFIX,
                 $FOLDER_PATH_SEP.$doc->get_property(doc_title).$FOLDER_PATH_SUFFIX));
        $PAGE->set_property(folder_path,$PAGE->parse(folder_path));
    }
    if ($id && !iseditor($doc->get_property(doc_id),'document')) {
        $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
        $PAGE->set_property(body,'');
        $PAGE->set_property(page_title,sprintf(_TITLE_EDIT,$CLASSES[$class]));
        $PAGE->pparse(page);
        exit;
    }
    else {
        if (($id=='') && $_GET['folder']) {
            $doc->set_property(doc_folder_id,$_GET['folder']);
        }
        foreach($doc->get_properties() as $name => $val) {
            $PAGE->set_property($name,$val);
        }
        $f = $doc->input_form_values();
        $f[] = array(name => 'class',
                     type => hidden,
                     value => $class);
        $f[] = array(name => 'id',
                     type => hidden,
                     value => $id);
        $PAGE->set_property(form_instructions,$EDIT_INSTR[$class]."\n");
        $PAGE->set_property(form_instructions,_EDIT_INSTR,TRUE);
    }
}
else { // no ID, no class
    $classlist[] = _PROMPT_DOC_TYPE;
    asort($CLASSES);
    foreach($CLASSES as $a => $b)
      if ($a != 'none') $classlist[$a] = $b;
    $f = array(
        array(name => 'class',
              type => select,
              help => 'doctype',
              options => $classlist,
              prompt => _PROMPT_SELECTCLASS),
        array(name => submitted,
              type => hidden,
              value => 0),
        array(name => doc_folder_id,
              type => hidden,
              value => $_GET['folder'])
    );
    $PAGE->set_property(form_instructions,_EDIT_SELECTCLASS_INSTR);
}

// establish form variables
$PAGE->set_property(form_name,'edit');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->input_form(body,$f,'','Save');
$PAGE->set_property(page_title,sprintf(_TITLE_EDIT,$CLASSES[$class]));

// validate folder type
if ($folder && ($class!='')) {
    $fclass = foldertype($folder);
    $fg = new $fclass($folder);
    if (($fg->get_property(folder_limit_type)!='') && ($class!=$fg->get_property(folder_limit_type))) {
        $PAGE->set_property(error,sprintf(_ERR_BADTYPE,$fg->get_property(folder_limit_type)));
        $PAGE->set_property(body,'');
    }
}

// display the page
$PAGE->pparse(page);
?>
