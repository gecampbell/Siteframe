<?php
// autoblocks
// $Id: autoblocks.php,v 1.82 2007/09/29 17:10:05 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.


// categories
$AUTOBLOCK[categories] = "SELECT * FROM categories ORDER BY cat_name";
$AUTOBLOCK[all_categories] = $AUTOBLOCK[categories];

// comments
$AUTOBLOCK[doc_comments] = "SELECT comment_id FROM comments WHERE comment_doc_id=%d ORDER BY comment_created";
$AUTOBLOCK[all_comments] = "SELECT comment_id FROM comments ORDER BY comment_created DESC";

// documents
$AUTOBLOCK[all_documents] = "SELECT * FROM docs ORDER BY doc_title";
$AUTOBLOCK[all_notices] = "SELECT * FROM docs WHERE doc_type='Notice' ORDER BY doc_created";
// those two have to go first, since they're referenced below
$AUTOBLOCK[active_ads] = "SELECT * FROM docs WHERE doc_type='Ad' AND doc_folder_id=%d ORDER BY doc_created";
$AUTOBLOCK[active_notices] = $AUTOBLOCK[all_notices];
$AUTOBLOCK[all_docs] = $AUTOBLOCK[all_documents];
$AUTOBLOCK[category_docs] = "SELECT docs.doc_id,docs.doc_created,docs.doc_modified,doc_hidden,doc_folder_id,doc_owner_id,doc_tag,doc_type,doc_title,doc_body,doc_props FROM docs LEFT JOIN doc_categories ON (docs.doc_id=doc_categories.doc_id) WHERE doc_cat_id=%d ORDER BY doc_title,docs.doc_id";
$AUTOBLOCK[docs_of_type] = "SELECT * FROM docs WHERE doc_type='%s' ORDER BY doc_title";
$AUTOBLOCK[docs_of_type_created] = "SELECT * FROM docs WHERE doc_type='%s' ORDER BY doc_created,doc_title";
$AUTOBLOCK[document] = "SELECT * FROM docs WHERE doc_id=%d";
$AUTOBLOCK[documents_today] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY doc_title";
// $AUTOBLOCK[folder_docs] = "SELECT * FROM docs WHERE doc_folder_id=%d AND doc_hidden=0 ORDER BY %s";
// $AUTOBLOCK[folder_docs] = "SELECT * FROM docs JOIN users ON (docs.doc_owner_id=users.user_id) WHERE doc_folder_id=%d AND doc_hidden=0 ORDER BY %s";
//$AUTOBLOCK[folder_docs] = "SELECT * FROM docs,users WHERE (docs.doc_owner_id=users.user_id) AND doc_folder_id=%d AND doc_hidden=0 ORDER BY %s";
$AUTOBLOCK[folder_docs] = "SELECT * FROM docs INNER JOIN users ON (docs.doc_owner_id=users.user_id) WHERE doc_folder_id=%d AND doc_hidden=0 ORDER BY %s";
$AUTOBLOCK[month_days] = "SELECT * FROM docs WHERE doc_id=%d";
$AUTOBLOCK[query] = "%s";
$AUTOBLOCK[recent_articles] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_type='Article' ORDER BY doc_id DESC LIMIT %d";
$AUTOBLOCK[recent_days] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY doc_id DESC";
$AUTOBLOCK[recent_docs] = "SELECT * FROM docs WHERE doc_hidden=0 ORDER BY doc_id DESC LIMIT %d";
$AUTOBLOCK[recent_folder_docs_10] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_folder_id=%d ORDER BY doc_id DESC LIMIT 10";
$AUTOBLOCK[recent_images] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_type='Image' ORDER BY doc_id DESC LIMIT %d";
$AUTOBLOCK[recent_images_days] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_type='Image' AND doc_created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY doc_created DESC";
$AUTOBLOCK[recent_articles] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_type='Article' ORDER BY doc_id DESC LIMIT %d";
$AUTOBLOCK[recent_articles_days] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_type='Article' AND doc_created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY doc_created DESC";
$AUTOBLOCK[user_docs] = "SELECT * FROM docs WHERE doc_owner_id=%d ORDER BY doc_title";
$AUTOBLOCK[user_docs_type] = "SELECT * FROM docs WHERE doc_owner_id=%d ORDER BY doc_type,doc_title";
$AUTOBLOCK[user_images] = "SELECT * FROM docs WHERE doc_owner_id=%d AND doc_type='Image' ORDER BY doc_title";
$AUTOBLOCK[weblog] = "SELECT * FROM docs WHERE doc_hidden=0 AND doc_created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY doc_id DESC";

