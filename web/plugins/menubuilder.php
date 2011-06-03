<?php
// Menu builder plugin
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: menubuilder.php,v 1.5 2003/06/25 02:33:25 glen Exp $
//
// this plugin provides a couple of macros that allow users to
// easily define and display menus
//
// {!menu_item menuname prompt [link] [condition]!}
//  adds an item to menunanme; if [condition] is provided, then only adds
//  it if the condition is true
// {!menu menuname [sep]!}
//  displays menuname with optional separator between the items
// {!menu_sort menuname [sep]!}
//  displays menuname, sorted by the prompt strings
//
// PLEASE NOTE: do not remove this plugin.
// Although it is packaged as a removable plugin, it is commonly
// used in most standard templates; if you remove it, you may cause
// parts of your site to cease functioning.

$Menu = new Plugin('Menu');
$Menu->set_macro('menu_item','menu_add_item');
$Menu->set_macro('menu','menu_get_menu');
$Menu->set_macro('menu_sort','menu_sort_menu');
$Menu->register();

$MENUBUILDER = array();

// supporting functions
function menu_add_item($arr) {
  global $MENUBUILDER,$PAGE;
  $PAGE->set_property('__menu_add_item__',$arr[3]);
  $x = $PAGE->parse('__menu_add_item__');
  @eval(sprintf('$tmp=(%s);',$x));
  if (isset($arr[3]) && !$tmp)
    return;
  $MENUBUILDER[$arr[0]][$arr[1]] = $arr[2];
}

// format a menu
function menu_get_menu($arr) {
  global $MENUBUILDER;
  if (!count($MENUBUILDER[$arr[0]]))
    return '';
  $n = 0;
  foreach($MENUBUILDER[$arr[0]] as $prompt => $link) {
    $n++;
    $prompt = preg_replace('/^[0-9]*/','',$prompt);
    if ($n>1)
      $out .= $arr[1];
    if ($link=='')
      $out .= sprintf('<b>%s</b>',$prompt);
    else if (substr(strtolower($link),0,2) == '<a')
      $out .= sprintf($link,$prompt);
    else
      $out .= sprintf('<a href="%s">%s</a>',$link,$prompt);
  }
  return $out;
}

// sort then format a menu
function menu_sort_menu($arr) {
  global $MENUBUILDER;
  if (!is_array($MENUBUILDER[$arr[0]]))
    return sprintf("menu_sort ERROR: %s is not a defined menu",$arr[0]);
  ksort($MENUBUILDER[$arr[0]]);
  return menu_get_menu($arr);
}
