<?php
// siteframe.php
// $Id: siteframe.php,v 1.198 2007/09/29 17:05:45 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This is the core Siteframe file that is included in all publicly-
// viewable web pages (not the classes/ directory files). The "include"
// statement for the configuration file, immediately below, might need
// to be modified to point to your include file, which should be stored
// OUTSIDE the reach of your web server.

define(SITEFRAME_VERSION,'3.2.4');
define(USER_STATUS_HOLD,0);
define(USER_STATUS_NORMAL,1);
define(USER_STATUS_ADMIN,99);

/* default include files
*/
if ($LOCAL_PATH != "../")
    $LOCAL_PATH = "./";
include "${LOCAL_PATH}config.php";              // global configuration file
if (!defined(DBWRITE))
    define(DBWRITE,DBHOST);
include "${LOCAL_PATH}siteconfig.php";          // local configuration stuff
include "${LOCAL_PATH}classes/siteframe.php";   // base Siteframe class
include "${LOCAL_PATH}classes/db.php";          // database access object
include "${LOCAL_PATH}classes/autoblocks.php";  // autoblocks
include "${LOCAL_PATH}classes/plugin.php";      // plugin object
include "${LOCAL_PATH}classes/template.php";    // template object
include "${LOCAL_PATH}classes/trigger.php";     // trigger object
include "${LOCAL_PATH}macros.php";              // macro functions
include "${LOCAL_PATH}classes/user.php";        // user object
include "${LOCAL_PATH}classes.php";             // document classes
include "${LOCAL_PATH}globals.php";             // manage global variables

/* seed the random number generator
*/
srand((double)microtime()*1000000);

//# change language specification to reflect user preferences
//include './lang/en.php';                      // language constants

/* siteframe_abort(msg) - aborts with an error message
*/
function siteframe_abort($msg,$opt1='',$opt2='',$opt3='',$opt4='') {
    global $PHP_SELF;
    $out = sprintf($msg,$opt1,$opt2,$opt3,$opt4);
    printf(
      "<html><body><pre>".
      "Siteframe abort in %s:\n  %s\n".
      "</pre></body></html>\n",
      $PHP_SELF,
      $out
    );
    logmsg('ABORT in %s: %s',$PHP_SELF,$out);
    die('Aborted');
}

/* logmsg(msg) - write a message to the log file
*/
function logmsg($msg,$arg1='',$arg2='',$arg3='',$arg4='') {
    global $DB,$PHP_SELF;
    $newmsg = addslashes(sprintf($PHP_SELF.':'.$msg,$arg1,$arg2,$arg3,$arg4));
    $DB->write("INSERT INTO activity (event_date,message) VALUES ".
               "(NOW(),'$newmsg')");
}

/* clean(str) - clean up a value
*/
function clean($str) {
    return strip_tags(stripslashes(trim($str)));
}

/* clean_html(str) - clean up a value, HTML ok
*/
function clean_html($str) {
    global $OKTAGS;
    return strip_tags(stripslashes(trim($str)),$OKTAGS);
}

/* isadmin() - returns true if logged-in user is an administrator
*/
function isadmin() {
    global $CURUSER;
    if (!$CURUSER) return 0;
    if ($CURUSER->get_property(user_status)==USER_STATUS_ADMIN)
        return 1;
    else
        return 0;
}

