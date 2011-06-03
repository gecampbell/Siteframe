<?php
// Trackback
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: trackback.php,v 1.19 2003/06/10 19:45:38 glen Exp $
//
// this plug-in implements Movabletype's Trackback functionality

$Trackback = new Plugin('Trackback');
if ($ENABLE_TRACKBACK) {
  $Trackback->set_input_property('Document',
      array(name => trackback_enable,
            type => checkbox,
            rval => 1,
            prompt => "Enable trackback",
            doc => "Trackback is a protocol for communicating between ".
                   "websites. By enabling trackback, you can be notified ".
                   "when other websites or documents make reference to ".
                   "this particular document.",
            fcn_val => "trackbackValidate")
  );
  $Trackback->set_input_property('Document',
      array(name => trackback_ping_url,
            type => textarea,
            prompt => "Trackback: URL(s) to ping",
            doc => "Trackback is a protocol for communicating between ".
                   "websites. You can enter one or more URLs here ".
                   "(each on a separate line) of ".
                   "websites/documents that you wish to ping via Trackback.",
            fcn_val => "trackbackValidate")
  );
}
$Trackback->set_output_property('Document',
    array(name => rdf,
          callback => "trackbackGenerateRDF")
);
$Trackback->set_output_property('Document',
    array(name => trackback_url,
          callback => "trackbackGenerateURL")
);
$Trackback->set_autoblock('tb_pings',
    "SELECT * FROM docs,trackback WHERE (docs.doc_id=trackback.tb_doc_id) ".
    "AND docs.doc_id=%d ORDER BY trackback.created DESC");
//$Trackback->set_template('XMLtbpings','xmltbpings');
$Trackback->set_trigger('Document','add','trackbackSendPing');
$Trackback->set_trigger('Document','update','trackbackSendPing');
$Trackback->set_global('Trackback','ENABLE_TRACKBACK',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Enable Trackback',
    doc => 'Trackback is a protocol for allowing websites to notify one '.
           'another when they are referenced remotely. Click this box to '.
           'enable the Trackback plugin for the entire site.'
  ));
$Trackback->register();

// trackbackValidate - check properties
function trackbackValidate(&$obj,$property,$value) {
    switch($property) {
    case 'trackback_enable':
        return 1;
    case 'trackback_ping_url':
        if (trim($value)=='')
            return 0;
        $urls = explode("\n",$value);
        /*
        foreach($urls as $url) {
            if (substr($url,0,7)!='http://')
                $obj->add_error('URL (%s) does not have http:// prefix',$url);
        }
        */
        if ($obj->errcount()) return 0;
        return 1;
    default: // unsupported property
        $obj->add_error('Unsupported property [%s]',$property);
        return 0;
    }
}

$tbRDF = <<<ENDRDF
<!--
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
         xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
<rdf:Description
    rdf:about="{site_url}/document.php?id={doc_id}"
    dc:identifer="{site_url}/document.php?id={doc_id}"
    dc:title="{doc_title}"
    trackback:ping="{site_url}/tb.php?doc={doc_id}" />
</rdf:RDF>
-->
ENDRDF;

$tbPING = <<<ENDTBPING
POST %s HTTP/1.1
Host: %s
Content-Type: application/x-www-form-urlencoded
Content-Length: %d
Connection: close

%s

ENDTBPING;

// trackbackGenerateRDF - generates {rdf} slot for document
function trackbackGenerateRDF(&$obj) {
    global $tbRDF,$PAGE;
    if (!$obj->get_property('trackback_enable'))
        return '';
    $PAGE->set_property(_rdf_,$tbRDF);
    // note that this has to refer to the internal array; if it
    // calls $obj->get_properties(), that recursively calls this function
    $PAGE->set_array($obj->_properties);
    return $PAGE->parse(_rdf_);
}

// trackbackGenerateURL - generates the {trackback_url} for document
function trackbackGenerateURL(&$obj) {
    global $SITE_URL;
    return sprintf('%s/tb.php?doc=%d',$SITE_URL,$obj->get_property('doc_id'));
}

// trackbackSendPing(url) - sends a ping to the URLs specified
function trackbackSendPing(&$obj,$class='',$event='',$event_url='') {
    global $tbPING,$PAGE;

    if ($event_url=='')
      $event_url = $obj->get_property('trackback_ping_url');
    if (trim($event_url)=='')
        return;

    // gather data to send
    $pageurl = sprintf('%s/document.php?id=%d',
                    $PAGE->get_property('site_url'),
                    $obj->get_property('doc_id'));
    $blog = rawurlencode($PAGE->get_property('site_name'));
    $title = rawurlencode($obj->get_property('doc_title'));
    $excerpt = rawurlencode(substr($obj->get_property('doc_body'),0,40));
    $data = sprintf('blog_name=%s&title=%s&url=%s&excerpt=%s',
                    $blog,$title,$pageurl,$excerpt);

    // gather the URLs
    $urllist = explode("\n",$event_url);

    // process each of them
    foreach($urllist as $url) {
        $pingee = parse_url($url);
        $request_url = sprintf('%s?%s',$pingee['path'],$pingee['query']);
        if ($pingee['port']) $port = $pingee['port'];
        else $port = 80;
        $socket = fsockopen($pingee['host'],$port);
        if (!$socket) {
            $obj->add_error('Trackback:Could not connect to host [%s]',$pingee['host']);
        }
        else {
            $msg = sprintf($tbPING,
                $request_url,
                $pingee['host'],
                strlen($data),
                $data);
            fputs($socket,$msg);
            while(!feof($socket)) {
                  $res .= fgets($socket, 128);
            }
            // check $res for error
            if (preg_match('/<error>(.*)<\/error>/',$res,$m)) {
                $errno = $m[1];
                if (preg_match('/<message>(.*)<\/message>/',$res,$n)) {
                    $errmsg = $n[1];
                }
            }
            if ($errno)
                logmsg('Trackback:pinged %s, result=%d',$url,$errno,$errmsg);
            else
                logmsg('Trackback:pinged %s, successful',$url);
        }
        fclose($socket);
    }

    // clear the field
    $obj->set_property('trackback_ping_url','');
}

?>
