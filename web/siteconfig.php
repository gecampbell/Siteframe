<?php
// $Id: siteconfig.php,v 1.8 2003/06/07 01:27:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
//
// This configuration file should not normally need to be changed.

// theme - defines the default theme
//   themes are stored under web/themes/<theme-name>
//   the various template file definitions should
//   probably not be changed
$SITE_THEME = $THEME = "default";       // default theme directory
define(THEMEPATH,   'themes/');         // path to theme files
define(TPL_PAGE,    'page');            // main page template
define(TPL_FORM,    'form');            // template for input forms
define(TPL_POPUP,   'popup');           // used for popup windows

// TPL_FLAG_UNDEFINED - if this variable is set to true, then all
// undefined template variables will be replaced with {varname:UNDEFINED}
// in the output text. This can be useful for debugging. The default
// is to replace all undefined template variables with blanks.
$TPL_FLAG_UNDEFINED = false;

// rating - defines the possible values for ratings
$RATING[1] = " 1 (worst)";
$RATING[2] = " 2 ";
$RATING[3] = " 3 ";
$RATING[4] = " 4 ";
$RATING[5] = " 5 ";
$RATING[6] = " 6 ";
$RATING[7] = " 7 ";
$RATING[8] = " 8 ";
$RATING[9] = " 9 ";
$RATING[10]= "10 (best)";

// defines unacceptable file extensions for uploading
// this is to prevent execution of uploaded content on the server
define(NOUPLOADFILES, 'php|htm|html|asp|cgi');

// this property defines the page that people are sent to once they
// have logged in to the site. Normally, this will be the home page
// (index.php), but you could change this, for example, to let them
// set properties or choose to subscribe to a mailing list
define(LOGIN_REDIRECT,'/');

// supported languages
$LANGUAGES['dk'] = 'Danish';
$LANGUAGES['en'] = 'English';
$LANGUAGES['it'] = 'Italiano';
$LANGUAGES['xe'] = 'Rude English';

?>