/* iseditor(user_id) - is the current user authorized to edit something
**   owned by 'user_id'? An authorized editor is the owner or someone
**   with administrative privileges
*/
function iseditor($obj_id,$obj_class='',$permission='can_edit') {
    global $CURUSER,$DB;
    if (!$CURUSER) return 0;
    $uid = $CURUSER->get_property('user_id');
    if ($CURUSER->get_property(user_status)==USER_STATUS_ADMIN)
        return 1;
    else if ((trim($obj_class)=='') && ($uid==$obj_id))
        return 1;
    else if (trim($obj_class)=='')
        return 0;
    else {
        switch(strtolower($obj_class)) {
        case 'document':
            $q = sprintf("SELECT doc_owner_id FROM docs WHERE doc_id=%d",$obj_id);
            break;
        case 'folder':
            $q = sprintf("SELECT folder_owner_id FROM folders WHERE folder_id=%d",$obj_id);
            break;
        case 'group':
            $q = sprintf("SELECT group_owner_id FROM groups WHERE group_id=%d",$obj_id);
            break;
        default:
            siteframe_abort('Severe unexpected error; iseditor(obj_class=[%s]) '.
                'has an unanticipated value',$obj_class);
        }
        $r = $DB->read($q);
//if (!$r) print '[a]'.mysql_error();
        list($id) = $DB->fetch_array($r);
        if ($id==$uid)
            return 1;
        // if the class is a document, does the user have permissions on the
        // folder containing the document?
        if ($obj_class=='document') {
          $class = doctype($obj_id);
          if ($class == '') return 0;
          $doc = new $class($obj_id);
          $folder_id = $doc->get_property('doc_folder_id');
        }
        // next, does the user have permissions
        $q = sprintf(
            'SELECT %s FROM permissions '.
            'WHERE obj_type=\'%s\' AND obj_id=%d AND editor_type=\'U\' AND '.
            ' editor_id=%d',
            $permission,
            ($folder_id ? 'folder' : $obj_class),
            ($folder_id ? $folder_id : $obj_id),
            $uid);
        $r = $DB->read($q);
//if (!$r) print '[b]'.mysql_error();
        list($val) = $DB->fetch_array($r);
        if ($val) return 1;
        // next, is the user in a group with permissions
        $q = sprintf(
            'SELECT %s FROM permissions,groups,group_members '.
            'WHERE permissions.editor_id=groups.group_id '.
            'AND groups.group_id=group_members.group_id '.
            'AND obj_type=\'%s\' AND obj_id=%d '.
            'AND editor_type=\'G\' AND group_members.group_user_id=%d ',
            $permission,
            ($folder_id ? 'folder' : $obj_class),
            ($folder_id ? $folder_id : $obj_id),
            $uid);
        $r = $DB->read($q);
//if (!$r) print '[c]'.mysql_error();
        list($val) = $DB->fetch_array($r);
        if ($val) return 1;
        else return 0;
    }
    return 0;
// there are three options here:
// 1. the current user owns the object/id
// 2. the current user has been granted editor status for the object
// 3. the current user is a member of a group that has been
//    granted editor status for the object
//*** still needs to be written
}

// issubmittor(id,group|folder)
// returns TRUE if user can submit (add new items) to the folder or group
function issubmittor($id,$class) {
    return iseditor($id,$class,'can_submit');
}

// ismember(group,$uid) return true if currentuser is a member of group
function ismember($group,$uid=0) {
    global $CURUSER,$DB;
    if (!$CURUSER) return 0;
    if ($uid == 0) $uid=$CURUSER->get_property('user_id');
    $r = $DB->read(sprintf('SELECT COUNT(*) FROM group_members WHERE '.
                           ' group_id=%d AND group_user_id=%d',
                           $group,$uid));
//if (!$r) print '[d]'.mysql_error();
    list($num) = $DB->fetch_array($r);
    return $num;
}

/* doctype(id) return the document type for a document
*/
function doctype($id) {
    global $DB;
    $q = sprintf("SELECT doc_type FROM docs WHERE doc_id=%d",$id);
    $r = $DB->read($q);
    list($type) = $DB->fetch_array($r);
    return $type;
}

/* foldertype(id) return the folder type for a document
*/
function foldertype($id) {
    global $DB;
    $q = sprintf("SELECT folder_type FROM folders WHERE folder_id=%d",$id);
    $r = $DB->read($q);
    list($type) = $DB->fetch_array($r);
    return $type;
}

/* doc_categories(type) returns an array of document categories
** for the request doc_type
*/
function doc_categories($doctype) {
  global $DB;
  $q = "SELECT cat_id,cat_name FROM categories ".
       "WHERE cat_doc_type='' OR cat_doc_type='$doctype' ".
       "ORDER BY cat_name";
  $r = $DB->read($q);
  $arr[0] = "None";
  while(list($id,$name) = $DB->fetch_array($r)) {
    $arr[$id] = $name;
  }
  return $arr;
}

