<?php
// setuser.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: setuser.php,v 1.4 2005/01/30 18:14:16 glen Exp $
//
// This script allows an administrator to "become" another 
// user (so that that user's settings, Preferences, can be
// modified). 

include "siteframe.php";

if (!isadmin()) 
    die("Not administrator");
else if (!$_GET['id'])
    die("No id specified");
else {
    setcookie(COOKIENAME,$_GET['id']);
    header("Location: user.php");
}

?>
