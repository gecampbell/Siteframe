<?php
// config.php
// $Id: config.php,v 1.13 2003/06/07 01:27:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
//
// This file contains general configuration information for your
// Siteframe installation. It should be stored somewhere OUTSIDE
// the reach of your web server so that it cannot be exploited.
// You should modify the "include" statement in web/siteframe.php
// to properly point to this file once you have installed it.
// Most of the default values here are created using the define()
// syntax of PHP, since they are static variables and will not change
// over time.

$LANGUAGE = 'en';   // default language setting

// cookie name
//   you probably won't ever need to change this
define(COOKIENAME,  'Siteframe30');

// Siteframe database parameters
// if you are using replicated databases, set DBHOST to the
// local slave (used for queries), and DBWRITE to the master
// (used for inserts, updates, and deletes)
define(DBHOST,      'localhost');   // host for queries
define(DBWRITE,     'localhost');        // host for writes
define(DBNAME,      'siteframe');
define(DBUSER,      'siteframe');
define(DBPASS,      'wizard');

?>