/* recordset(result,self,offset,ipp)
**   given a result set, generates a recordset navigator
**   for items per page (ipp) starting at offset for page self
*/
    function recordset($result,$self,$offset,$ipp) {
        global $DB;

        // compute number of rows; if less than $ipp, generate nothing
        $num_rows = $DB->num_rows($result);
        if ($num_rows <= $ipp)
            return '';

        // determine URL separator
        if (ereg('\?',$self))
            $sep = "&";
        else
            $sep = "?";

        // compute number of pages
        $num_pages = floor(($num_rows - 1) / $ipp) + 1;
        $current_page = floor($offset / $ipp);

        // compute current position
        $current = $offset + 0;
        $previous = $current - $ipp;
        if ($previous < 0)
            $previous = 0;
        $remainder = $num_rows % $ipp;
        if (($num_rows % $ipp) != 0)
            $last = $num_rows - ($num_rows % $ipp);
        else
            $last = $num_rows - $ipp;
        $next = $current + $ipp;
        if ($next > $last)
            $next = $last;

        // generate pages
        $out = '';
        if ($num_pages > 20) {
            $start = $current_page - 10;
            if ($start < 0)
                $start = 0;
            $finish = $start + 20;
            if ($finish > $num_pages)
                $finish = $num_pages;
        }
        else {
            $start = 0;
            $finish = $num_pages;
        }
        if ($start > 0)
            $out = "...\n";
        for($i=$start;$i<$finish;$i++) {
            $off = $i * $ipp;
            $page = $i + 1;
            if ($i == $current_page)
                $out .= "$page";
            else
                $out .= "<a href=\"$self${sep}offset=$off\">$page</a>";
            $out .= "\n";
        }
        if ($finish < $num_pages)
            $out .= "...\n";
        if ($offset!=0) {
            $pfirst = sprintf("<a href=\"$self\">%s</a>\n",_PROMPT_FIRST);
            $pprev = sprintf("<a href=\"$self${sep}offset=$previous\">%s</a>\n",_PROMPT_PREV);
        }
        else {
            $pfirst = _PROMPT_FIRST."\n";
            $pprev = _PROMPT_PREV."\n";
        }
        if ($offset!=$last) {
            $pnext = sprintf("<a href=\"$self${sep}offset=$next\">%s</a>\n",_PROMPT_NEXT);
            $plast = sprintf("<a href=\"$self${sep}offset=$last\">%s</a>\n",_PROMPT_LAST);
        }
        else {
            $pnext = _PROMPT_NEXT."\n";
            $plast = _PROMPT_LAST."\n";
        }
        return "<p class=\"recordset\">$pfirst $pprev [ $out ] $pnext $plast</p>\n";
    }

/* folderlist(id) - returns an array of folders available to user ID
**   array is $a[id] = 'Name'
*/
function folderlist($id,$limit='') {
    global $DB,$DOC_REQUIRE_FOLDER;
    $admin = isadmin();
    if ($DOC_REQUIRE_FOLDER)
      $a[0] = "Please select a folder (required)";
    else
      $a[0] = "None";
    $r = $DB->read("SELECT folder_id,folder_name,folder_type ".
                    "FROM folders ".
                    "ORDER BY folder_name");
    while(list($fid,$fname,$class) = $DB->fetch_array($r)) {
        $f = new $class($fid);
        if (($f->get_property(folder_limit_type) == '') ||
            ($f->get_property(folder_limit_type) == $limit)) {
            if (($f->get_property(folder_owner_id) == $id)||$admin)
                $a[$fid] = $fname;
            else if (issubmittor($fid,'folder'))
                $a[$fid] = $fname;
            else if ($f->get_property(folder_public))
                $a[$fid] = "(Public) ".$fname;
        }
    }
    return $a;
}

// restricted - put a call to this function in every page that is restricted
function restricted() {
    global $CURUSER,$REGISTER_MODEL;
    if (($REGISTER_MODEL=='closed') && (!$CURUSER)) {
        header('Location: index.php');
    }
}

