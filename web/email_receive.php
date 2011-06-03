<?php
// email_receive.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: email_receive.php,v 1.24 2003/06/07 01:27:23 glen Exp $
//
// reads and parses a received email message

include "siteframe.php";

// can we tell if we're running in a browser?
// let's test the server browser string; it's not set when
// running from the command line
if (isset($_SERVER['HTTP_USER_AGENT']))
  die('Sorry: this script cannot be run from a browser');

// errmsg - display error message
function errmsg($msg,$a='',$b='',$c='',$d='',$e='',$f='') {
  global $err,$errcount;
  $err[++$errcount] = sprintf("email_receive.php:".$msg."\n",$a,$b,$c,$d,$e,$f);
}

// status - save status message for reply
function status($msg,$a='',$b='',$c='',$d='',$e='',$f='') {
  global $stat;
  $stat[] = sprintf($msg."\n",$a,$b,$c,$d,$e,$f);
}

// check for global allowance setting
if (!$ALLOW_EMAIL_SUBMISSIONS) {
  printf("Email submissions not permitted\n");
  exit;
}

$stat[] = sprintf("Initiated at %s\n",date('Y-M-d H:i'));
$err[] = sprintf("Error Log\n");

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

// process arguments
for($i=1; $i<$argc; $i++) {
  $opt = $argv[$i];
  if (substr($opt,0,1) == '-') {
    $opt = substr($opt,1,strlen($opt)-1);
    printf("INFO:option %s\n",$opt);
    list($var,$val) = split('=',$opt,2);
    $$var = $val;
  }
  else
    printf("INFO:unrecognized parameter %s\n",$opt);
}

$stdin = fopen('php://stdin','r');
if (!$stdin) {
  printf("ERROR:unable to open standard input\n");
  die();
}
$msg = fread($stdin,99999);

list($header,$body) = split("\n\n",$msg,2);

$newdoctype = 'Article';

// MIME email processing
if (preg_match('/[Cc]ontent-[Tt]ype:\s+multipart\/mixed.*boundary="([^"]+)"/s',
               $header, $m)) {

  printf("INFO:MIME email\n");
  errmsg("MIME email is not currently supported");
  $newdoc = 1;
  $sep = $m[1];
  $parts = split($sep,$body);
  foreach ($parts as $subpart) {
    list($subhead,$subbody) = split("\n\n",$subpart,2);
    if (preg_match('/Content-Type:\s*(.*);/m',$subhead,$sm)) {
      // process this; otherwise ignore it
      $type = $sm[1];
    }
    else $type='';
    if (preg_match('/Content-Disposition:\s*(.*);/m',$subhead,$sm)) {
      $disp = $sm[1];
    }
    else $disp='';
    if (preg_match('/Content-Transfer-Encoding:\s*(.*)/m',$subhead,$sm)) {
      $encd = $sm[1];
    }
    else $encd='';
    if (preg_match('/filename="([^"]+)"/m',$subhead,$sm)) {
      $file = $sm[1];
    }
    else $file='';
    // if content-type is "text/ascii" treat it as the body
    switch ($type) {
    case 'text/html':
    case 'text/ascii':
      $body = $subbody;
      break;
    default:
      if (($disp == 'attachment') && ($file != '')) {
        if ($type == 'image/jpeg') {
          $newdoctype = 'Image';
        }
        else {
          $newdoctype = 'DocFile';
        }
        // decode file
        switch($encd) {
        case 'base64':
          $filecontent = base64_decode($subbody);
          $file_attachment_name = sprintf('/tmp/%s',md5(rand()));
          $attfp = fopen($file_attachment_name,'w');
          fwrite($attfp,$filecontent);
          fclose($attfp);
          break;
        default:
          errmsg("Unsupported encoding [%s]",$encd);
        }
      }
    }
  }
}

// determine who it's from
if (preg_match('/[Ff]rom:\s+(.+)$/Um',$header,$m)) {
  $from_str = $m[1];
  if (preg_match('/([^\s\(\<]+@[^\s\)\>]+)/',$from_str,$f)) {
    $from_addr = $f[1];
    status("Message identified from [%s]",$from_addr);
  }
  else
    errmsg("no identifiable From: email address");
}
else {
  errmsg("no From: header");
}

// determine the subject line (becomes title)
if (preg_match('/[Ss]ubject:\s+(.+)$/Um',$header,$m)) {
  $subject = $m[1];
  status("Subject identified as [%s]",$subject);
}
else {
  errmsg("no identifiable Subject: line");
}

