<?php
// update_user.php
// $Id: update_user.php,v 1.19 2005/03/08 04:40:34 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//

// note that siteframe.php is not included, since this is invoked from
// daily.php

// can we tell if we're running in a browser?
// let's test the server browser string; it's not set when
// running from the command line
if (isset($_SERVER['HTTP_USER_AGENT']))
  die('Sorry: this script cannot be run from a browser');

// do not time out
set_time_limit(0);

// calculate max activity count
$max_activity = 0;

$q = "SELECT * FROM users ORDER BY user_lastname,user_firstname,user_created";
$r = $DB->read($q);
while($dbrow = $DB->fetch_array($r)) {
  $user = new User(0,$dbrow);
  // calculate number of documents
  $q = sprintf("SELECT COUNT(*) FROM docs WHERE doc_owner_id=%d",
        $user->get_property('user_id'));
  list($num_docs) = @$DB->fetch_array($DB->read($q));
  $user->set_property('user_document_count',$num_docs+0);
  // calculate number of folders
  $q = sprintf("SELECT COUNT(*) FROM folders WHERE folder_owner_id=%d",
        $user->get_property('user_id'));
  list($num_folders) = @$DB->fetch_array($DB->read($q));
  $user->set_property('user_folder_count',$num_folders+0);
  // calculate number of comments
  $q = sprintf("SELECT COUNT(*) FROM comments WHERE owner_id=%d",
        $user->get_property('user_id'));
  list($num_comments) = @$DB->fetch_array($DB->read($q));
  $user->set_property('user_comment_count',$num_comments+0);
  // calculate number of ratings
  $q = sprintf("SELECT COUNT(*),AVG(rating),SUM(rating) FROM ratings WHERE user_id=%d",
        $user->get_property('user_id'));
  list($num_ratings,$avg_rating,$sum_rating) = @$DB->fetch_array($DB->read($q));
  $user->set_property('user_rating_count',$num_ratings+0);
  $user->set_property('user_rating_average',$avg_rating+0);
  $user->set_property('user_rating_total',$sum_rating+0);
  // total ratings plus comments
  $total = $num_ratings+$num_comments;
  $user->set_property('user_activity_count',$total);
  if ($total != 0)
    $activity[] = $total;
  if ($total > $max_activity) {
    $max_activity = $total;
  }
  // update user
  $user->update();
}

if (count($activity)) {
  rsort($activity);
  reset($activity);
  $percent10 = $activity[count($activity)/10];
  message("90th percentile=%d",$percent10);
  $percent5 = $activity[count($activity)/20];
  message("95th percentile=%d",$percent5);

  // now, set percentile flags
  $q = "SELECT * FROM users";
  $r = $DB->read($q);
  while($dbrow = $DB->fetch_array($r)) {
    $user = new User(0,$dbrow);
    $total = $user->get_property('user_activity_count');
    if ($total > $percent10) {
      $user->set_property('user_top10',1);
      printf("Top 10%%: %s\n",$user->get_property('user_name'));
    }
    else {
      $user->set_property('user_top10',0);
    }
    if ($total > $percent5) {
      $user->set_property('user_top5',1);
      $user->set_property('user_top10',0);
      printf("Top 5%%: %s\n",$user->get_property('user_name'));
    }
    else {
      $user->set_property('user_top5',0);
    }
    $user->update();
  }

}

message("Optimizing users table...");
$DB->write("OPTIMIZE TABLE users");
message("Repairing users table...");
$DB->write("REPAIR TABLE users");

?>
