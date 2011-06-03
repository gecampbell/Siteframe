<?php
/* delete.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: delete.php,v 1.10 2003/06/25 04:35:20 glen Exp $
**
** delete a document
*/
include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_DELETE);

$comment = $_GET['comment'];
$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];

// if comment=N is specified, delete and then exit
if ($comment) {
    $co = new Comment($comment);
    if (iseditor($co->get_property(comment_owner_id))) {
        $co->delete();
        header("Location: document.php?id=".$co->get_property(comment_doc_id));
    }
    else {
        $PAGE->set_property($co->get_errors());
        $PAGE->set_property(body,'');
        $PAGE->pparse(page);
        exit;
    }
}
// id=N parameter is required
if (!$id) {
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body);
}
else if ($_POST['submitted']) {
    $doc = new $_POST['class']($id);
    if (!iseditor($doc->get_property(doc_owner_id))) {
        $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
        $PAGE->set_property(body,'');
    }
    else {
        $doc->delete($_POST['reason']);
        if ($doc->errcount()) {
            $PAGE->set_property(error,$doc->get_errors());
        }
        else {
            $PAGE->set_property(error,_MSG_DELETED);
        }
        $PAGE->set_property(body,'');
    }
}
else {
    $r = $DB->read("SELECT doc_type FROM docs WHERE doc_id=$id");
    list($class) = $DB->fetch_array($r);
    if ($class=='') {
        $PAGE->set_property(error,_ERR_BADDOC);
        $PAGE->set_property(body);
    }
    else {
        $doc = new $class($id);
        if (!iseditor($doc->get_property(doc_owner_id))) {
            $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
            $PAGE->set_property(body,'');
        }
        else {
            $title = $doc->get_property(doc_title);
            $deletelabel = _MSG_DELETE_BUTTON;
            $delete_form = array(
                array(name => 'id',
                      type => 'hidden',
                      value => $id),
                array(name => 'class',
                      type => 'hidden',
                      value => $class),
                array(name => 'reason',
                      type => 'textarea',
                      rows => 3,
                      cols => 20,
                      focus => TRUE,
                      prompt => 'Reason (optional)',
                      doc => 'You can choose to enter a reason for deleting this object here')
            );
            $PAGE->input_form(body,$delete_form,'',$deletelabel);
            $PAGE->set_property(error,_MSG_DELETEDOC);
        }
    }
}

$PAGE->pparse(page);

?>
