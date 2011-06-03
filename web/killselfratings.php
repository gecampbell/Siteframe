<?php
// killselfratings.php
// $Id: killselfratings.php,v 1.2 2003/06/24 04:37:03 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this command-line script removes all self-ratings from the
// ratings table. It goes through all the documents, and
// deletes ratings where the ratings.user_id = docs.doc_owner_id.

require "siteframe.php";

// can we tell if we're running in a browser?
// let's test the server browser string; it's not set when
// running from the command line
if (isset($_SERVER['HTTP_USER_AGENT']))
  die('Sorry: this script cannot be run from a browser');

$count=0;
$r = $DB->read("SELECT doc_id,doc_owner_id FROM docs");
while(list($did,$uid) = $DB->fetch_array($r)) {
  print('.');
  @$DB->write(sprintf('DELETE FROM ratings WHERE user_id=%d AND doc_id=%d',
          $uid,$did));
  $count += $DB->affected_rows();
}

printf("\n%d rating(s) deleted\n",$count);

?>