// parse_text - converts text to HTML as best possible
function parse_text($str) {

  if ($str=='')
    return $str;
  $pos = strpos($str,'<');
  if ($pos === false) {

    $out = $str;

    // convert http:// to [link] style
    $out = preg_replace(
            '/(?<![\!\[\|])((http|https|ftp|mailto):\/\/[\w\.\+\#\?\/\=\%\-\,\&\;\~]+[\w\+\#\?\/\=\%\-])(?=[\W\'\"\.\,]*\s*)/m',
            '[\1|\1]',
            $out);

    $out = str_replace('!http://', 'http://', $out);
    // replace [URL] type links with [URL|URL]
    $out = preg_replace(
            '/(?<!\[)\[([^\|\]\[]+)\]/mU',
            '[\1|\1]',
            $out);

    // replace [text|URL] type links
    $out = preg_replace(
            '/(?<!\[)\[([^\[].+)\|(.+)\]/mU',
            '<a href="\2">\1</a>',
            $out);
    $out = str_replace('[[', '[', $out);

    // tables

    $out = preg_replace(
            '/\|\^([^\|\n]*)/',
            '<td align="center">\1</td>',
            $out);
    $out = preg_replace(
            '/\|\>([^\|\n]*)/',
            '<td align="right">\1</td>',
            $out);
    $out = preg_replace(
            '/(\|\<|\|\|)([^\|\n]*)/',
            '<td>\2</td>',
            $out);
    $out = preg_replace(
            '/^(\<td.*\/td\>)$/m',
            '<tr>\1</tr>',
            $out);
    $out = preg_replace(
            '/(?<!\<\/tr\>)(<tr>.*<\/tr>)(?!\n\<tr>)/sU',
            "<table class=\"simple\">\n\\1\n</table>",
            $out);


    // double single quotes = italic
    $out = preg_replace(
            '|\'\'(.+)\'\'|mU',
            '<em>\1</em>',
            $out);

    // double underscore = bold
    $out = preg_replace(
            '|__(.+)__|mU',
            '<b>\1</b>',
            $out);

    // get rid of carriage returns
    $out = str_replace("\r","",$out);

    // line breaks
    $out = str_replace('%%%', "<br/>", $out);

    // rules
    $out = preg_replace('/^----+/m', "<hr/>\n", $out);

    // preformatted
    $out = preg_replace(
            '/(?<=\n|$)( +.*)\n*(?=\n[^\s])/sU',
            "<pre>\n\\1\n</pre>",
            $out);

    // unordered lists
    $out = preg_replace(
            '/^\*\s*(.*)$/m',
            "<li>\\1</li>",
            $out);
    $out = preg_replace(
            '/(?<!\<\/li\>\n)(<li>.*<\/li>)(?!\n\<li>)/sU',
            "<ul>\n\\1\n</ul>\n",
            $out);

    // ordered lists
    $out = preg_replace(
            '/^\#\s*(.*)$/m',
            "<li>\\1</li>",
            $out);
    $out = preg_replace(
            '/(?<!\<\/li\>\n|\<ul\>\n)(<li>.*<\/li>)(?!\n\<li>|\n\<\/ul)/sU',
            "<ol>\n\\1\n</ol>\n",
            $out);

    // definition lists
    $out = preg_replace('/^;(.*):(.*)$/mU',
                        '<dt>\1</dt><dd>\2</dd>',
                        $out);
    $out = preg_replace(
            '/(?<!\<\/dd\>\n)(<dt>.*<\/dd>)(?!\n\<dt>)/sU',
            "<dl>\n\\1\n</dl>\n",
            $out);

    // headings
    $out = preg_replace('/^!!!\s*(.*)$/m', '<h2>\1</h2>', $out);
    $out = preg_replace('/^!!\s*(.*)$/m', '<h3>\1</h3>', $out);
    $out = preg_replace('/^!\s*(.*)$/m', '<h4>\1</h4>', $out);

    // what is a paragraph?
    // it starts with either multiple newlines \n\n+[^<]
    // or a >\n+[^<]
    $out = preg_replace(
            '/(?<=\>\n|\n\n|^)\n*([^<\s].+)\n*(?=\n\n|\n\<|$)/Us',
            "<p>\\1</p>",
            $out);

    return $out;
  }
  else
    return $str;
}

// used for debugging
function logfile($msg,$a='',$b='',$c='',$d='',$e='',$f='',$g='') {
    $fp = fopen("/tmp/debug.log","a");
    fwrite($fp,sprintf("%s $msg\n",microtime(),$a,$b,$c,$d,$e,$f,$g));
    fclose($fp);
}

// get_sorted_dir(path)
// returns a sorted array of files in a directory
function get_sorted_dir($path) {
    $a = array();
    $dp = @opendir($path);
    if ($dp) {
        while(false != $file=readdir($dp)) {
            if ($file!='.' && $file!='..' && $file!='CVS') {
                $a[] = $path.'/'.$file;
            }
        }
    }
    @closedir($dp);
    sort($a);
    return $a;
}

// siteframe_filename(user_id,doc_id,name)
// constructs a valid filename
function siteframe_filename($name,$ext='') {
    $name = preg_replace('/\s+/','-',strtolower($name));
    $name = preg_replace('/[^a-z0-9\.-]/','_',$name);
    if ($ext!='') {
        $name = preg_replace('/\.[^\.]+$/',$ext,$name);
    }
    return urlencode($name);
}

// siteframe_userdir($uid)
// constructs a path to a user directory
function siteframe_userdir($uid) {
    global $FILEPATH;
    return sprintf('%s%04d/',$FILEPATH,$uid);
}

