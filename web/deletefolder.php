<?php
/* deletefolder.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: deletefolder.php,v 1.6 2005/01/31 07:29:49 glen Exp $
**
** delete a folder
*/
include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_DELETEFOLDER);

$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];

// id=N parameter is required
if (!$id) {
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body);
}
else if ($_POST['submitted']) {
    $class = $_POST['class'];
    $folder = new $class($id);
    if (!iseditor($folder->get_property(folder_owner_id))) {
        $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
        $PAGE->set_property(body,'');
    }
    else {
        $folder->delete();
        if ($folder->errcount()) {
            $PAGE->set_property(error,$folder->get_errors());
        }
        else {
            $PAGE->set_property(error,_MSG_DELETED);
        }
        $PAGE->set_property(body,'');
    }
}
else {
    $class = foldertype($id);
    if ($class=='') {
        $PAGE->set_property(error,_ERR_BADFOLDER);
        $PAGE->set_property(body);
    }
    else {
        $folder = new $class($id);
        if (!iseditor($folder->get_property(folder_owner_id))) {
            $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
            $PAGE->set_property(body,'');
        }
        else {
            $title = $folder->get_property(folder_name);
            $deletelabel = _MSG_DELETE_BUTTON;
            $delform = <<<END
            <h3>Deleting: "$title"</h3>
            <form name="delete" method="post" action="$PHP_SELF">
                <input type="hidden" name="submitted" value="1"/>
                <input type="hidden" name="id" value="$id"/>
                <input type="hidden" name="class" value="$class"/>
                <input type="submit" value="$deletelabel"/>
            </form>
END;
            $PAGE->set_property(error,_MSG_DELETEFOLDER);
            $PAGE->set_property(body,$delform);
        }
    }
}

$PAGE->pparse(page);

?>
