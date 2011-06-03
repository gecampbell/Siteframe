<?php
/* deleteuser.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: deleteuser.php,v 1.5 2003/06/07 01:27:23 glen Exp $
**
** Used to delete a user
*/
include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_DELETEUSER);

$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];

if (!$id) {
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body,'');
}
else if (!iseditor($id)) {
    $PAGE->set_property(error,_ERR_NOTAUTHORIZED);
    $PAGE->set_property(body,'');
}
else if ($_POST['submitted']) {
    $u = new User($id);
    $u->delete();
    if ($u->errcount()) {
        $PAGE->set_property(error,$u->get_errors());
    }
    else {
        $PAGE->set_property(error,_MSG_DELETED);
    }
    $PAGE->set_property(body,'');
}
else {
    $u = new User($id);
    $uname = $u->get_property(user_name);
    $deletelabel = _MSG_DELETE_BUTTON;
    $delform = <<<END
    <h3>Deleting: $uname</h3>
    <form name="delete" method="post" action="$PHP_SELF">
        <input type="hidden" name="submitted" value="1"/>
        <input type="hidden" name="id" value="$id"/>
        <input type="submit" value="$deletelabel"/>
    </form>
END;
    $PAGE->set_property(error,_MSG_DELETEUSER);
    $PAGE->set_property(body,$delform);
}

$PAGE->pparse(page);
?>
