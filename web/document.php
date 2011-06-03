<?php
// document.php
// $Id: document.php,v 1.19 2004/03/16 06:48:49 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// display a document
//
$page_start = microtime();
include "siteframe.php";

$full = $_GET['full'];
$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];;
$tag = $_GET['tag'];

if ($full) {
    $PAGE->set_property(image_full_size,1);
}

if ($tag!='') {
    $r = $DB->read("SELECT doc_id FROM docs WHERE doc_tag='$tag'");
    list($id) = $DB->fetch_array($r);
}
if (!$id) {
    $PAGE->set_property(page_title,'Error: No Document');
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body);
}
else {
    $class = doctype($id);
    if (trim($class)=='') {
        $PAGE->set_property(error,sprintf("No document with ID %d",$id));
        $PAGE->set_property(body,'');
    }
    else {
        $doc = new $class($id);
        $PAGE->set_property('main_doc_id',$id);
        if ($_GET['folder'])
            $f = $_GET['folder'];
        else
            $f = $doc->get_property(doc_folder_id);
        if ($f) {
            $fclass = foldertype($f);
            $fx = new $fclass($f);
            $r = $DB->read($fx->folder_docs_sql());
            $previd = 0;
            $nextid = 0;
            $foo_id = 0;
            while ($foo_id != $id) {
                $dbrow = @$DB->fetch_array($r);
                if (!$dbrow)
                    break;
                $foo_id = $dbrow['doc_id'];
                if ($foo_id != $id)
                    $previd = $foo_id;
            }
            $dbrow = @$DB->fetch_array($r);
            if ($dbrow)
                $nextid = $dbrow['doc_id'];
            if ($_GET['folder']) {
                if ($nextid)
                    $PAGE->set_property(next_id,$nextid.'&amp;folder='.$f);
                if ($previd)
                    $PAGE->set_property(prev_id,$previd.'&amp;folder='.$f);
            }
            else {
                if ($nextid)
                    $PAGE->set_property(next_id,$nextid);
                if ($previd)
                    $PAGE->set_property(prev_id,$previd);
            }
            $PAGE->set_property(folder_path,
                $fx->folder_path($FOLDER_PATH_SEP,
                    $FOLDER_PATH_PREFIX,
                    $FOLDER_PATH_SEP.$doc->get_property(doc_title).$FOLDER_PATH_SUFFIX));
            $PAGE->set_property(folder_path,$PAGE->parse(folder_path));
        }
        // $num_words = str_word_count(strip_tags($doc->get_property('doc_body')));
        // $PAGE->set_property('doc_num_words',$num_words+0);
        $ALLOW_COMMENTS = $doc->get_property(allow_comments);
        $ALLOW_RATINGS = $doc->get_property(allow_ratings);
        $PAGE->set_property(allow_comments,$ALLOW_COMMENTS);
        $PAGE->set_property(allow_ratings,$ALLOW_RATINGS);
        $PAGE->set_property(page_title,clean($doc->get_property(doc_title)));
        $PAGE->set_property(_doc_,$doc->display($TEMPLATES[$class]));
        $PAGE->set_property(body,macro($PAGE->parse(_doc_)));
    }
}
$PAGE->pparse(page);
/*
logmsg("DEBUG:start=%s,end=%s",$page_start,microtime());
logmsg("DEBUG:num_reads=%d,num_writes=%d",$DB->num_reads,$DB->num_writes);
printf('<!-- reads(%s) -->',print_r($DB->reads,TRUE));
*/
?>
