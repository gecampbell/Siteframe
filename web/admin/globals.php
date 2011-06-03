<?php
// globals.php
// $Id: globals.php,v 1.6 2003/06/27 02:18:40 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// central, core global properties

require "siteframe.php";

// set some default values
if ($FILEPATH=='')                  set_global('FILEPATH','files/');
if ($DEFAULT_IMAGE_SIZE=='')        set_global('DEFAULT_IMAGE_SIZE',400);
if ($SELFPORTRAIT_SIZE=='')         set_global('SELPORTRAIT_SIZE',150);
if ($PUBLISH_MODEL=='')             set_global('PUBLISH_MODEL','open');
if ($IMAGE_QUALITY=='')             set_global('IMAGE_QUALITY',80);
if ($MAX_IMAGE_SIZE=='')            set_global('MAX_IMAGE_SIZE',1200);
if ($ALLOW_COMMENTS=='')            set_global('ALLOW_COMMENTS',1);
if ($COMMENT_SUBJECTS=='')          set_global('COMMENT_SUBJECTS',1);
if ($LINES_PER_PAGE=='')            set_global('LINES_PER_PAGE',20);
if ($THEME=='')                     set_global('THEME','default');
if ($SITE_THEME=='')                set_global('SITE_THEME','default');
if ($NOTICES_ADMIN_ONLY=='')        set_global('NOTICES_ADMIN_ONLY',1);
if ($CHARSET=='')                   set_global('CHARSET','iso-8859-1');
if ($REPORT_DAYS=='')               set_global('REPORT_DAYS',1);
if ($SITE_DATE_FORMAT=='')          set_global('SITE_DATE_FORMAT','F j, Y');
if ($SITE_TIME_FORMAT=='')          set_global('SITE_TIME_FORMAT','h:ia');
if ($SITE_TEMPLATES=='')            set_global('SITE_TEMPLATES','templates');
if ($TREE_TITLE=='')                set_global('TREE_TITLE','Site Map');
if ($COOKIE_DAYS=='')               set_global('COOKIE_DAYS',7);
if ($TRACK_SESSIONS=='')            set_global('TRACK_SESSIONS',1);
if ($DEFAULT_USER_SUBSCRIBE=='')    set_global('DEFAULT_USER_SUBSCRIBE',1);
if ($CACHED_VALUE_UPDATE_TIME=='')  set_global('CACHED_VALUE_UPDATE_TIME',60);
if ($OKTAGS=='')                    set_global('OKTAGS',
<<<ENDOKTAGS
<b> <big> <blink> <blockquote> <br> <br/> <hr> <i> <s> <small>
<strike> <tt> <u> <abbr> <cite> <code> <del> <dfn> <em> <ins> <kbd>
<samp> <strong> <var> <a> <p> <ul> <ol> <li> <dl> <dt> <dd> <table>
<th> <tr> <td> <h1> <h2> <h3> <h4> <h5> <h6> <div> <img> <span>
ENDOKTAGS
);

// process the submitted form
if ($_POST['submitted']) {
  foreach($_POST as $name => $value) {
    switch($name) {
    case 'SITE_URL':
      $urlarr = parse_url($value);
      if (($urlarr['scheme']!='http')&&($urlarr['scheme']!='https')) {
        $PAGE->set_property('error','Unrecognized scheme for SITE_URL<br/>',TRUE);
      }
      else {
        if (substr($value,strlen($value)-1,1) == '/') {
          $value = substr($value,0,strlen($value)-1);
        }
        set_global($name,$value);
        $$name = $value;
      }
      break;
    case 'submitted':
      break;
    default:
      set_global($name,$value);
      $$name = $value;
    }
  }
  set_global('SITE_THEME',$THEME);
  // if there are no administrators, then make one
  if (!$NUM_ADMINS) {
    header('Location: newadmin.php');
    exit;
  }
  else {
    $PAGE->set_property('error','Configuration updated');
  }
}

