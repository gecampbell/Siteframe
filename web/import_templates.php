<html>
<head><title>Import Templates</title>
</head>
<body>
<pre>
<?php
// import_templates.php
// $Id: import_templates.php,v 1.6 2003/06/11 06:39:04 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// imports template files into the themes/templates table

//require "siteframe.php";
require "siteconfig.php";
require "config.php";
require "globals.php";
require "classes/siteframe.php";
require "classes/db.php";
require "admin/sftemplate.php";

function clean($s) {
  return $s;
}
function logmsg($s) {
  return;
}

$DB = new Db();

$r = $DB->read('SELECT name,value FROM properties');
while(list($name,$value) = $DB->fetch_array($r))
  $$name = $value;

$r = $DB->read('SELECT COUNT(*) FROM templates');
list($num_tpl) = $DB->fetch_array($r);
if ($num_tpl)
  die('Sorry - there are already templates in the database');

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

function fixit($s) {
  $s = preg_replace('/{!include.*(\w+)\.ihtml.*!}/U','{template:\1}',$s);
  $s = preg_replace('/page=(\w+)\.ihtml/U','page=\1',$s);
  return $s;
}

$themedirs = get_sorted_dir(THEMEPATH);
foreach($themedirs as $themedir) {
  $themefiles = get_sorted_dir($themedir);
  $themename = fname_only($themedir);

  // delete existing themes
  $r = $DB->read(
    sprintf('SELECT theme_id FROM themes WHERE theme_name=\'%s\'',
      $themename)
  );
  list($id) = $DB->fetch_array($r);
  @$DB->write(
    sprintf('DELETE FROM themes WHERE theme_id=%d',$id)
  );
  print mysql_error();
  @$DB->write(
    sprintf('DELETE FROM templates WHERE tpl_theme_id=%d',$id)
  );
  print mysql_error();

  // build the new one
  $th = new sftheme();
  $th->set_property('theme_name',$themename);
  $th->add();
  foreach($themefiles as $filename) {
    $tpl = new sftemplate();
    $tpl->set_property('tpl_theme_id',$th->get_property('theme_id'));
    $tpl->set_property('tpl_name',fname_only($filename));
    $tpl->set_property('tpl_filename',$filename);
    $tpl->set_property('tpl_body',fixit(file_get_contents($filename)));
    $tpl->add();
    printf("File: %s, status=%d, msg=%s\n",
        $tpl->get_property('tpl_filename'),$tpl->errcount(),$tpl->get_errors());
  }
}

$tplfiles = get_sorted_dir($SITE_TEMPLATES);
foreach($tplfiles as $filename) {
  $tpl = new sftemplate();
  $tpl->set_property('tpl_name',fname_only($filename));
  $tpl->set_property('tpl_body',fixit(file_get_contents($filename)));
  $tpl->set_property('tpl_theme_id',0);
  @$DB->write(
    sprintf('DELETE FROM templates WHERE tpl_theme_id=0 AND tpl_name=\'%s\'',
      addslashes($tpl->get_property('tpl_name')))
  );
  $tpl->add();
  printf("File: %s, status=%d, msg=%s\n",
        $filename,$tpl->errcount(),$tpl->get_errors());
}

if ($HOME_PAGE) {
  $r = $DB->read("SELECT doc_body FROM docs WHERE doc_id=$HOME_PAGE");
  list($x) = $DB->fetch_array($r);
  $tpl = new sftemplate();
  $tpl->set_property('tpl_name','Home_Page');
  $tpl->set_property('tpl_type_id',1);
  $tpl->set_property('tpl_body',fixit($x));
  $tpl->add();
  set_global('HOME_PAGE','Home_Page');
  printf("Loaded home page template\n");
}
if ($NAVIGATION) {
  $r = $DB->read("SELECT doc_body FROM docs WHERE doc_id=$NAVIGATION");
  list($x) = $DB->fetch_array($r);
  $tpl = new sftemplate();
  $tpl->set_property('tpl_name','Navigation');
  $tpl->set_property('tpl_type_id',2);
  $tpl->set_property('tpl_body',fixit($x));
  $tpl->add();
  set_global('NAVIGATION','Navigation');
  printf("Loaded navigation template\n");
}
if ($FOOTER_TEXT) {
  $r = $DB->read("SELECT doc_body FROM docs WHERE doc_id=$FOOTER_TEXT");
  list($x) = $DB->fetch_array($r);
  $tpl = new sftemplate();
  $tpl->set_property('tpl_name','Footer');
  $tpl->set_property('tpl_type_id',3);
  $tpl->set_property('tpl_body',fixit($x));
  $tpl->add();
  set_global('FOOTER_TEXT','Footer');
  printf("Loaded footer template\n");
}

?>
</pre>
</body>
</html>