/*---------------------------------------------------------------------
** This code is executed for every page
*/

$LANGUAGE = 'en';               // set a default language (can be overridden)
$DB = new Db();                 // establish default database connection
$PAGE = new Template();         // default page template

$r = $DB->read("SELECT name,value FROM properties");
while(list($name,$val) = $DB->fetch_array($r)) {
    global $$name;
    $$name = $val;
    $PAGE->set_property(strtolower($name),$val);
}

// use the cookie to "login" the user
if ($_COOKIE[COOKIENAME]) {
    $CURUSER = new User(0,0,$_COOKIE[COOKIENAME]);
    if (!$CURUSER->get_property(user_id)) {
        setcookie(COOKIENAME);
    }
    else if ($CURUSER->get_properties()) {
        foreach($CURUSER->get_properties() AS $name => $val) {
            $var = strtoupper($name);
            if ($val != -1) {
                $$var = $val;           // set global override properties
                $PAGE->set_property($name,$val);
            }
        }
    }
}

// track session visits
if ($TRACK_SESSIONS && !$SESSION) {
    setcookie('SESSION',time());
    $q = sprintf("INSERT INTO sessions (session_date,session_uid,remote_ip,referer,agent,authuser) ".
                 "VALUES (NOW(),%d,'%s','%s','%s','%s')",
             $CURUSER ? $CURUSER->get_property(user_id) : 0,
             $_SERVER['REMOTE_ADDR'],
             $_SERVER['HTTP_REFERER'],
             $_SERVER['HTTP_USER_AGENT'],
             $_SERVER['PHP_AUTH_USER']);
    $DB->write($q);
}

// load the theme templates
if (!$SITE_THEME)
    $SITE_THEME='default';
if (!$USER_THEME || trim($THEME)=='')
    $THEME = $SITE_THEME;
$PAGE->load_theme($THEME);

// load the content templates
$PAGE->set_property('site_templates',$SITE_TEMPLATES);
$PAGE->set_path("${LOCAL_PATH}/${SITE_TEMPLATES}");

// autoload macro definitions
// MACRO_AUTOLOAD is now deprecated
// read all macro files
if ($mlist = get_sorted_dir("${LOCAL_PATH}macros")) {
    foreach($mlist as $file) {
        $fp = fopen($file,'r');
        $mactmp = '';
        if (!$fp) {
            logmsg("Unable to open macro file %s",$file);
        }
        else {
            while(!feof($fp)) {
               $mactmp .= fgets($fp,2048);
            }
            macro($mactmp);
            fclose($fp);
        }
    }
}

// set some default variables for every page
$PAGE->set_property('siteframe_version',SITEFRAME_VERSION);
$PAGE->set_property('current_file',$PHP_SELF);
$PAGE->set_property('site_name',$SITE_NAME);
$PAGE->set_property('site_url',$SITE_URL);
// thanks to Barend Jan de Jong for suggestion to support https URLs
if (preg_match('/https{0,1}:\/\/[^\/]+(\/.*)/',$SITE_URL,$m)) {
    $SITE_PATH = $m[1];
    $PAGE->set_property('site_path',$m[1]);
}
else {
    $SITE_PATH = '';
    $PAGE->set_property('site_path','');
}
$PAGE->set_property('site_email',$SITE_EMAIL);
$PAGE->set_property('theme',$THEME);
$PAGE->set_property('theme_display',str_replace('_',' ',$THEME));
if (trim($LOGO)!='')
    $PAGE->set_property('logo',$LOGO);

include "${LOCAL_PATH}lang/${LANGUAGE}.php";
$PAGE->set_property('language',$LANGUAGE_CODE);
if ($CHARSET!='')
    $PAGE->set_property('charset',$CHARSET);
else
    $PAGE->set_property('charset','iso-8859-1');

// create page GET variables
foreach($_GET as $a => $b) {
  $PAGE->set_property("_GET_$a",$b);
}
foreach($_COOKIE as $a => $b) {
  $PAGE->set_property("_COOKIE_$a",$b);
}

// load and initialize all plugins
if ($mlist = get_sorted_dir("${LOCAL_PATH}plugins")) {
    foreach($mlist as $file) {
        include "$file";
    }
}