// define the core global properties
$CONFIG[] = array(
  name => 'SITE_NAME',
  type => 'text',
  size => 250,
  focus => 1,
  value => $SITE_NAME,
  prompt => 'Website title',
  doc => 'The website name is used as the title of the home page and in most '.
         'references throughout the website.'
);
$defaulturl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$defaulturl = preg_replace('/\/admin.*/','',$defaulturl);
$CONFIG[] = array(
  name => 'SITE_URL',
  type => 'text',
  size => 250,
  value => ($SITE_URL=='') ? $defaulturl : $SITE_URL,
  prompt => 'Website URL',
  doc => 'Enter the URL of your website, including the connection method (http:// '.
         'or https://. Do <em>not</em> include a trailing slash, but do include '.
         'the complete path to your website root directory (i.e., where index.php '.
         'is installed).'
);
$CONFIG[] = array(
  name => 'SITE_DESCRIPTION',
  type => 'textarea',
  rows => 2,
  value => $SITE_DESCRIPTION,
  prompt => 'Website description or tagline',
  doc => 'The website description is used on the home page (in most themes) and '.
         'will appear as a generic description of the website. This field is '.
         'usually used for a "tagline" or brief sentence to describe your site.'
);
$CONFIG[] = array(
  name => 'SITE_EMAIL',
  type => 'text',
  size => 250,
  value => $SITE_EMAIL,
  prompt => 'Website e-mail address',
  doc => 'Most e-mails that originate from your website will show this e-mail '.
         'address as the origin. Make sure that this is a valid address, since '.
         'return notifications will be sent here.'
);
$CONFIG[] = array(
  name => 'REGISTER_MODEL',
  type => 'select',
  help => 'register_model',
  options => array(
    'confirm' => 'Confirm (requires e-mail confirmation)',
    'open' => 'Open (allows immediate access)',
    'closed' => 'Closed (new members must be added by administrator)'
  ),
  value => $REGISTER_MODEL,
  prompt => 'User registration model',
  doc => 'This setting determines how new users can join your website. '.
         'If the registration model is <b>Open</b>, then new site members '.
         'can join as soon as they complete the registration form. If the '.
         'setting is <b>Confirm</b>, then new users must confirm that they '.
         'have a valid e-mail address. If the model is <b>Closed</b>, '.
         'then new site members can only be added by a site administrator. '.
         'The <b>Open</b> model is <em>not</em> recommended, since it can '.
         'leave the website administrator liable for abuse by anonymous site '.
         'members.'
);
$CONFIG[] = array(
  name => 'LANGUAGE',
  type => 'select',
  options => $LANGUAGES,
  value => $LANGUAGE,
  prompt => 'Default website language',
  doc => 'This setting controls the default language for many of the prompts '.
         'and other "hard-coded" text on the website. It has no effect on any '.
         'content created by you or site members.'
);
// build a list of themes
$r = $DB->read("SELECT theme_name FROM themes ".
               "WHERE theme_name NOT LIKE '%.BAK' ORDER BY theme_name");
while(list($nm) = $DB->fetch_array($r))
  $themelist[$nm] = str_replace('_',' ',$nm);
$CONFIG[] = array(
  name => 'THEME',
  type => 'select',
  options => $themelist,
  value => $THEME,
  prompt => 'Default theme for the site',
  doc => 'The theme controls the overall appearance or "look and feel" of '.
         'the website. Once the site is configured, you can choose a different '.
         'theme or even create a new theme (or edit an existing one).'
);
$CONFIG[] = array(
  name => 'SITE_DATE_FORMAT',
  type => 'select',
  options => array(
    'Y/m/d'     => date('Y/m/d'),
    'Y/M/d'     => date('Y/M/d'),
    'd/M/Y'     => date('d/M/Y'),
    'm/d/y'     => date('m/d/y'),
    'Y-m-d'     => date('Y-m-d'),
    'Y-M-d'     => date('Y-M-d'),
    'd-M-Y'     => date('d-M-Y'),
    'm-d-y'     => date('m-d-y'),
    'M d'       => date('M d'),
    'F j, Y'    => date('F j, Y'),
    'j F Y'     => date('j F Y'),
    'jS F Y'    => date('jS F Y'),
    'F jS, Y'   => date('F jS, Y'),
    'Y.m.d'     => date('Y.m.d'),
    'm.d.Y'     => date('m.d.Y'),
    'd.m.Y'     => date('d.m.Y'),
    'y.m.d'     => date('y.m.d'),
    'm.d.y'     => date('m.d.y'),
    'd.m.y'     => date('d.m.y'),
  ),
  value => $SITE_DATE_FORMAT,
  prompt => 'Default format for date',
  doc => 'The default display format for date values.'
);
$CONFIG[] = array(
  name => 'SITE_TIME_FORMAT',
  type => 'select',
  options => array(
    'H:i'       => date('H:i'),
    'h:i a'     => date('h:i a'),
    'h:i A'     => date('h:i A'),
    'H:i T'     => date('H:i T'),
    'H:i:s T'   => date('H:i:s T'),
    'h:i a T'   => date('h:i a T'),
    'h:i:s a T' => date('h:i:s a T'),
    'h:i A T'   => date('h:i A T'),
    'h:i:s A T' => date('h:i:s A T'),
    'h.i.s'     => date('h.i.s'),
    'H.i.s'     => date('H.i.s'),
    'B'         => date('B').' (Swatch Internet Time)'
  ),
  value => $SITE_TIME_FORMAT,
  prompt => 'Default format for time',
  doc => 'The default display format for time values.'
);
$CONFIG[] = array(
  name => 'ENCRYPTION',
  type => 'select',
  options => array(
                'MD5'  => 'MD5() encryption (use with MySQL &lt; 4.0)',
                'SHA1' => 'SHA1() encrytion (use with MySQL 4.0 and later)'
              ),
  disabled => ($ENCRYPTION!='') ? 1 : 0,
  value => $ENCRYPTION,
  prompt => 'Password encryption method',
  doc => 'If you are using MySQL 4.0 or later, the SHA1() method is '.
         'recommended; use MD5() for older versions of MySQL. If this '.
         'selection is disabled, it is because an encryption method has '.
         'already been selected, and it cannot be changed. These are '.
         'one-way encryption methods, and there is no method available '.
         'to convert between them.'
);

// the form instructions
$instructions = <<<ENDINSTRUCTIONS
<p>Use this page to define the core configuration for your website
(you can modify these settings later through the Control Panel &rarr; Basic
Configuration page). When complete, press <b>Submit</b> to continue.</p>
ENDINSTRUCTIONS;

// finally, put everything together
$PAGE->set_property('page_title','Basic Configuration');
$PAGE->set_property('form_instructions',$instructions);
$PAGE->set_property('form_action','globals.php');
$PAGE->input_form('body',$CONFIG);
$PAGE->pparse('page');

?>
