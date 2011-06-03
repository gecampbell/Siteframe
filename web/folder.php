<?php
/* folder.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: folder.php,v 1.12 2003/06/07 01:27:23 glen Exp $
**
** displays a folder
*/
include "siteframe.php";

$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];

if (!$id) {
    $PAGE->set_property(page_title,'Error: No Folder');
    $PAGE->set_property(error,_ERR_NOID);
    $PAGE->set_property(body,'');
}
else {
    $class = foldertype($id);
    if ($class=='') {
        $PAGE->set_property(error,'Non-existent folder');
        $PAGE->set_property(body,'');
    }
    else {
        $folder = new $class($id);
        if ($class == 'CFolder') {
          if ($_POST['submitted']) {
            $folder->process();
            $PAGE->set_property(error,'Your vote was recorded');
          }
          if (strtotime("now") > strtotime($folder->get_property('folder_end_voting'))) {
            switch($folder->get_property('folder_competition_type')) {
            case 'max':
              $AUTOBLOCK[competition_docs] = sprintf(
                "SELECT docs.doc_id,SUM(rating) ".
                "FROM docs LEFT OUTER JOIN ratings ON ".
                " (docs.doc_id=ratings.doc_id) ".
                "WHERE docs.doc_folder_id=%d ".
                "GROUP BY doc_id ".
                "ORDER BY 2 DESC",
                $folder->get_property('folder_id'));
              break;
            case 'maxavg':
              $AUTOBLOCK[competition_docs] = sprintf(
                "SELECT docs.doc_id,AVG(rating) ".
                "FROM docs LEFT OUTER JOIN ratings ON ".
                " (docs.doc_id=ratings.doc_id) ".
                "WHERE docs.doc_folder_id=%d ".
                "GROUP BY doc_id ".
                "ORDER BY 2 DESC",
                $folder->get_property('folder_id'));
              break;
            case 'vote':
              $AUTOBLOCK[competition_docs] = sprintf(
                "SELECT docs.doc_id,COUNT(*) ".
                "FROM docs LEFT OUTER JOIN ratings ON ".
                " (docs.doc_id=ratings.doc_id) ".
                "WHERE docs.doc_folder_id=%d ".
                "GROUP BY doc_id ".
                "ORDER BY 2 DESC",
                $folder->get_property('folder_id'));
              break;
            }
          }
        }
        $ALLOW_COMMENTS = $folder->get_property(allow_comments);
        $ALLOW_RATINGS = $folder->get_property(allow_ratings);
        $PAGE->set_property(allow_comments,$ALLOW_COMMENTS);
        $PAGE->set_property(allow_ratings,$ALLOW_RATINGS);
        $PAGE->set_property(page_title,clean($folder->get_property(folder_name)));
        $limit = $folder->get_property(folder_limit_type);
        $PAGE->set_property(folder_path,
            $folder->folder_path($FOLDER_PATH_SEP,$FOLDER_PATH_PREFIX,$FOLDER_PATH_SUFFIX));
        $PAGE->set_property(folder_path,$PAGE->parse(folder_path));
        if ($limit != '') {
            $PAGE->set_property(_body_,$folder->display($TEMPLATES[$class][$limit]));
        }
        else {
            $PAGE->set_property(_body_,$folder->display($TEMPLATES[$class][0]));
        }
        $PAGE->set_property(body,$PAGE->parse(_body_));
    }
}
$PAGE->pparse(page);

?>
