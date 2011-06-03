<?php
// portal.php
// $Id: portal.php,v 1.1 2003/05/27 03:42:55 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.

require "siteframe.php";

if (!$CURUSER) {
  $PAGE->set_property('page_title','Unauthorized');
  $PAGE->set_property('error','You must be logged in to use this page');
  $PAGE->pparse('page');
  exit;
}

$CREATECATEGORY = <<<ENDCREATECATEGORY
create table portal_categories (
    category_id     int             not null auto_increment,
    user_id         int             not null,
    category        varchar(250)    not null,
    primary key (category_id),
    unique index (user_id,category)
);
ENDCREATECATEGORY;

$CREATELINKS = <<<ENDCREATELINKS
create table portal_links (
    link_id         int             not null auto_increment,
    user_id         int             not null,
    category_id     int             not null,
    link_count      bigint          not null,
    link_last_visit datetime,
    name            varchar(250)    not null,
    url             varchar(250)    not null,
    notes           text,
    primary key (link_id),
    unique index (user_id,category_id,url)
);
ENDCREATELINKS;

$AUTOBLOCK[portal_categories] =
  sprintf('SELECT * FROM portal_categories WHERE user_id=%d ORDER BY category',
    $CURUSER->get_property('user_id'));
$AUTOBLOCK[portal_category_links] =
  sprintf('SELECT * FROM portal_links WHERE user_id=%d AND category_id=%%d ORDER BY name',
    $CURUSER->get_property('user_id'));

// first time, install the tables
if (!$PORTAL_INSTALLED) {
  $DB->write($CREATECATEGORY);
  $PAGE->set_property('error',$DB->error(),TRUE);
  $DB->write($CREATELINKS);
  $PAGE->set_property('error',$DB->error(),TRUE);
  set_global('PORTAL_INSTALLED',1);
}

$category_form = array(
  array(
    name => 'form_type',
    type => 'hidden',
    value => 'category'
  ),
  array(
    name => "category",
    type => "text",
    size => "50",
    prompt => "Category",
    doc => "Enter the name of a new category here"
  ),
);
$PAGE->set_property('form_instructions',
  'Use this form to add a new category to your portal');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('category_form',$category_form);

$r = $DB->read(
  sprintf('SELECT category_id,category FROM portal_categories WHERE user_id=%d ORDER BY category',$CURUSER->get_property('user_id')));
while(list($id,$name) = $DB->fetch_array($r))
  $categorylist[$id] = $name;
$link_form = array(
  array(
    name => 'form_type',
    type => 'hidden',
    value => 'link'
  ),
  array(
    name => 'category_id',
    type => 'select',
    options => $categorylist,
    prompt => 'Category',
    doc => 'Select a category for the link'
  ),
  array(
    name => 'url',
    type => 'text',
    size => 250,
    prompt => 'Link URL',
    doc => 'A valid URL'
  ),
  array(
    name => 'name',
    type => 'text',
    size => 250,
    prompt => 'Title',
    doc => 'A name for the link'
  ),
);
$PAGE->set_property('form_instructions',
  'Use this form to add a new link to your portal');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('link_form',$link_form);

$PAGE->set_property('page_title',$SITE_NAME.' Portal');
$PAGE->load_template('portal','portal');
$PAGE->set_property('body',$PAGE->parse('portal'));
$PAGE->pparse('page');

?>
