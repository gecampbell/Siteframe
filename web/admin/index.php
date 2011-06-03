<?php
// admin/index.php - administrative interface
// $Id: index.php,v 1.41 2007/09/17 00:12:53 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.

require "siteframe.php";

if (trim($SITE_NAME) == '') {
    header('Location: globals.php');
    exit;
}

if (!$NUM_ADMINS) {
  header('Location: newadmin.php');
  exit;
}

// dir_size - compute size of directory
function dir_size($dir) {
    $totalsize=0;
    if ($dirstream = @opendir($dir)) {
        while (false !== ($filename = readdir($dirstream))) {
            if ($filename!="." && $filename!="..") {
                if (is_file($dir."/".$filename))
                    $totalsize+=filesize($dir."/".$filename);

                if (is_dir($dir."/".$filename))
                    $totalsize+=dir_size($dir."/".$filename);
            }
        }
        closedir($dirstream);
    }
    return $totalsize;
}

$PAGE->set_property(page_title,"Admin Menu");

// maintenance mode
if (isset($_GET['mmode'])) {
    $MAINTENANCE_MODE = 1-$MAINTENANCE_MODE;
    set_global('MAINTENANCE_MODE',$MAINTENANCE_MODE);
}
$MNT = $MAINTENANCE_MODE ? "ON" : "OFF";

// get some stats
list($num_users) = $DB->fetch_array($DB->read("SELECT COUNT(*) FROM users"));
list($num_docs) = $DB->fetch_array($DB->read("SELECT COUNT(*) FROM docs"));
list($num_fols) = $DB->fetch_array($DB->read("SELECT COUNT(*) FROM folders"));
list($num_groups) = $DB->fetch_array($DB->read("SELECT COUNT(*) FROM groups"));
list($num_comments) = $DB->fetch_array($DB->read("SELECT COUNT(*) FROM comments"));
$space_user = 'N/A';
/* uncomment to compute directory size */
// $space_used = number_format(dir_size($LOCAL_PATH.$FILEPATH)/(1024*1024),2);

if (isset($SITE_NAME))
    $global_properties = "Basic configuration";
else
    $global_properties = "<b>Basic configuration</b>";

if ($IS_REGISTERED)
    $register = "Register your site";
else {
    $register = "<b>Register your site</b>";
    if (rand(1,10)==3) {
        $PAGE->set_property('error',
            '<a href="register.php">Click here to register your site</a>');
    }
}

if ($HOME_PAGE) {
  list($id) = $DB->fetch_array($DB->read(sprintf('SELECT tpl_id FROM templates WHERE tpl_name=\'%s\' AND tpl_theme_id=0',$HOME_PAGE)));
  $CPINDEX['Templates'][sprintf('Edit home page: %s',$HOME_PAGE)] =
    sprintf("$SITE_PATH/admin/edittemplate.php?id=%d",$id);
}
if ($NAVIGATION) {
  list($id) = $DB->fetch_array($DB->read(sprintf('SELECT tpl_id FROM templates WHERE tpl_name=\'%s\' AND tpl_theme_id=0',$NAVIGATION)));
  $CPINDEX['Templates'][sprintf('Edit navigation: %s',$NAVIGATION)] =
    sprintf("$SITE_PATH/admin/edittemplate.php?id=%d",$id);
}
if ($FOOTER_TEXT) {
  list($id) = $DB->fetch_array($DB->read(sprintf('SELECT tpl_id FROM templates WHERE tpl_name=\'%s\' AND tpl_theme_id=0',$FOOTER_TEXT)));
  $CPINDEX['Templates'][sprintf('Edit footer: %s',$FOOTER_TEXT)] =
    sprintf("$SITE_PATH/admin/edittemplate.php?id=%d",$id);
}
if ($MACRO_AUTOLOAD) {
  list($id) = $DB->fetch_array($DB->read(sprintf('SELECT tpl_id FROM templates WHERE tpl_name=\'%s\' AND tpl_theme_id=0',$MACRO_AUTOLOAD)));
  $CPINDEX['Templates'][sprintf('Edit macros: %s',$MACRO_AUTOLOAD)] =
    sprintf("$SITE_PATH/admin/edittemplate.php?id=%d",$id);
}

