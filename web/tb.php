<?php
// tb.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: tb.php,v 1.7 2003/06/13 22:26:47 glen Exp $
//
// trackback ping handler

include "siteframe.php";

// template for response message
$XMLresponse = <<<ENDXMLRESPONSE
<?xml version="1.0" encoding="iso-8859-1" ?>
<response>
<error>{xmlerror}</error>
{!if defined(xmlmessage) '<message>{xmlmessage}</message>'!}
</response>

ENDXMLRESPONSE;

$PAGE->set_property(page,$XMLresponse);

if (!$ENABLE_TRACKBACK) {
    $PAGE->set_property(xmlerror,1);
    $PAGE->set_property(xmlmessage,'Trackback is not currently enabled on this site');
}
else if ($_GET['__mode']=='rss') {
    // retrieve pings
    // first, check for valid document
    $q = sprintf("SELECT COUNT(*) FROM docs WHERE doc_id=%d",$_GET['doc']);
    $r = $DB->read($q);
    list($num) = $DB->fetch_array($r);
    if ($num!=1) {
        $PAGE->set_property(xmlerror,1);
        $PAGE->set_property(xmlmessage,'No document with that ID');
    }
    else {
        $PAGE->load_template(page,$TEMPLATES[XMLtbpings]);
        $PAGE->set_property(doc_id,$_GET['doc']);
        logmsg('Trackback:responding to __mode=rss ping from %s',$_SERVER['REMOTE_ADDR']);
    }
}
else {
    // add new ping - must support either POST or GET
    if ($_POST['blog_name']!='') {
        $url = $_POST['url'];
        $title = $_POST['title'];
        $blog = $_POST['blog_name'];
        $excerpt = $_POST['excerpt'];
    }
    else {
        $url = $_GET['url'];
        $title = $_GET['title'];
        $blog = $_GET['blog_name'];
        $excerpt = $_GET['excerpt'];
    }
    $q = sprintf("INSERT INTO trackback ".
                 "(tb_doc_id,created,tb_url,tb_title,tb_ip,tb_site,tb_excerpt) ".
                 "VALUES ".
                 "(%d,       NOW(),  '%s',  '%s',    '%s', '%s',   '%s')",
                 $doc = $_GET['doc'],
                 $url,
                 $title,
                 $_SERVER['REMOTE_ADDR'],
                 $blog,
                 $excerpt);

    if (!$doc) {
        // respond error
        $PAGE->set_property(xmlerror,1);
        $PAGE->set_property(xmlmessage,'A document ID is required (doc=)');
        logmsg('Trackback:ping received w/o doc ID, url=%s',$_GET['url']);
    }
    else {
        // verify that the document exists
        $r = $DB->read(sprintf('SELECT COUNT(*) FROM docs WHERE doc_id=%d',$doc));
        list($num) = $DB->fetch_array($r);
        if ($num!=1) {
            $PAGE->set_property(xmlerror,1);
            $PAGE->set_property(xmlmessage,'No document with that ID');
        }
        else {
            // add ping to the database
            $DB->write($q);
            $msg = $DB->get_errors();
            if ($msg!='') {
                // this means an error was generated
                $PAGE->set_property(xmlerror,1);
                $PAGE->set_property(xmlmessage,sprintf('Errors occurred: %s',$msg));
                logmsg('Trackback:ping failure %s',$msg);
            }
            else {
                // everything's hunky-dory
                $PAGE->set_property(xmlerror,0);
                logmsg('Trackback:ping received on doc=%d',$doc);
            }
        }
    }
}

// now, send the response

$PAGE->pparse(page);

?>