// navigation
$DEFAULT_NAVIGATION = <<<ENDNAVIGATION
  <a href="{site_path}/">Home</a><br/>
  <a href="{site_path}/register.php">Register</a><br/>
  <a href="{site_path}/login.php">Login</a><br/>
  <a href="{site_path}/search.php">Search</a><br/>
  <a href="{site_path}/user.php">My Page</a><br/>
  <a href="{site_path}/prefs.php">Preferences</a><br/>
  <a href="{site_path}/edit.php">New Document</a><br/>
  <a href="{site_path}/editfolder.php">New Folder</a><br/>
  <a href="{site_path}/folders.php">List Folders</a><br/>
  <a href="{site_path}/docs.php">List Documents</a><br/>
  <a href="{site_path}/groups.php">List Groups</a><br/>
  <a href="{site_path}/users.php">List Users</a><br/>
  {BEGIN:folder_subfolders 0}
  {!if '{row_number}==1' '<b>Folders</b><br/>'!}
  <a href="{site_path}/folder.php?id={folder_id}">{folder_name_display}</a><br/>
  {END:folder_subfolders}
  {BEGIN:categories}
  {!if '{row_number}==1' '<b>Categories</b><br/>'!}
  <a href="{site_path}/category.php?id={category_id}">{category_name}</a><br/>
  {END:categories}
  {!if admin(user_id) '<a href="{site_path}/admin/">Control Panel</a>'!}
ENDNAVIGATION;
$PAGE->set_property('default_navigation',$DEFAULT_NAVIGATION);
if ($NAVIGATION!='') {
    $PAGE->set_property('_nav_',
      parse_text($PAGE->get_template_body($NAVIGATION)));
    $PAGE->set_property('_nav_',$PAGE->parse('_nav_'));
    $PAGE->set_property('navigation',$PAGE->parse('_nav_'));
}
else {
    $PAGE->set_property(_navigation_,$DEFAULT_NAVIGATION);
    $PAGE->set_property(navigation,$PAGE->parse(_navigation_));
}
// footer
if ($FOOTER_TEXT!='') {
    $PAGE->set_property('_footer_',
      parse_text($PAGE->get_template_body($FOOTER_TEXT)));
    $PAGE->set_property('footer',$PAGE->parse('_footer_'));
}
else
    $PAGE->set_property(footer);
// auto-load macros
if ($MACRO_AUTOLOAD!='') {
    macro($PAGE->get_template_body($MACRO_AUTOLOAD));
}

// default property values
if (!$SITE_DATE_FORMAT) {
    $SITE_DATE_FORMAT = 'Y-M-d';
    $PAGE->set_property('site_date_format',$SITE_DATE_FORMAT);
}
if (!$SITE_TIME_FORMAT) {
    $SITE_TIME_FORMAT = 'H:i T';
    $PAGE->set_property('site_time_format',$SITE_TIME_FORMAT);
}

// let's give'em a random number
$PAGE->set_property('rand',rand());

// load checkbox-replacement symbols
$q = "SELECT obj_prop_name,obj_prop_options FROM obj_props WHERE obj_prop_type='checkbox'";
$r = $DB->read($q);
$SYMBOLS['##'] = '##'; // need an empty one to make an array
while(list($name,$opt) = $DB->fetch_array($r)) {
  if (trim($opt)!='') {
    $SYMBOLS[$name] = $opt;
    $SYMBOLS["user_$name"] = $opt;
    $SYMBOLS["doc_$name"] = $opt;
    $SYMBOLS["comment_$name"] = $opt;
    $SYMBOLS["folder_$name"] = $opt;
  }
}

// establish default document classes
establish_classes();    // define document classes (in classes.php)

if (!$ADMIN_PAGE && $MAINTENANCE_MODE) {
    $PAGE->set_property(body,$MAINT_MODE_MSG!='' ? $MAINT_MODE_MSG :
      'The site is currently undergoing maintenance.<br/>Please check back later.');
    $PAGE->set_property('page_title','Maintenance');
    $PAGE->pparse('page');
    exit;
}

if ($PHP_SELF=='')
  $PHP_SELF = $_SERVER['PHP_SELF'];

// clean up attack vectors
function clean_it_up( $arr )
{
	foreach( $arr as $name => $value )
	{
		switch( $name )
		{
		case 'comment_doc_id':
		case 'comment_reply_to':
		case 'folder':
		case 'group':
		case 'group_id':
		case 'id':
		case 'user':
			$$global[ $name ] = mysql_real_escape_string( $value );
			break;
		default:
		}
	}
	return $arr;
}
if (isset($_GET))  $_GET = clean_it_up( $_GET );
if (isset($_POST)) $_POST = clean_it_up( $_POST );