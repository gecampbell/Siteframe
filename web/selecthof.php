<?php
// selecthof.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: selecthof.php,v 1.19 2009/12/31 16:12:43 glen Exp $
//
// contaxg.com: this program selects the most recent "Hall of Fame" entry
//
// criteria:
//    1. Highest average rating
//    2. Older than 60 days
//    3. More than 4 ratings
//    4. Not currently in the Hall of Fame
//    5. Document type is Image

include "siteframe.php";

// can we tell if we're running in a browser?
// let's test the server browser string; it's not set when
// running from the command line
if (isset($_SERVER['HTTP_USER_AGENT']))
  die('Sorry: this script cannot be run from a browser');

// these are the folder ID's of any existing Hall of Fame folders
define(_HOF_FOLDERS, '286,1192,1204,1669,1696,2107,2380,2616,2854');
// the current HoF folder
define(_HOF_CURRENT, 2854);
// mailing list address
define(_HOF_MAILLIST, 'contaxg@contaxg.com');

$q = sprintf("SELECT ratings.doc_id,COUNT(*),AVG(rating) FROM ratings ".
             "LEFT JOIN docs ON (ratings.doc_id=docs.doc_id) ".
             "WHERE doc_folder_id NOT IN (%s) ".
             "  AND docs.doc_created < DATE_SUB(NOW(),INTERVAL 60 DAY) ".
             "  AND doc_type='Image' ".
             "GROUP BY ratings.doc_id ".
             "HAVING COUNT(*)>4 ".
             "ORDER BY 3 DESC, docs.doc_created ".
             "LIMIT 1",
             _HOF_FOLDERS);
$r = $DB->read($q);
list($docid,$count,$average) = $DB->fetch_array($r);
$doc = new Image($docid,0);
$doc->set_property('doc_folder_id',_HOF_CURRENT);
$doc->set_property('doc_body',
        sprintf("%s\n\nSelected for the Hall of Fame on %s",
           $doc->get_property('doc_body'),
           date('Y/M/d')), 1);
$doc->update();

$user = new User($doc->get_property('doc_owner_id'));
$user->set_property('user_hof',1);

printf("The new Hall of Fame image is \"%s\" by %s\n",
  $doc->get_property('doc_title'),
  $user->get_property('user_name'));
printf("%s/document.php?id=%d\n", $SITE_URL, $doc->get_property('doc_id'));

$user->update();

logmsg("New Hall of Fame Image \"%s\"",$doc->get_property('doc_title'));

// construct notification message

$subject = "New Hall of Fame Member";
$msg = <<<ENDBODY
"%s"
by %s
%s/document.php?id=%d

Congratulations to %s for our new Hall of Fame image!
ENDBODY;
$body = sprintf($msg,
          $doc->get_property('doc_title'),
          $user->get_property('user_name'),
          $SITE_URL,
          $doc->get_property('doc_id'),
          $user->get_property('user_name'));

$mail = new Email();
$mail->set_property('email_from',
    sprintf("%s <%s>",$SITE_NAME,$SITE_EMAIL));
$mail->set_property('email_subject',stripslashes($subject));
$mail->set_property('email_ascii',stripslashes($body));
if ($SITE_NEWSLETTER_EMAIL)
{
	$mail->add_address($SITE_NEWSLETTER_EMAIL,'to');
}
else
{
	$r = $DB->read("SELECT user_email FROM users");
	while(list($em) = $DB->fetch_array) {
		$mail->add_address($em,'bcc');
	}
}
$mail->send();

?>
