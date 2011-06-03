<?php
// search.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: search.php,v 1.9 2003/06/07 01:27:23 glen Exp $

include "siteframe.php";

$PAGE->load_template(_search_,$TEMPLATES[Search]);
$PAGE->block(_search_,search_results,search_results_item);
$PAGE->set_property(form_instructions,_MSG_SEARCH_INSTR);
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->set_property(search_results,'');

$searchfor = $_POST['searchfor'];

if ($_POST['submitted']) {
    $search_users = $_POST['search_users'];
    $search_documents = $_POST['search_documents'];
    $search_folders = $_POST['search_folders'];
    $search_comments = $_POST['search_comments'];
    if ($search_users||$search_documents||$search_folders||$search_comments) {
        // do nothing
    }
    else {
        // nothing's set; set everything
        $search_users=1;
        $search_documents=1;
        $search_folders=1;
        $search_comments=1;
    }
    $searchstr = addslashes($searchfor);
    $found = 0;
    if ($search_users) {
        $r = $DB->read("SELECT user_id FROM users ".
                        "WHERE MATCH(user_firstname,user_lastname,user_nickname,user_props) ".
                        "AGAINST('$searchstr')");
        while(list($id) = $DB->fetch_array($r)) {
            $usr = new User($id);
            $PAGE->set_property(search_result_type,'User');
            $PAGE->set_property(search_name,$usr->get_property(user_name));
            $PAGE->set_property(search_url,
                sprintf("%s/user.php?id=%d",
                    $PAGE->get_property(site_path),
                    $usr->get_property(user_id)));
            $PAGE->set_property(search_results,$PAGE->parse(search_results_item),true);
            $found++;
        }
    }
    if ($search_documents) {
        $r = $DB->read("SELECT doc_id FROM docs ".
                        "WHERE MATCH(doc_title,doc_body,doc_props) ".
                        "AGAINST('$searchstr') ");
        while(list($id) = $DB->fetch_array($r)) {
            $class = doctype($id);
            $doc = new $class($id);
            $PAGE->set_property(search_result_type,$CLASSES[$class]);
            $PAGE->set_property(search_name,$doc->get_property(doc_title));
            $PAGE->set_property(search_url,
                sprintf("%s/document.php?id=%d",
                    $PAGE->get_property(site_path),
                    $doc->get_property(doc_id)));
            $PAGE->set_property(search_results,$PAGE->parse(search_results_item),true);
            $found++;
        }
    }
    if ($search_folders) {
        $r = $DB->read("SELECT folder_id FROM folders ".
                        "WHERE MATCH(folder_name,folder_body,folder_props) ".
                        "AGAINST('$searchstr')");
        while(list($id) = $DB->fetch_array($r)) {
            $class = foldertype($id);
            $f = new $class($id);
            $PAGE->set_property(search_result_type,'Folder');
            $PAGE->set_property(search_name,$f->get_property(folder_name));
            $PAGE->set_property(search_url,
                sprintf("%s/folder.php?id=%d",
                    $PAGE->get_property(site_path),
                    $f->get_property(folder_id)));
            $PAGE->set_property(search_results,$PAGE->parse(search_results_item),true);
            $found++;
        }
    }
    if ($search_comments) {
        $r = $DB->read("SELECT doc_id FROM comments ".
                        "WHERE MATCH(body,comment_props) ".
                        "AGAINST('$searchstr')");
        while(list($id) = $DB->fetch_array($r)) {
            $class = doctype($id);
            $doc = new $class($id);
            $PAGE->set_property(search_result_type,"Comment on ".$CLASSES[$class]);
            $PAGE->set_property(search_name,$doc->get_property(doc_title));
            $PAGE->set_property(search_url,
                sprintf("%s/document.php?id=%d",
                    $PAGE->get_property(site_path),
                    $doc->get_property(doc_id)));
            $PAGE->set_property(search_results,$PAGE->parse(search_results_item),true);
            $found++;
        }
    }
    $PAGE->set_property(num_results,$found ? $found : 'No');
    $PAGE->set_property(search_users,$search_users);
    $PAGE->set_property(search_documents,$search_documents);
    $PAGE->set_property(search_folders,$search_folders);
    $PAGE->set_property(search_comments,$search_comments);
}
else {
    $PAGE->set_property(search_results,'');
    $PAGE->set_property(num_results,'');
    $PAGE->set_property(search_users,1);
    $PAGE->set_property(search_documents,1);
    $PAGE->set_property(search_folders,1);
    $PAGE->set_property(search_comments,1);
}

$PAGE->set_property(searchfor,$searchfor ? $searchfor : '');
$PAGE->set_property(body,$PAGE->parse(_search_));
$PAGE->set_property(page_title,_TITLE_SEARCH);
$PAGE->pparse(page);
?>