// for weblogs
// the last N days that have doc_created
$AUTOBLOCK[doc_days] = "SELECT DISTINCT DATE_FORMAT(doc_created,'%%Y-%%m-%%d') AS day FROM docs WHERE doc_created>=DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY 1 DESC";
// get all docs.doc_created on a certain day
$AUTOBLOCK[day_docs] = "SELECT * FROM docs WHERE DATE_FORMAT(doc_created,'%%Y-%%m-%%d')='%s' ORDER BY doc_created DESC";

// documents, plus something
$AUTOBLOCK['current'] = "SELECT doc_id FROM schedule WHERE begin_date<=NOW() AND end_date>=NOW() AND folder_id=%d";
$AUTOBLOCK[competition_docs] = "SELECT doc_id FROM docs WHERE doc_folder_id=%d ORDER BY doc_created";
$AUTOBLOCK[doc_ratings] = "SELECT doc_id,AVG(rating) FROM ratings GROUP BY doc_id ORDER BY 2 DESC";
$AUTOBLOCK[folder_doc_events] = "SELECT docs.doc_id FROM events,docs WHERE (docs.doc_id=events.doc_id) AND doc_folder_id=%d ORDER BY event_begin,event_end";
$AUTOBLOCK[image_ratings] = "SELECT ratings.doc_id,AVG(rating) FROM ratings,docs WHERE (ratings.doc_id = docs.doc_id) AND doc_type='Image' GROUP BY doc_id ORDER BY 2 DESC";
$AUTOBLOCK[image_ratings_limit] = "SELECT ratings.doc_id,AVG(rating),COUNT(*) FROM ratings,docs WHERE (ratings.doc_id = docs.doc_id) AND doc_type='Image' GROUP BY doc_id HAVING COUNT(*) > %d ORDER BY 2 DESC";
$AUTOBLOCK[most_commented] = "SELECT docs.doc_id,COUNT(*) FROM docs LEFT JOIN comments ON docs.doc_id=comments.comment_doc_id GROUP BY doc_id ORDER BY 2 DESC,docs.doc_created DESC";
$AUTOBLOCK[most_commented_images] = "SELECT docs.doc_id,COUNT(*) FROM docs LEFT JOIN comments ON docs.doc_id=comments.comment_doc_id WHERE doc_type='Image' GROUP BY doc_id ORDER BY 2 DESC,docs.doc_created DESC";
$AUTOBLOCK[on_this_day] = "SELECT doc_id FROM events WHERE MONTH(event_begin)=MONTH(NOW()) AND DAYOFMONTH(event_begin)=DAYOFMONTH(NOW()) ORDER BY event_begin";
$AUTOBLOCK[random] = "SELECT doc_id FROM docs WHERE doc_id=%d";
$AUTOBLOCK[schedule] = "SELECT docs.doc_id FROM schedule LEFT JOIN docs ON (schedule.doc_id=docs.doc_id) WHERE schedule.folder_id=%d ORDER BY schedule.begin_date";
$AUTOBLOCK[top_rated] = "SELECT doc_id FROM docs WHERE doc_id=%d AND doc_hidden=0";
$AUTOBLOCK[top_rated_month] = "SELECT doc_id FROM docs WHERE doc_id=%d AND doc_hidden=0";
$AUTOBLOCK[top_rated_week] = "SELECT doc_id FROM docs WHERE doc_id=%d AND doc_hidden=0";
$AUTOBLOCK[upcoming_events] = "SELECT events.doc_id FROM events LEFT JOIN docs ON (events.doc_id=docs.doc_id) WHERE (event_begin > NOW()) AND (event_begin < DATE_ADD(NOW(),INTERVAL %d DAY)) ORDER BY event_begin,event_end";
$AUTOBLOCK[user_doc_ratings] = "SELECT ratings.doc_id,rating FROM ratings LEFT JOIN docs ON (docs.doc_id=ratings.doc_id) WHERE ratings.user_id=%d ORDER BY doc_title";

// users
$AUTOBLOCK[all_users] = "SELECT * FROM users WHERE user_status>0 ORDER BY user_lastname,user_firstname,user_created";
$AUTOBLOCK[favorite_users] = "SELECT doc_owner_id,COUNT(*),AVG(rating) FROM ratings LEFT JOIN docs ON (ratings.doc_id=docs.doc_id) WHERE user_id=%d GROUP BY doc_owner_id HAVING COUNT(*)>1 ORDER BY 3 DESC";
$AUTOBLOCK[query_users] = "%s";
$AUTOBLOCK[recent_users] = "SELECT * FROM users WHERE user_status>0 ORDER BY user_id DESC LIMIT %d";
$AUTOBLOCK[user] = "SELECT * FROM users WHERE user_id=%d";
$AUTOBLOCK[users_today] = "SELECT * FROM users WHERE user_status>0 AND user_created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY user_lastname,user_firstname";

