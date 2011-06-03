<?php
// obj_props.php
// $Id: obj_props.php,v 1.9 2003/05/11 05:55:52 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// used to define local object properties (for extensions)

require "siteframe.php";

$PAGE->set_property('page_title','Select Object to Edit');

// retrieve list of classes
$q = "SELECT obj_id,obj_class FROM objs WHERE obj_active=1";
$r = $DB->read($q);
while(list($id,$cl) = $DB->fetch_array($r)) {
  $CLASSLIST[$id] = $cl;
}

// update existing classes

// retrieve information on all classes
$instr = '<p>Select the object type to maintain:</p>';
foreach($CLASSLIST as $id => $cl) {
  $list .= sprintf('<a href="obj_class.php?class=%s">%s</a><br/>',
            $id,
            $CLASSES[$cl]=='' ? $cl : $CLASSES[$cl]);
}
$PAGE->set_property('body',$instr.'<p>'.$list.'</p>');

$PAGE->set_property('body',"<p>&raquo; <a href=\"$PHP_SELF\">Return to top</a></p>",true);
$PAGE->pparse('page');

?>
