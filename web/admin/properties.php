<?php
// properties.php
// $Id: properties.php,v 1.89 2003/09/22 03:03:53 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// allows administrator to set extended properties

require "siteframe.php";

if (!$NUM_ADMINS) {
  header('Location: newadmin.php');
  exit;
}

// define an internal template for the input form
$FORMTPL = <<<ENDFORMTPL
<p>{form_instructions}</p>
<form method="post" action="{form_action}" enctype="multipart/form-data">
<table style="border:none;">
{BEGIN:input_form}
<tr>
{!if '"{input_form_type}"=="ignore"'

  '<td colspan="9" style="background:#dddddd; color:black; padding:2px;">
   {input_form_prompt}</td>'

  '<td><small>{input_form_name}</small></td>
   <td><b>{input_form_prompt}</b><br/>
   {input_form_field}
   <p class="doc">{input_form_doc}</p></td>'
!}
</tr>
{END:input_form}
<tr><td colspan="9">
<input type="hidden" name="submitted" value="1"/>
{BEGIN:input_form_hidden}
 {hidden_form_field}
{END:input_form_hidden}
<input type="submit" value="{input_form_submit}"/>
<input type="reset"/>
</td></tr>
</table>
</form>
ENDFORMTPL;

if (!$DEFAULT_IMAGE_SIZE)
    $DEFAULT_IMAGE_SIZE = 600;
if ($OKTAGS=='')
    $OKTAGS = '<b> <big> <blink> <blockquote> <br> <br/> <hr> <i> <s> <small> <strike> <tt> <u> <abbr> <cite> <code> <del> <dfn> <em> <ins> <kbd> <samp> <strong> <var> <a> <p> <ul> <ol> <li> <dl> <dt> <dd> <table> <th> <tr> <td> <h1> <h2> <h3> <h4> <h5> <h6> <div> <img> <span>';
if (!$PUBLISH_MODEL)
    $PUBLISH_MODEL='open';
if ($FOLDER_PATH_SEP=='')
    $FOLDER_PATH_SEP = "&nbsp;&gt;&nbsp;";
if ($FOLDER_PATH_PREFIX=='')
    $FOLDER_PATH_PREFIX =
      sprintf('<div class="path"><a href="%s/">%s</a> &gt;&nbsp;',$SITE_PATH,$SITE_NAME);
if (!$FOLDER_PATH_SUFFIX)
    $FOLDER_PATH_SUFFIX = '</div>';
if ($CACHED_VALUE_UPDATE_TIME == '')
    $CACHED_VALUE_UPDATE_TIME = 30;
if ($COOKIE_DAYS == '')
    $COOKIE_DAYS = 3;
if ($SFOLDER_AUTO_REMOVE == '')
    $SFOLDER_AUTO_REMOVE = 1;
if ($MAX_DOC_CATEGORIES == '')
    $MAX_DOC_CATEGORIES = 3;
if ($MAX_AD_DAYS == '')
    $MAX_AD_DAYS = 14;
if ($MAX_AD_SIZE == '')
    $MAX_AD_SIZE = 500;
if ($SITE_URL=='')
    $SITE_URL = 'http://';
if ($CHARSET=='')
    $CHARSET='iso-8859-1';
if ($LOG_DAYS=='')
    $LOG_DAYS=7;

// build list of content templates
// of the specified type
function templatelist($typeid) {
  global $DB;
  $tpllist[''] = 'None selected';
  $r = $DB->read(sprintf(
    'SELECT tpl_name FROM templates WHERE tpl_theme_id=0 AND tpl_type_id=%d '.
    'ORDER BY tpl_name',$typeid));
  while(list($nm) = $DB->fetch_array($r))
    $tpllist[$nm] = $nm;
  return $tpllist;
}

// check
if ($FILEPATH=='')
    $FILEPATH = 'files/';

// prepare a list of top-level folders
$q = "SELECT folder_id,folder_name FROM folders WHERE folder_parent_id=0 ORDER BY folder_name";
$r = $DB->read($q);
$topfolders[0] = '-None-';
while(list($fid,$fname) = $DB->fetch_array($r)) {
    $topfolders[$fid] = $fname;
}

// this includes the standard global property definitions
require "global_defs.php";

// check for input form submissions
if ($_POST['submitted']) {
  foreach ($CPGLOBAL as $category => $a) {
    foreach($a as $name => $value) {
      switch($value['type']) {
      case 'file':
        if ($_FILES[$name]['name']!='') {
            @unlink($_GLOBALS[$name]);
            set_global($name,'');
            $obj = new Siteframe(); // "placeholder" object
            $obj->save_file(
                $name,
                $_FILES[$name]['tmp_name'],
                $_FILES[$name]['name'],
                $_FILES[$name]['size'],
                $_FILES[$name]['type'],
                $LOCAL_PATH.$_POST[filepath]);
            set_global($name,str_replace($LOCAL_PATH,'',$obj->get_property($name)));
        }
        break;
      case 'checkbox':
      case 'number':
        set_global($name,$_POST[$name]+0);
        break;
      case 'textboxarray':
      case 'checkboxarray':
        $val = $_POST[$name];
        if (count($val))
          foreach($val as $v)
            $outval[] = $v;
        set_global($name,implode(';',$outval));
        break;
      default:
        if (isset($_POST[$name]))
          set_global($name,trim($_POST[$name]));
      }
    }
  }
  $r = $DB->read("SELECT name,value FROM properties");
  while(list($name,$value)=$DB->fetch_array($r)) {
    global $$name;
    $$name = $value;
  }
  $PAGE->set_property('error','Configuration updated');
}

if ($LOGO_DELETE) {
  @unlink($LOGO);
  set_global('LOGO','');
  set_global('LOGO_DELETE','');
  $LOGO='';
  $LOGO_DELETE='';
}

// build the input form
ksort($CPGLOBAL);
foreach ($CPGLOBAL as $category => $a) {
  $out[] = array(
    type => 'ignore',
    prompt => sprintf("<b>%s</b>\n",preg_replace('/^[0-9]*/','',$category)),
  );
  ksort($a);
  foreach($a as $name => $value) {
    $value['name'] = $name;
    $value['value'] = $$name;
    $value['prompt'] = strtolower($value['prompt']);
    if ($value['type']=='number')
      $value['type']='text';
    $out[] = $value;
  }
}

// define body of page
$instr = <<<END
<p>This page is used to define global properties for Siteframe.
You can come back to this page and modify these properties at any time.
Please be careful with what you enter here;
the values take effect immediately, and if you do something unusual,
it might make life difficult for you. This page contains the most accurate
documentation on these properties available (since it is maintained with
the source code).</p>
<p>Enter the necessary information below and press <b>Set Properties</b>.
END;

$PAGE->set_property('site_name',$PHP_SELF);
$PAGE->set_property('page_title','Extended Properties');
$PAGE->set_property('site_path',$LOCAL_PATH);

$PAGE->set_property('_in_form',$FORMTPL);

$PAGE->set_property(doc_id,0);
$PAGE->set_property(doc_folder_id,0);
$PAGE->set_property(folder_id,0);
$PAGE->set_property(form_name,'install');
$PAGE->set_property(form_action,$PHP_SELF);
$PAGE->set_property(form_instructions,$instr);
$PAGE->set_property(input_form_hidden,'');
$PAGE->input_form(body,$out,'','Set Properties');

$PAGE->pparse('page');
?>