// folders
$AUTOBLOCK[all_folders] = "SELECT * FROM folders ORDER BY folder_name";
$AUTOBLOCK[folder] = "SELECT * FROM folders WHERE folder_id=%d";
$AUTOBLOCK[folder_children] = "SELECT * FROM folders,users WHERE (folders.folder_owner_id=users.user_id) AND folder_parent_id=%d ORDER BY %s";
$AUTOBLOCK[folder_ratings] = "SELECT *,AVG(rating) AS AVERAGE,COUNT(*) AS NUMRATINGS FROM ratings LEFT JOIN docs ON (ratings.doc_id=docs.doc_id) LEFT JOIN folders ON (docs.doc_folder_id=folders.folder_id) GROUP BY folder_id ORDER BY AVERAGE DESC";
$AUTOBLOCK[folder_subfolders] = $AUTOBLOCK[folder_children];
$AUTOBLOCK[folder_subfolders_rev] = "SELECT * FROM folders,users WHERE (folders.folder_owner_id=users.user_id) AND folder_parent_id=%d ORDER BY %s DESC";
$AUTOBLOCK[folders_today] = "SELECT * FROM folders WHERE created>DATE_SUB(NOW(),INTERVAL %d DAY) ORDER BY folder_name";
$AUTOBLOCK[private_folders] = "SELECT * FROM folders WHERE folder_public=0 ORDER BY folder_name";
$AUTOBLOCK[public_folders] = "SELECT * FROM folders WHERE folder_public!=0 ORDER BY folder_name";
$AUTOBLOCK[recent_folders] = "SELECT * FROM folders ORDER BY folder_id DESC LIMIT %d";
$AUTOBLOCK[user_folders] = "SELECT * FROM folders WHERE folder_owner_id=%d ORDER BY folder_name";

// sessions
$AUTOBLOCK[sessions_day] = "SELECT DATE_FORMAT(session_date,'%%Y-%%m-%%d'),COUNT(*) FROM sessions GROUP BY 1 ORDER BY 1";
$AUTOBLOCK[sessions_total] = "SELECT MIN(session_date),MAX(session_date),COUNT(*) FROM sessions";

// rss feed
$AUTOBLOCK[rss] = "%s";

// get_folder_group
// when passed a dbrow (database return array), returns a
// concatenated string of the folder's group data
function get_folder_group($folder_sorted,$dbrow) {
    global $PAGE;
    if ($PAGE->get_property('folder_group')) {
        switch($folder_sorted) {
        case 'user':
            return $dbrow['user_firstname'].' '.$dbrow['user_lastname'];
        case 'type':
            return $dbrow['doc_type'];
        default:
            return '';
        }
    }
    else
        return '';
}

