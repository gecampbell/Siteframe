<?php
// macros.php
// $Id: macros.php,v 1.38 2004/10/24 05:33:51 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This is an include file that defines functions for processing Siteframe
// macros. A macro is indicated in the text stream in the format:
//
//    {!macro "arg1" "arg2" ...!}
//
// All the arguments ("arg1," "arg2," etc.) are optional. There are a number of
// predefined macros which are implemented in this file, and macros can be
// user-defined as well (by the site administrator).
//
// 2001-10-25: added restrictions to !include so that it cannot access
//   files above the current working directory

$MACROS[macro_cvs_info] = '$Id: macros.php,v 1.38 2004/10/24 05:33:51 glen Exp $';

/* macro(str) - expand macros in string str
*/
function macro($str) {
    return preg_replace_callback('/\{!([^\s!]+)\s*(.*?)!\}/s',macro_call,$str);
}

/* macro_call(str) - expand one macro
*/
function macro_call($arr) {
    global $DB,$PAGE,$MACROS,$DEBUG,$TEMPLATES;
    $tval = rand();
    $macroname = $arr[1];
    $num_args = 0;
    preg_match_all('/"[^"]*"|\'[^\']*\'|[^\s]+/',$arr[2],$match);
    foreach($match as $m) {
        ++$num_args;
        foreach($m as $i => $v) {
            if (substr($v,0,1) == '"') {
                $arg[$i] = str_replace('"','',$v); // remove quotes
            }
            else if (substr($v,0,1) == "'") {
                $arg[$i] = str_replace("'","",$v);
            }
            else {
                $arg[$i] = $v;
            }
            $arg[$i] = preg_replace_callback('/^([a-z0-9_]+)\((.*)(|,(.*))\)/U',
                          pseudo_func,$arg[$i]);
        }
    }

    // only arg0 is pre-parsed!!!!! this sucks
    for ($i=0; $i<1; $i++) {
        $PAGE->set_property("__$tval",$arg[$i]);
        $arg[$i] = $PAGE->parse("__$tval");
    }

    switch($macroname) {

    case '#': // macro comment, ignore
        break;

    case 'date':
        if ($arg[1]!='') {
            $PAGE->set_property("__$tval",$arg[1]);
            $tmp = $PAGE->parse("__$tval");
            if (trim($tmp) == '-')
                $out = '-';
            else {
                if (substr($tmp,0,4) < 1902)
                  $out = str_replace(' 00:00:00','',$tmp);
                else
                  $out = date($arg[0],strtotime($tmp));
            }
        }
        else
            $out = date($arg[0]);
        break;

    case 'define':
        $out = ''; // produces no output
        $MACROS[$arg[0]] = $arg[1];
        break;

    case 'document':
        $PAGE->set_property("__$tval",$arg[0]);
        @eval(sprintf('$tmp=(%s);',$PAGE->parse("__$tval")));
        $class = doctype($tmp);
        $doc = new $class($tmp);
        $PAGE->set_property("_doc_$tval",$doc->display($TEMPLATES[$class]));
        $out = $PAGE->parse("_doc_$tval");
        break;

    case 'getimagesize':
        $out = ''; // produces no output
        $imarr = getimagesize($arg[0]);
        $PAGE->set_property('getimagesize_width',$imarr[0]);
        $PAGE->set_property('getimagesize_height',$imarr[1]);
        $PAGE->set_property('getimagesize_string',$imarr[3]);
        break;

    case 'if':
        $tmp = false;
        if($DEBUG)
            logmsg("if(%s,%s,%s)",htmlentities($arg[0]),htmlentities($arg[1]),htmlentities($arg[2]));
        // logmsg("DEBUG:eval=(%s)",$arg[0]);
        @eval(sprintf('$tmp=(%s);',$arg[0]));
        if ($tmp) {
            $out = $arg[1];
        }
        else {
            $out = $arg[2];
        }
        break;

    case 'case':
        $comp = $arg[0];
        $found = false;
        for($i=1; $i<=count($arg); $i+=2) {
            @eval(sprintf('$tmp=(%s);',$arg[$i]));
            if ($tmp == $comp) {
                $found = true;
                $out = $arg[$i+1];
                break;
            }
        }
        if (!$found && ($i==count($arg)+1))
            $out = $arg[count($arg)-1];
        else if (!$found)
            $out = '';
        break;

    case 'include':
        if ($arg[0][0] == '/') {
            $out = sprintf('{illegal filename for include: "%s"}',$arg[0]);
        }
        else if (strpos($arg[0],'..')===false) {
            if (is_file($arg[0])) {
                $fp = fopen($arg[0],'r');
                if ($fp) {
                    while(!feof($fp)) {
                        $out .= fgets($fp,2048);
                    }
                    $PAGE->set_property("_include_$tval",$out);
                    $out = $PAGE->parse("_include_$tval");
                }
                else {
                    $out = sprintf('[include:"%s" failed to open]',$arg[0]);
                }
            }
            else {
                $out = sprintf('[include:"%s" is not a file]',$arg[0]);
            }
        }
        else {
            $out = sprintf('{illegal filename for include: "%s"}',$arg[0]);
        }
        break;

    case 'list':
        foreach($MACROS as $name => $def) {
            $out .= $name . ': ' . htmlentities($def) . "<br/>\n";
        }
        break;

    case 'format':
        switch ($num_args) {
        case 1:
            $out = number_format($arg[0]);
            break;
        case 2:
            $out = number_format($arg[0],$arg[1]);
            break;
        case 3:
            $out = number_format($arg[0],$arg[1],$arg[2]);
            break;
        case 4:
            $out = number_format($arg[0],$arg[1],$arg[2],$arg[3]);
        }
        break;

    case 'fortune':
        exec("/usr/games/fortune",$fortune);
        $out = nl2br(implode("\n",$fortune));
        break;

    case 'replace':
        $out = str_replace($arg[0],$arg[1],$arg[2]);
        break;

    case 'set':
        $PAGE->set_property("__$tval",$arg[1]);
        $PAGE->set_property($arg[0],$PAGE->parse("__$tval"),$arg[2]);
        break;
    case 'setglobal':
        $PAGE->set_property("__$tval",$arg[1]);
        $PAGE->set_global($arg[0],$PAGE->parse("__$tval"));
        break;

    case 'tolower':
        $out = strtolower($arg[0]);
        break;

    case 'toupper':
        $out = strtoupper($arg[0]);
        break;

    default:
        $tmp = $MACROS[$macroname];
        if (function_exists($tmp)) {
          // function-defined macro
          $PAGE->set_property("__$tval",$tmp($arg));
        }
        else {
          // plain-text macro
          for($i=0;$i<=9;$i++) {
              $tmp = str_replace("\$$i",$arg[$i-1],$tmp);
          }
          $PAGE->set_property("__$tval",$tmp);
        }
        $out = $PAGE->parse("__$tval");
        break;
    }
    return $out;
}

/* pseudo_func(arr) - handle pseudo-function
*/
function pseudo_func($arr) {
    global $PAGE;
    $fname = $arr[1];
    switch($fname) {
    case 'admin':
        $out = isadmin()+0;
        break;
    case 'defined':
        if ($PAGE->get_property($arr[2])!='')
            $out = 1;
        else
            $out = 0;
        break;
    case 'editor':
        $out = iseditor($PAGE->get_property($arr[2]),$arr[4])+0;
        break;
    case 'member':
        $out = ismember($arr[2])+0;
        break;
    case 'rand':
        $out = rand($arr[2]);
        break;
    case 'submittor':
        $out = issubmittor($PAGE->get_property($arr[2]),$arr[4])+0;
        break;
    default:
        $out = sprintf(_ERR_BADFUNCTION,$fname);
    }
    return $out;
}

?>