// at this point we have the sender's email address
// determine the user information
if (!$errcount) {
  $r = $DB->read("SELECT user_id FROM users WHERE user_email='$from_addr'");
  list($uid) = $DB->fetch_array($r);
  if (!$uid) {
    errmsg("No user with email address [%s]",$from_addr);
  }
  $user = new User($uid);
  $CURUSER = new User($uid);

  // any document references?
  if (!$newdoc && ($n = preg_match('/document.php\?id=([0-9]+)/',$body,$m))) {
    if ($n>1) {
      errmsg("Multiple document references");
    }
    // here, we have a doc ID; assume a new comment on the document
    $id = $m[1];
    printf("INFO:Comment on id [%d]\n",$id);
    // verify this is an existing document
    $xdoc = new Document($id);
    if (!$xdoc) {
      errmsg("No document with id=%d",$id);
    }
    else {
      // confirm this is not a duplicate comment
      $q = sprintf("SELECT comment_id FROM comments ".
                   "WHERE owner_id=%d ".
                   "  AND doc_id=%d",
                   $user->get_property('user_id'),
                   $id);
      $r = $DB->read($q);
      while(list($cid) = $DB->fetch_array($r)) {
        $x = new Comment($cid);
        if ($x->get_property('comment_subject') == $subject)
          errmsg("Duplicate comment, not submitted");
      }
      if (!$errcount) {
        // strip ">" quoted lines
        $cbody = preg_replace('/^>[^\n]*$/m',"\n",$body);
        $cbody = preg_replace('/\n\n\n+/',"\n\n",$cbody);
        $cbody = trim($cbody);
        // ok, continue
        $comment = new Comment(0, $id);
        $comment->set_property('comment_subject',$subject);
        $comment->set_property('comment_owner_id',$user->get_property('user_id'));
        $comment->set_property('comment_body',$cbody);
        $comment->add();
        if ($comment->errcount()) {
          errmsg($comment->get_errors());
        }
        else {
          status("Comment added to document [%s]",$doc->get_property('doc_title'));
        }
      }
    }
  }
  else { // new document
    // check for duplicate
    $q = sprintf("SELECT doc_id FROM docs WHERE doc_title='%s' AND doc_owner_id=%d",
          $subject,$user->get_property('user_id'));
    $r = $DB->read($q);
    list($did) = $DB->fetch_array($r);
    if ($did) {
      errmsg("Duplicate article, not submitted");
    }
    if (!$errcount) {
      $doc = new $newdoctype(0);
      $doc->set_property('doc_title',$subject);
      $doc->set_property('doc_owner_id',$user->get_property('user_id'));
      $doc->set_property('doc_body',$body);
      switch($newdoctype) {
      case 'Article':
        break;
      case 'Image':
      case 'DocFile':
        $HTTP_POST_FILES[doc_file][tmp_name] = $file_attachment_name;
        $HTTP_POST_FILES[doc_file][type] = $type;
        $HTTP_POST_FILES[doc_file][name] = $file;
        $doc->set_property('doc_file','Attachment');
        break;
      }
      // $folder may be set by -folder=N
      $doc->set_property('doc_folder_id',$folder+0);
      $doc->add();
      if ($doc->errcount()) {
        errmsg($doc->get_errors());
      }
      else {
        status("Created new article '%s'",$subject);
        status("Article URL: <a href=\"%s/document.php?id=%d\">%s/document.php?id=%d</a>",
          $SITE_URL,$doc->get_property('doc_id'),
          $SITE_URL,$doc->get_property('doc_id'));
      }
    }
  }
}
else {
  errmsg("No action taken, too many errors");
}

// return confirmation email to sender
$PAGE->set_property('subject',$subject);
$PAGE->set_property('body',$body);
$PAGE->set_property('errcount',$errcount+0);
$PAGE->load_template('_ascii_',$TEMPLATES[Confirm][ascii]);
$email = new Email();
$email->add_address($from_addr,'to');
$email->set_property('email_from',
    sprintf('%s <%s>',$SITE_NAME,$SITE_EMAIL));
$email->set_property('email_subject',
    sprintf('[%s] message confirmation, %d error(s)',$SITE_NAME,$errcount));
if ($user->get_property('no_html_email')) {
  foreach($stat as $statmsg) {
    $PAGE->set_property('status',$statmsg,true);
  }
  foreach($err as $errmsg) {
    $PAGE->set_property('errors',$errmsg,true);
  }
  $email->set_property('email_ascii',strip_tags($PAGE->parse('_ascii_')));
}
else {
  $PAGE->load_template('_html_',$TEMPLATES[Confirm][html]);
  foreach($stat as $statmsg) {
    $PAGE->set_property('status',nl2br($statmsg),true);
  }
  foreach($err as $errmsg) {
    $PAGE->set_property('errors',nl2br($errmsg),true);
  }
  $PAGE->set_property('body',nl2br($body));
  $email->set_property('email_html',$PAGE->parse('_html_'));
}
$email->send();

?>