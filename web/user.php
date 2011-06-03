<?php
/* user.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: user.php,v 1.10 2003/06/07 01:27:24 glen Exp $
**
** Displays all information on a given user
*/
include "siteframe.php";
restricted();

$PAGE->set_property('page_title',_TITLE_USER);
$page = $_GET['page'];
$id = $_GET['id'];
$PAGE->load_template('user_page', ($page=='') ? $TEMPLATES[User] : $page);

if ($id) { // display information on requested user
    $u = new User($id);
    if ($u->get_property('user_id')) {
        $PAGE->set_property('page_title',
            sprintf('%s %s',
                $u->get_property('user_firstname'),
                $u->get_property('user_lastname')));
        $a = $u->get_properties();
        $PAGE->set_property('user_user_last_login','');
        foreach($a as $name => $val) {
            $PAGE->set_property("user_$name",$val);
        }
        $PAGE->set_property('_user_',$PAGE->parse('user_page'));
    }
    else {
        $PAGE->set_property('error',sprintf(_ERR_NOUSER,$id));
        $PAGE->set_property('_user_','');
    }
}
else if ($CURUSER && $CURUSER->get_property('user_id')) { // display information on current user (self)
    $PAGE->set_property('page_title',
        sprintf('%s %s',
            $CURUSER->get_property('user_firstname'),
            $CURUSER->get_property('user_lastname')));
    $a = $CURUSER->get_properties();
    foreach($a as $name => $val) {
        $PAGE->set_property("user_$name",$val);
    }
    $PAGE->set_property('_user_',$PAGE->parse('user_page'));
}
else { // problem - this is an error
    $PAGE->set_property('error',_ERR_BADUSER);
    $PAGE->set_property('_user_','');
}
$PAGE->set_property(doc_id,0);
$PAGE->set_property(doc_folder_id,0);
$PAGE->set_property(folder_id,0);
$PAGE->set_property('body',$PAGE->parse('_user_'));
$PAGE->pparse('page');
?>