// autoblock_replace($m) - callback function
function autoblock_replace($m) {
    global $CACHED_VALUE_UPDATE_TIME,$AUTOBLOCK,$DB,
        $PAGE,$offset,$LINES_PER_PAGE,$PHP_SELF,
        $page,$id,$SYMBOLS;
    $PAGE->push();
    $fcn = $m[1];
    $arg = $m[2];
    $opt = $m[3];
    $bod = $m[4];
    $tval = sprintf("_%8d_",rand());
    if ($arg[0] == '"')
        $arg = str_replace('"','',$arg);
    else if ($arg[0] == "'")
        $arg = str_replace("'","",$arg);
    else if (($arg[0] == '-') && ($opt=='')) {
        $opt = $arg;
        $arg = '';
    }
    else if (!preg_match('/\d+/',$arg))
        $arg = $PAGE->get_property($arg);
    $AUTOBLOCK[$fcn] = trim($AUTOBLOCK[$fcn]);
    // if the autoblock is the name of a function
    // it should return an array
    if (function_exists($AUTOBLOCK[$fcn])) {
      $PAGE->set_property($matched_str,'');
      $bod = str_replace('{%BEGIN','{BEGIN',$bod);
      $bod = str_replace('{%END','{END',$bod);
      $PAGE->set_property($tval,$bod);
      $arr = $AUTOBLOCK[$fcn]($arg);
      if ($opt!='')
          $out = $opt;
      else
          $out = $fcn;
      $PAGE->set_property($out,'');
      $count = 0;
      if (count($arr))
        foreach($arr as $row) {
          ++$count;
          $PAGE->push();
          foreach($row as $n => $v)
            $PAGE->set_property($n,$v);
          $PAGE->set_property(row_number,$count);
          $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
          $PAGE->set_global($out,$PAGE->parse($tval),true);
          $PAGE->pop();
        }
      return "{".$out."}";
    }
    else if ($AUTOBLOCK[$fcn]!= '') {
        $PAGE->set_property($matched_str,'');
        $bod = str_replace('{%BEGIN','{BEGIN',$bod);
        $bod = str_replace('{%END','{END',$bod);
        $PAGE->set_property($tval,$bod);
        $recset = ($opt=='-r');

        /* This initial case statement performs pre-processing on some
        ** statements. For example, the "folder_docs" slot can have an
        ** alternate sort method
        */
        switch($fcn) {
        case 'folder_subfolders':
        case 'folder_subfolders_rev':
        case 'folder_children':
            if ($arg == 0) {
                $sortby = 'folders.folder_name';
            }
            else {
                $fx = new Folder($arg);
                $sortby = $fx->subfolder_orderby();
                if (($fx->get_property('folder_limit_type')=='none') &&
                    ($fx->get_property('folder_pages')))
                    $recset = 1;
            }
            $q = sprintf($AUTOBLOCK[$fcn],$arg,$sortby);
            break;
        case 'folder_docs':
            // $sortby = get_folder_doc_sort($arg);
            /*
            if ($arg == 0) { //
                $q = sprintf($AUTOBLOCK[$fcn],-1,'docs.doc_created');
                break;
            }
            */
            if ($arg) {
                $fclass = foldertype($arg);
                $fx = new $fclass($arg);
                if ($fx->get_property('folder_pages'))
                    $recset = 1;
                $q = $fx->folder_docs_sql();
            }
            else
                $q = sprintf($AUTOBLOCK[$fcn],0,'doc_id');
            break;
            /*
            $sortby = $fx->doc_orderby();
            $q = sprintf($AUTOBLOCK[$fcn],$arg,$sortby);
            break;
            */
        case 'random':
            $r = $DB->read("SELECT MIN(doc_id),MAX(doc_id) FROM docs WHERE doc_type='$arg'");
            list($min,$max) = $DB->fetch_array($r);
            $try = 0;
            $val = 0;
            while(!$val && ($try++ < 20)) {
                $randnum = rand($min,$max);
                $r = $DB->read(sprintf("SELECT doc_id FROM docs ".
                                        "WHERE doc_type='%s' AND doc_id>=%d ".
                                        "ORDER BY doc_created",
                                        $arg,
                                        $randnum));
                list($val) = $DB->fetch_array($r);
                // logmsg("debug: random number=%d, val=%d",$randnum,$val);
            }
            $q = sprintf($AUTOBLOCK[$fcn],$val);
            break;
        case 'top_rated':
            $r = $DB->read("SELECT docs.doc_id,AVG(rating) FROM ".
                            "ratings LEFT JOIN docs ON ratings.doc_id=docs.doc_id ".
                            "WHERE doc_type='$arg' ".
                            "  AND doc_hidden=0 ".
                            "GROUP BY docs.doc_id ".
                            "ORDER BY 2 DESC, doc_created DESC LIMIT 1");
            list($val) = $DB->fetch_array($r);
            $q = sprintf($AUTOBLOCK[$fcn],$val);
            break;
        case 'top_rated_month':
            $gvar = strtoupper("TOP_RATED_MONTH_$arg");
            $gvartime = strtoupper("TOP_RATED_MONTH_TIME_$arg");
            if ((time() - $GLOBALS[$gvartime]) > ($CACHED_VALUE_UPDATE_TIME*60)) {
                $r = $DB->read("SELECT docs.doc_id,AVG(rating) FROM ".
                                "ratings LEFT JOIN docs ON ratings.doc_id=docs.doc_id ".
                                "WHERE doc_type='$arg' AND ".
                                "doc_created>DATE_SUB(NOW(),INTERVAL 30 DAY) ".
                                " AND doc_hidden=0 ".
                                "GROUP BY docs.doc_id ".
                                "ORDER BY 2 DESC, doc_created DESC LIMIT 1");
                list($val,$rating) = $DB->fetch_array($r);
                set_global($gvar,$val+0);
                set_global($gvartime,time());
            }
            else {
                $val = $GLOBALS[$gvar];
            }
            $q = sprintf($AUTOBLOCK[$fcn],$val);
            break;
        case 'top_rated_week':
            $gvar = strtoupper("TOP_RATED_WEEK_$arg");
            $gvartime = strtoupper("TOP_RATED_WEEK_TIME_$arg");
            if ((time() - $GLOBALS[$gvartime]) > ($CACHED_VALUE_UPDATE_TIME*60)) {
                $r = $DB->read("SELECT docs.doc_id,AVG(rating) FROM ".
                                "ratings LEFT JOIN docs ON ratings.doc_id=docs.doc_id ".
                                "WHERE doc_type='$arg' AND ".
                                "doc_created>DATE_SUB(NOW(),INTERVAL 7 DAY) ".
                                " AND doc_hidden=0 ".
                                "GROUP BY docs.doc_id ".
                                "HAVING COUNT(*) > 2 ".
                                "ORDER BY 2 DESC, doc_created DESC LIMIT 1");
                list($val,$rating) = $DB->fetch_array($r);
                set_global($gvar,$val+0);
                set_global($gvartime,time());
            }
            else {
                $val = $GLOBALS[$gvar];
            }
            $q = sprintf($AUTOBLOCK[$fcn],$val);
            break;
        default:
            $q = sprintf($AUTOBLOCK[$fcn],$arg);
        }

        switch($fcn) {
        case 'rss':
          break;
        default:
          $r = $DB->read($q);
//if (!$r) printf('[e] error=%s fcn=%s ',mysql_error(),$fcn);
        }

        // produce recordset
        if ($recset) {
            $PHP_SELF = $_SERVER['PHP_SELF'];
            $offset = $_GET['offset'];
            $PAGE->set_global(recordset,recordset($r,"$PHP_SELF?page=$page&id=$id",$offset,$LINES_PER_PAGE));
            $pos = 0;
            while ($pos < $offset) {
                $DB->fetch_array($r);
                $pos++;
            }
            $pos = 0;
            $pmax = $LINES_PER_PAGE;
        }
        else {
            $pmax = 999999;
            $pos = 0;
        }

        if ($DB->error()!='')
            siteframe_abort('Autoblock [fcn=%s] error=%s [q=%s]',$fcn,$DB->error(),$q);

        if ($opt!='')
            $out = $opt;
        else
            $out = $fcn;

        $out = str_replace('%',rand(),$out);

        $PAGE->set_global($out,''); // start clean

        $count = 0;

        switch($fcn) {

        case 'sessions_day':
            while((list($day,$num) = $DB->fetch_array($r)) && ($pos++ < $pmax)) {
                ++$count;
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $PAGE->set_property(folder_limit_type,'');
                $PAGE->set_property(session_date,$day);
                $PAGE->set_property(session_count,$num);
                $PAGE->set_global($out,$PAGE->parse($tval),true);
                $num_total += $num;
            }
            break;
        case 'sessions_total':
            list($min,$max,$total) = $DB->fetch_array($r);
            $PAGE->set_property(session_date_min,$min);
            $PAGE->set_property(session_date_max,$max);
            $PAGE->set_property(session_count_total,$total);
            $PAGE->set_global($out,$PAGE->parse($tval),true);
            break;

        case 'all_users':
        case 'recent_users':
        case 'query_users':
        case 'users_today':
        case 'user':
            while(($dbrow = $DB->fetch_array($r)) && ($pos++ < $pmax)){
                ++$count;
                $PAGE->push();
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $PAGE->set_property(folder_limit_type,'');
                $PAGE->set_property(user_user_selfportrait,'');
                $PAGE->set_property(user_user_homepage,'');
                foreach($SYMBOLS as $a => $b)
                  $PAGE->set_property($a,'');
                $user = new User(0,$dbrow);
                foreach($user->get_properties() as $name => $value) {
                    $PAGE->set_property("user_$name",$value);
                    if ($SYMBOLS[$name]!='')
                      $SYMBOLS["user_$name"] = $SYMBOLS[$name];
                }
                $PAGE->set_global($out,$PAGE->parse($tval),true);
                $PAGE->pop();
            }
            break;

        case 'favorite_users':
            while((list($uid,$ucount,$uavg) = $DB->fetch_array($r)) && ($pos++ < $pmax)){
                ++$count;
                $PAGE->push();
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $PAGE->set_property(folder_limit_type,'');
                $PAGE->set_property(user_user_selfportrait,'');
                $PAGE->set_property(favorite_user_rating,$uavg);
                $PAGE->set_property(favorite_user_rating_count,$ucount);
                $PAGE->set_property(user_user_homepage,'');
                foreach($SYMBOLS as $a => $b)
                  $PAGE->set_property($a,'');
                $ruser = $DB->read("SELECT * FROM users WHERE user_id=$uid");
                $dbrow = $DB->fetch_array($ruser);
                $user = new User(0,$dbrow);
                foreach($user->get_properties() as $name => $value) {
                    $PAGE->set_property("user_$name",$value);
                }
                $PAGE->set_global($out,$PAGE->parse($tval),true);
                $PAGE->pop();
            }
            break;


        case 'all_folders':
        case 'folder_children':
        case 'folder_subfolders':
        case 'folder_subfolders_rev':
        case 'folder_ratings':
        case 'private_folders':
        case 'public_folders':
        case 'recent_folders':
        case 'user_folders':
        case 'folders_today':
        case 'folder':
            $previous_group = -1;
            while(($dbrow = $DB->fetch_array($r)) && ($pos++ < $pmax)){
                ++$count;
                // set parent folder data
                if ($dbrow[folder_parent_id]&&($pos==1)) {
                    $foldx = new Folder($dbrow[folder_parent_id]);
                    foreach ($foldx->get_properties() as $n => $v) {
                        $PAGE->set_property('parent_'.$n,$v);
                    }
                }

                $PAGE->push();

                // check for folder group break
                if ($PAGE->get_property('folder_group')) {
                    $newgroup = get_folder_group($PAGE->get_property('folder_sorted'),$dbrow);
                    if ($newgroup != $previous_group) {
                        $PAGE->set_property('folder_group_break',1);
                    }
                    else {
                        $PAGE->set_property('folder_group_break',0);
                    }
                    $previous_group = $newgroup;
                }

                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $PAGE->set_property(folder_limit_type,'');
                $PAGE->set_property(folder_rating,$dbrow['AVERAGE']);
                $PAGE->set_property(folder_rating_count,$dbrow['NUMRATINGS']);
                $class = $dbrow['folder_type'];
                $folder = new $class(0, $dbrow);
                foreach($folder->get_properties() as $name => $value) {
                    $PAGE->set_property($name,$value);
                }
                $PAGE->set_global($out,$PAGE->parse($tval),true);
                $PAGE->pop();
            }
            break;

        case 'month_days':
            // arg should be YYYYMM
            $y = substr($arg,0,4);
            $m = substr($arg,4,2);
            $dfirst = sprintf("%04d-%02d-%02d",$y,$m,1);
            $rx = $DB->read("SELECT DATE_ADD('$dfirst',INTERVAL 1 MONTH)");
            list($nextmonth) = $DB->fetch_array($rx);
            $rx = $DB->read("SELECT DATE_SUB('$dfirst',INTERVAL 1 MONTH)");
            list($prevmonth) = $DB->fetch_array($rx);
            $PAGE->set_property(next_year,substr($nextmonth,0,4));
            $PAGE->set_property(next_month,substr($nextmonth,5,2));
            $PAGE->set_property(prev_year,substr($prevmonth,0,4));
            $PAGE->set_property(prev_month,substr($prevmonth,5,2));
            $PAGE->block($tval,day_events,day_events_item);
            for($i=1;$i<=31;$i++) {
                $PAGE->set_property(day_events,'');
                $dtval = getdate(strtotime($dx=sprintf("%04d-%02d-%02d",$y,$m,$i)));
                if ($dtval[mon]==$m) {
                    foreach($dtval as $name => $val) {
                        $PAGE->set_property($name,$val);
                    }
                    $qx = sprintf("SELECT doc_id FROM events ".
                                  "WHERE DATE_FORMAT(event_begin,'%%Y-%%m-%%d')='%04d-%02d-%02d'",
                            $y, $m, $i);
                    $rx = $DB->read($qx);
                    while(list($idx) = $DB->fetch_array($rx)) {
                        $docx = new Event($idx);
                        foreach($docx->get_properties() as $n => $v) {
                            $PAGE->set_property($n,$v);
                        }
                        $dtval = getdate(strtotime($docx->get_property(event_begin)));
                        foreach($dtval as $name => $val) {
                            $PAGE->set_property($name,$val);
                        }
                        $PAGE->set_property(day_events,$PAGE->parse(day_events_item),true);
                    }
                    $PAGE->set_global($out,$PAGE->parse($tval),true);
                }
            }
            break;

        case 'categories':
            while(($arr=$DB->fetch_array($r)) && ($pos++ < $pmax)) {
                ++$count;
                $PAGE->push();
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $PAGE->set_property(category_id,$arr['cat_id']+0);
                $PAGE->set_property(category_name,$arr['cat_name']);
                $PAGE->set_property(category_doc_type,$arr['cat_doc_type']);
                $PAGE->set_property(category_description,$arr['cat_description']);
                $PAGE->set_global($out,$PAGE->parse($tval),true);
                $PAGE->pop();
            }
            break;

        case 'all_comments':
        case 'doc_comments':
            while((list($cid) = $DB->fetch_array($r)) && ($pos++ < $pmax)) {
                ++$count;
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $com = new Comment($cid);
                foreach($SYMBOLS as $a => $b)
                  $PAGE->set_property($a,'');
                foreach($com->get_properties() as $name => $value) {
                    $PAGE->set_property($name,$value);
                }
                $PAGE->set_global($out,$PAGE->parse($tval),true);
            }
            break;

        case 'all_notices':
        case 'active_notices':
        case 'active_ads':
        case 'docs_of_type':
        case 'docs_of_type_created':
        case 'document':
        case 'documents_today':
        case 'folder_docs':
        case 'month_days':
        case 'query':
        case 'recent_articles':
        case 'recent_days':
        case 'recent_docs':
        case 'recent_images':
        case 'recent_images_days':
        case 'user_docs':
        case 'user_images':
        case 'weblog':
            $count = 0;
            $previous_group = -1;
            while(($dbrow = $DB->fetch_array($r)) && ($pos++ < $pmax)) {
                ++$count;
                $PAGE->push();

                // check for folder group break
                if ($PAGE->get_property('folder_group')) {
                    $newgroup = get_folder_group($PAGE->get_property('folder_sorted'),$dbrow);
                    if ($newgroup != $previous_group) {
                        $PAGE->set_property('folder_group_break',1);
                    }
                    else {
                        $PAGE->set_property('folder_group_break',0);
                    }
                    $previous_group = $newgroup;
                }

                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                $PAGE->set_property(doc_summary,'');
                $PAGE->set_property(doc_folder_id,'');
                if (count($dbrow) > 4) {
                    $class = $dbrow['doc_type'];
                    if(trim($class)=='') {
                        siteframe_abort( "AH-OOH-GAH! Severe error, docid=%d, fcn=$fcn",
                            $dbrow['doc_id'] );
                    }
                    $doc = new $class(0, $dbrow);
                }
                else {
                    $class = doctype($dbrow['doc_id']);
                    if(trim($class)=='') {
                        siteframe_abort( "AH-OOH-GAH! Severe error, docid=%d, fcn=$fcn",
                            $dbrow['doc_id'] );
                    }
                    $doc = new $class($dbrow['doc_id']);
                }
                if (count($SYMBOLS)>1) {
                  foreach($SYMBOLS as $a => $b)
                    $PAGE->set_property($a,'');
                }
                $PAGE->set_array($doc->get_properties());
                $date = getdate(strtotime($doc->get_property(doc_created)));
                foreach($date as $n => $v)
                    $PAGE->set_property("doc_created_$n",$v);
                $date = getdate(strtotime($doc->get_property(doc_modified)));
                foreach($date as $n => $v)
                    $PAGE->set_property("doc_modified_$n",$v);
                // hide competition entries
                /* SHOW COMPETITION ENTRIES
                if ($PAGE->get_property('competition_entry')&&
                    $PAGE->get_property('doc_folder_competition_active')&&
                    ($fcn!='competition_docs')) {
                  // do nothing
                }
                else 
                */
                if (($fcn=='active_notices')||($fcn=='active_ads')) {
                  if ((time() > strtotime(
                                $doc->get_property('notice_begin_date'))) &&
                      (time() < strtotime(
                                $doc->get_property('notice_end_date'))
                                ))
                      $PAGE->set_global($out,$PAGE->parse($tval),true);
                  else
                      --$count;
                }
                else {
                  $PAGE->set_global($out,$PAGE->parse($tval),true);
                }
                $PAGE->pop();
            }
            break;

        case 'rss':
            $xtmp = 'x'.rand();
            $PAGE->set_property($xtmp,$arg);
            $filename = $PAGE->parse($xtmp);
            // check to see if cached version is in database
            $r = $DB->read("SELECT rss_loaded,rss_text FROM rss ".
                           "WHERE rss_url='$filename'");
            if (!$r) {
              siteframe_abort("RSS:unexpected SQL error: %s",mysql_error());
            }
            list($loaded,$rss) = $DB->fetch_array($r);
            if ((time()-strtotime($loaded)) > ($CACHED_VALUE_UPDATE_TIME*60)) {
              $fp = @fopen($filename,'r');
              if (!$fp) {
                $PAGE->set_property($tval,sprintf('[RSS:unable to open %s]',$arg));
                $PAGE->set_property($out,$PAGE->parse($tval));
              }
              else {
                $rss = fread($fp,999999);
                fclose($fp);
                @$DB->write("DELETE FROM rss WHERE rss_url='$filename'");
                $qi = sprintf("INSERT INTO rss (rss_url,rss_loaded,rss_text) VALUES ".
                              "('%s',NOW(),'%s')",
                              $filename,
                              addslashes($rss));
                $DB->write($qi);
                logmsg("Loaded RSS cache from %s",$filename);
              }
            }
            else {
              $rss = stripslashes($rss);
            }
            $XMLPARSER = xml_parser_create('UTF-8');
            xml_parser_set_option($XMLPARSER,XML_OPTION_CASE_FOLDING,false);
            xml_parse_into_struct($XMLPARSER,$rss,$vals,$index);

            // the real loop
            $count = 0;
            $PAGE->push();
            for($i=0; $i<count($vals); $i++) {
              $PAGE->set_property($vals[$i][tag],$vals[$i][value]);
              switch($vals[$i][level]) {
              case 1:
                if ($vals[$i][tag] != 'rss') {
                    siteframe_abort('RSS:invalid &lt;rss&gt; tag');
                }
                break;
              case 2:
                if ($vals[$i][tag] != 'channel') {
                    siteframe_abort('RSS:invalid &lt;channel&gt; tag');
                }
                break;
              case 3:
                if ($vals[$i][type]=='close') {
                  $PAGE->set_property(row_number,$count++);
                  $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                  $PAGE->set_global($out,$PAGE->parse($tval),true);
                }
                break;
              }
              $max=$PAGE->get_property('rss_limit');
              if (!$max)
                $max=9999;
              if($count>$max)
                break;
            }
            $PAGE->pop();
            xml_parser_free($XMLPARSER);
            break;

        case 'all_groups':
        case 'recent_groups':
        case 'user_groups':
        case 'group':
        case 'group_permissions':
        case 'user_permissions':
            $count = 0;
            while(($dbrow = $DB->fetch_array($r)) && ($pos++ < $pmax)) {
                ++$count;
                $PAGE->push();
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                foreach($dbrow as $n => $v)
                    if (!is_numeric($n))
                        $PAGE->set_property("$n",$v);
                if ($dbrow['user_id']) {
                    $u = new User($dbrow['user_id']);
                        foreach($u->get_properties() as $n => $v)
                            $PAGE->set_property("group_$n",$v);
                }
                $PAGE->set_global($out,$PAGE->parse($tval),true);
                $PAGE->pop();
            }
            break;

        default:
            $count = 0;
            while(($dbrow = $DB->fetch_array($r)) && ($pos++ < $pmax)) {
                $did = $dbrow[doc_id];
                $xtmp = $dbrow[2];
                ++$count;
                $PAGE->push();
                foreach($dbrow as $n => $v)
                    $PAGE->set_property($n,$v);
                $PAGE->set_property(row_number,$count);
                $PAGE->set_property(row_class,($count%2 ? "odd" : "even"));
                if ($did) {
                    $PAGE->set_property(doc_summary,'');
                    $PAGE->set_property(doc_folder_id,'');
                    $class = doctype($did);
                    if(trim($class)=='') {
                        siteframe_abort( "default:AH-OOH-GAH! Severe error, docid=%d, fcn=$fcn", $did );
                    }
                    $doc = new $class($did);
                    switch($fcn) {
                    case 'user_doc_ratings':
                        $PAGE->set_property(user_doc_rating,$xtmp);
                        break;
                    default:
                    }
                    foreach($SYMBOLS as $a => $b)
                      $PAGE->set_property($a,'');
                    foreach($doc->get_properties() as $name => $value) {
                        $PAGE->set_property($name,$value);
                    }
                    $date = getdate(strtotime($doc->get_property(doc_created)));
                    foreach($date as $n => $v)
                        $PAGE->set_property("doc_created_$n",$v);
                    $date = getdate(strtotime($doc->get_property(doc_modified)));
                    foreach($date as $n => $v)
                        $PAGE->set_property("doc_modified_$n",$v);
                }
                // hide competition entries
                if ($PAGE->get_property('competition_entry')&&
                    $PAGE->get_property('doc_folder_competition_active')&&
                    ($fcn!='competition_docs')) {
                  // do nothing
                }
                else {
                    $PAGE->set_global($out,$PAGE->parse($tval),true);
                }
                $PAGE->pop();
            }
        }
        $PAGE->pop();
        return "{".$out."}";
    }
    else {
        $PAGE->pop();
        return $m[0];
    }
}

?>
