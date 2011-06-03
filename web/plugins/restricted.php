<?php
// restricted.php
// $Id: restricted.php,v 1.1 2003/06/25 02:33:52 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this plugin defines a macro {!restricted 'group'!}
// if the member viewing the page is not a member of 'group', then
// the page terminates with an error.

define(_TITLE_RESTRICTED,'Restricted Access');
define(_ERR_RESTRICTED,'I\'m terribly sorry, but you are not authorized to view the requested page.');

$restricted = new Plugin('restricted');
$restricted->set_macro('restricted',fcn_check_group);
$restricted->register();

// this function implements the macro
function fcn_check_group($arg) {
  global $PAGE,$DB;
  if (isadmin())
    return;
  $r = $DB->read(sprintf("SELECT group_id FROM groups WHERE group_name='%s'",
          addslashes($arg[0])));
  if (!$r)
    siteframe_abort('error in fcn_check_group: %s',$DB->error());
  list($gid) = $DB->fetch_array($r);
  if (!ismember($gid)) {
    $PAGE->set_property('page_title',_TITLE_RESTRICTED);
    $PAGE->set_property('error',_ERR_RESTRICTED);
    $PAGE->pparse('page');
    exit;
  }
}

?>