$body = <<<END
<table class="stats">
<tr style="background:gray; color:white;"><td colspan="2"><b>Information</b></td></tr>
<tr><td>Version:    </td><td align="right">{siteframe_version}</td></tr>
<tr><td>Users:      </td><td align="right"> $num_users</td></tr>
<tr><td>Documents:  </td><td align="right"> $num_docs</td></tr>
<tr><td>Folders:    </td><td align="right"> $num_fols</td></tr>
<tr><td>Groups:     </td><td align="right"> $num_groups</td></tr>
<tr><td>Comments:   </td><td align="right"> $num_comments</td></tr>
<tr><td>Space used: </td><td align="right"> ${space_used}MB</td></tr>
<tr><td>File path:  </td><td> $FILEPATH</td></tr>
</table>
{control_panel}
<p class="info">{cvsid}</p>
END;

$CPINDEX['Configuration'][$global_properties] = "$SITE_PATH/admin/globals.php";
$CPINDEX['Configuration']['Extended properties'] = "$SITE_PATH/admin/properties.php";
$CPINDEX['Configuration']['Maintain categories'] = "$SITE_PATH/admin/categories.php";
$CPINDEX['Configuration']['Maintain document types'] = "$SITE_PATH/admin/objs.php";
$CPINDEX['Configuration']['Maintain object properties'] = "$SITE_PATH/admin/obj_props.php";
$CPINDEX['Configuration']['Create new administrator'] = "$SITE_PATH/admin/newadmin.php";

$CPINDEX['Contact']['Send e-mail to site members'] = "$SITE_PATH/admin/mailall.php";
$CPINDEX['Contact']['Send online note to site members'] = "$SITE_PATH/admin/noteall.php";
$CPINDEX['Contact']['Send feedback on Siteframe'] = "$SITE_PATH/admin/feedback.php";
$CPINDEX['Contact'][$register] = "$SITE_PATH/admin/register.php";
$CPINDEX['Contact']['Unsubscribe members'] = "$SITE_PATH/admin/unsubscribe.php";

$CPINDEX['Reports']['Activity log'] = "$SITE_PATH/admin/log.php";
$CPINDEX['Reports']['Visits per day: report'] = "$SITE_PATH/admin/report_days.php";
$CPINDEX['Reports']['Visits per day: chart'] = "$SITE_PATH/admin/chart_days.php";
$CPINDEX['Reports']['User agents'] = "$SITE_PATH/admin/agent.php";
$CPINDEX['Reports']['Referers'] = "$SITE_PATH/admin/referer.php";
$CPINDEX['Reports']['Recent visitors'] = "$SITE_PATH/admin/recent.php";

$CPINDEX['Templates']['Import theme/template XML file'] = "$SITE_PATH/admin/importtheme.php";
$CPINDEX['Templates']['Maintain themes'] = "$SITE_PATH/admin/themes.php";
$CPINDEX['Templates']['Maintain content templates'] = "$SITE_PATH/admin/templates.php";

$CPINDEX['Maintenance']['Clear the session log'] = "$SITE_PATH/admin/clearlog.php";
$CPINDEX['Maintenance']['Clear the activity log'] = "$SITE_PATH/admin/log.php?clear=1";
$CPINDEX['Maintenance']["Toggle maintenance mode (currently $MNT)"] = "$SITE_PATH/admin/?mmode";
$CPINDEX['Maintenance']['Upload a file'] = "$SITE_PATH/admin/upload.php";

ksort($CPINDEX);

foreach ($CPINDEX as $category => $a) {
  $out .= sprintf("<p><b>%s</b><br/>\n",$category);
  ksort($a);
  foreach($a as $prompt => $url) {
    $out .= sprintf("<a href=\"%s\">%s</a><br/>\n",$url,$prompt);
  }
  $out .= "</p>\n";
}

$PAGE->set_property('cvsid','$Id: index.php,v 1.41 2007/09/17 00:12:53 glen Exp $');
$PAGE->set_property('control_panel',$out);
$PAGE->set_property('_body_',$body);
$PAGE->set_property('body',$PAGE->parse('_body_'));
$PAGE->pparse(page);

?>
