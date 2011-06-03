<?php
// ratings
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: ratings.php,v 1.7 2003/06/07 01:27:23 glen Exp $
//
// displays all ratings for a document

include "siteframe.php";

$PAGE->set_property(page_title,'Document Ratings');

$id = $_GET['id'];

if (!$id) {
    $PAGE->set_property(error,_ERR_NOID);
}
else {
    $class = doctype($id);
    $doc = new $class($id);
    foreach($doc->get_properties() as $n => $v)
        $PAGE->set_property($n,$v);
    $PAGE->load_template(_ratings_,$TEMPLATES[Ratings]);
    $PAGE->set_property(ratings,'');
    $PAGE->block(_ratings_,ratings,rating_item);
    $PAGE->block(_ratings_,rating_users,rating_user);
    $PAGE->set_property(rating_users,'');
    if ($PAGE->get_property(doc_rating_count) > 0) {
        foreach($RATING as $n => $v) {
            $counts[($v+0)] = 0;
        }
        $r = $DB->read("SELECT COUNT(*) FROM ratings WHERE doc_id=$id");
        list($total) = $DB->fetch_array($r);
        $r = $DB->read("SELECT rating,COUNT(*) FROM ratings WHERE doc_id=$id GROUP BY rating");
        while(list($num,$count) = $DB->fetch_array($r)) {
            $counts[$num] = $count;
        }
        ksort($counts);
        $counter = 0;
        foreach($counts as $n => $v) {
            $counter++;
            $PAGE->set_property(row_number,$counter);
            $PAGE->set_property(row_class,($counter%2) ? "odd" : "even");
            $percent = ($v/$total)*100;
            $bar = '';
            for($y=0;$y<$percent;$y+=2)
                $bar .= '&nbsp;';
            $PAGE->set_property(rating,$RATING[$n]);
            $PAGE->set_property(rating_count,$counts[$n]);
            $PAGE->set_property(rating_percent,$percent);
            $PAGE->set_property(rating_bar,$bar);
            $PAGE->set_property(ratings,$PAGE->parse(rating_item),true);
        }
        $id = $doc->get_property(doc_id);
        $counter = 0;
        $r = $DB->read("SELECT rating,ratings.user_id FROM ratings ".
                        "LEFT JOIN users ON (ratings.user_id=users.user_id) ".
                        "WHERE doc_id=$id ".
                        "ORDER BY rating,user_lastname,user_firstname");
        while(list($rating,$uid) = $DB->fetch_array($r)) {
            $counter++;
            $PAGE->set_property(row_number,$counter);
            $PAGE->set_property(row_class,($counter%2) ? "odd" : "even");
            $PAGE->set_property(user_rating,$rating);
            $u = new User($uid);
            foreach($u->get_properties() as $n => $v)
                $PAGE->set_property("rating_$n",$v);
            $PAGE->set_property(rating_users,$PAGE->parse(rating_user),true);
        }
    }
    $PAGE->set_property(body,$PAGE->parse(_ratings_));
}

$PAGE->pparse(page);
?>
