<?php
// subscription.php
// $Id: subscription.php,v 1.15 2003/06/25 11:49:46 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this file defines the Subcription class, which is used to set watch points
// on documents, folders, or groups.

define(SUBSCR_FIELDS, 'subscr_id|subscr_owner_id|subscr_obj_type|subscr_obj_id|subscr_notify_mod|subscr_notify_add|subscr_notify_frequency|subscr_notify_type|subscr_created|subscr_modified');

// open values for enumerated fields - these should eventually be moved
// into the language modules
$SUBSCR_OBJ_TYPES['D'] = 'Document';
$SUBSCR_OBJ_TYPES['F'] = 'Folder';
$SUBSCR_OBJ_TYPES['G'] = 'Group';
$SUBSCR_OBJ_TYPES['U'] = 'User';

$SUBSCR_NOTIFY_FREQUENCIES['H'] = 'Hourly';
$SUBSCR_NOTIFY_FREQUENCIES['D'] = 'Daily';
$SUBSCR_NOTIFY_FREQUENCIES['W'] = 'Weekly';
$SUBSCR_NOTIFY_FREQUENCIES['I'] = 'Immediately';

$SUBSCR_NOTIFY_TYPES['E'] = 'E-mail';
$SUBSCR_NOTIFY_TYPES['O'] = 'Online message';

// subscription insert SQL statement
define(SUBSCR_INSERT,<<<ENDSUBSCRINSERT
INSERT INTO subscriptions
  (subscr_owner_id,subscr_obj_type,subscr_obj_id,subscr_notify_mod,
   subscr_notify_add,subscr_notify_frequency,subscr_notify_type,
   subscr_created,subscr_modified,subscr_props)
VALUES
  (%d,
   '%s',
   %d,
   '%s',
   '%s',
   '%s',
   '%s',
   NOW(),
   NOW(),
   '%s'
  )
ENDSUBSCRINSERT
);

// subscription update SQL statement
define(SUBSCR_UPDATE,<<<ENDSUBSCRUPDATE
UPDATE subscriptions SET
  subscr_owner_id=%d,
  subscr_obj_type='%s',
  subscr_obj_id=%d,
  subscr_notify_mod='%s',
  subscr_notify_add='%s',
  subscr_notify_frequency='%s',
  subscr_notify_type='%s',
  subscr_modified=NOW(),
  subscr_props='%s'
WHERE
  subscr_id=%d
ENDSUBSCRUPDATE
);

// subscription delete SQL statement
define(SUBSCR_DELETE,<<<ENDSUBSCRDELETE
DELETE FROM subscriptions
WHERE subscr_id=%d
ENDSUBSCRDELETE
);

// autoblock - doc subscriptions
$AUTOBLOCK['all_subscriptions'] = 'fcn_all_subscriptions';
function fcn_all_subscriptions($arg) {
  global $DB;
  $q = 'SELECT subscr_id FROM subscriptions '.
       'LEFT OUTER JOIN docs ON subscr_obj_id=doc_id '.
       'WHERE subscr_owner_id=%d AND subscr_obj_type=\'D\' '.
       'ORDER BY doc_title';
  $r = $DB->read(sprintf($q,$arg));
  if (!$r) siteframe_abort($DB->error());
  while(list($id) = $DB->fetch_array($r)) {
    $sub = new Subscription($id);
    $out[] = $sub->get_properties();
  }
  $q = 'SELECT subscr_id FROM subscriptions '.
       'LEFT OUTER JOIN folders ON subscr_obj_id=folder_id '.
       'WHERE subscr_owner_id=%d AND subscr_obj_type=\'F\' '.
       'ORDER BY folder_name';
  $r = $DB->read(sprintf($q,$arg));
  if (!$r) siteframe_abort($DB->error());
  while(list($id) = $DB->fetch_array($r)) {
    $sub = new Subscription($id);
    $out[] = $sub->get_properties();
  }
  $q = 'SELECT subscr_id FROM subscriptions '.
       'LEFT OUTER JOIN groups ON subscr_obj_id=group_id '.
       'WHERE subscr_owner_id=%d AND subscr_obj_type=\'G\' '.
       'ORDER BY group_name';
  $r = $DB->read(sprintf($q,$arg));
  if (!$r) siteframe_abort($DB->error());
  while(list($id) = $DB->fetch_array($r)) {
    $sub = new Subscription($id);
    $out[] = $sub->get_properties();
  }
  $q = 'SELECT subscr_id FROM subscriptions '.
       'LEFT OUTER JOIN users ON subscr_obj_id=user_id '.
       'WHERE subscr_owner_id=%d AND subscr_obj_type=\'U\' '.
       'ORDER BY user_lastname,user_firstname,user_id';
  $r = $DB->read(sprintf($q,$arg));
  if (!$r) siteframe_abort($DB->error());
  while(list($id) = $DB->fetch_array($r)) {
    $sub = new Subscription($id);
    $out[] = $sub->get_properties();
  }
  return $out;
}

class Subscription extends Siteframe {

  // Subscription - create a new one
  function Subscription($id=0) {
    global $DB;
    if ($id) {
      $q = sprintf('SELECT * FROM subscriptions WHERE subscr_id=%d',$id);
      $r = $DB->read($q);
      if (!$r) {
        $this->add_error(_ERR_NO_SUBSCR_ID,$id);
      }
      else {
        $row = $DB->fetch_array($r);
        foreach($row as $name => $value) {
          if (!is_numeric($name) && ($name!='subscr_props'))
            $this->set_property($name,$value);
        }
        $this->set_xml_properties($row['subscr_props']);
      }
    }
    else {
      $this->set_property('version',SITEFRAME_VERSION);
    }
  }

  // add() - add new subscription to table
  function add() {
    global $DB;
    $this->validate();
    if ($this->errcount()) return;
    $q = sprintf(SUBSCR_INSERT,
          $this->get_property('subscr_owner_id'),
          $this->get_property('subscr_obj_type'),
          $this->get_property('subscr_obj_id'),
          $this->get_property('subscr_notify_mod') ? 'Y' : 'N',
          $this->get_property('subscr_notify_add') ? 'Y' : 'N',
          $this->get_property('subscr_notify_frequency'),
          $this->get_property('subscr_notify_type'),
          addslashes($this->get_xml_properties()));
    $DB->write($q);
    $this->add_error($DB->error());
    $this->set_property('subscr_id',$DB->insert_id());
  }

  // update() - modify subscription
  function update() {
    global $DB;
    $this->validate();
    if ($this->errcount()) return;
    $q = sprintf(SUBSCR_UPDATE,
          $this->get_property('subscr_owner_id'),
          $this->get_property('subscr_obj_type'),
          $this->get_property('subscr_obj_id'),
          $this->get_property('subscr_notify_mod') ? 'Y' : 'N',
          $this->get_property('subscr_notify_add') ? 'Y' : 'N',
          $this->get_property('subscr_notify_frequency'),
          $this->get_property('subscr_notify_type'),
          addslashes($this->get_xml_properties()),
          $this->get_property('subscr_id'));
    $DB->write($q);
    $this->add_error($DB->error());
  }

  // delete() - remove subscription
  function delete() {
    global $DB;
    $this->validate();
    if ($this->errcount()) return;
    $q = sprintf(SUBSCR_DELETE,$this->get_property('subscr_id'));
    $DB->write($q);
    $this->add_error($DB->error());
    // delete associated notifications
    @$DB->write(
      sprintf(
        'DELETE FROM notifications WHERE note_subscr_id=%d',
        $this->get_property('subscr_id')
      )
    );
  }

  // set property
  function set_property($name,$value) {
    switch($name) {
    case 'subscr_notify_mod':
    case 'subscr_notify_add':
      if (($value=='Y')||($value==1))
        parent::set_property($name,1);
      else
        parent::set_property($name,0);
      break;
    case 'subscr_obj_type':
      $value = strtoupper($value);
      switch($value) {
      case 'D':
      case 'F':
      case 'G':
      case 'U':
        parent::set_property($name,$value);
        break;
      default:
        $this->add_error(_ERR_BAD_OBJ_TYPE,$value);
      }
      break;
    case 'subscr_notify_type':
      if ($value == 'O')
        parent::set_property('subscr_notify_frequency','I');
      parent::set_property($name,$value);
      break;
    default:
      parent::set_property($name,$value);
    }
  }

  // get property
  function get_property($name) {
    global
      $SUBSCR_OBJ_TYPES,
      $SUBSCR_NOTIFY_FREQUENCIES,
      $SUBSCR_NOTIFY_TYPES;
    $value = parent::get_property($name);
    switch($name) {
    case 'subscr_obj_type_display':
      return $SUBSCR_OBJ_TYPES[parent::get_property('subscr_obj_type')];
    case 'subscr_notify_frequency_display':
      return $SUBSCR_NOTIFY_FREQUENCIES[parent::get_property('subscr_notify_frequency')];
    case 'subscr_notify_type_display':
      return $SUBSCR_NOTIFY_TYPES[parent::get_property('subscr_notify_type')];
    default:
      return $value;
    }
  }

  // get_properties - all properties
  function get_properties() {
    $arr = parent::get_properties();
    $arr['subscr_obj_type_display'] =
      $this->get_property('subscr_obj_type_display');
    $arr['subscr_notify_frequency_display'] =
      $this->get_property('subscr_notify_frequency_display');
    $arr['subscr_notify_type_display'] =
      $this->get_property('subscr_notify_type_display');
    if ($this->get_property('subscr_obj_id')==0) {
      $obj = new Siteframe;
      switch($this->get_property('subscr_obj_type')) {
      case 'D':
        $title = 'All documents';
        break;
      case 'F':
        $title = 'All folders';
        break;
      case 'G':
        $title = 'All groups';
        break;
      case 'U':
        $title = 'All users';
        break;
      }
      $arr['subscr_obj_title'] = $title;
    }
    else {
      switch($this->get_property('subscr_obj_type')) {
      case 'D':
        $obj = new Document($this->get_property('subscr_obj_id'));
        break;
      case 'F':
        $obj = new Folder($this->get_property('subscr_obj_id'));
        break;
      case 'G':
        $obj = new Group($this->get_property('subscr_obj_id'));
        break;
      case 'U':
        $obj = new User($this->get_property('subscr_obj_id'));
        break;
      default:
        siteframe_abort('Weird error: no subscr_obj_type');
      }
      $arr['subscr_obj_title'] = $obj->title();
    }
    foreach($obj->get_properties() as $name => $value)
      $arr["subscr_$name"] = $value;
    return $arr;
  }

  // retrieve the XML properties for the subscription
  function get_xml_properties() {
    return parent::get_xml_properties(SUBSCR_FIELDS);
  }

  // build an array for input forms
  function input_form_values() {
    global
      $SUBSCR_NOTIFY_FREQUENCIES,
      $SUBSCR_NOTIFY_TYPES,
      $SUBSCRIPTION_IMMEDIATE,
      $NOTIFY_DEFAULT_TYPE;
    /*
    if (!$SUBSCRIPTION_IMMEDIATE)
      unset($SUBSCR_NOTIFY_FREQUENCIES['I']);
    */
    $form = array(
      array(
        name => 'subscr_id',
        type => 'hidden',
        value => $this->get_property('subscr_id')
      ),
      array(
        name => 'subscr_owner_id',
        type => 'hidden',
        value => $this->get_property('subscr_owner_id')
      ),
      array(
        name => 'subscr_obj_type',
        type => 'hidden',
        value => $this->get_property('subscr_obj_type')
      ),
      array(
        name => 'subscr_obj_id',
        type => 'hidden',
        value => $this->get_property('subscr_obj_id')
      ),
      array(
        name => 'subscr_notify_add',
        type => 'checkbox',
        rval => 1,
        value => $this->get_property('subscr_id') ?
                 $this->get_property('subscr_notify_add') :
                 1,
        prompt => _PROMPT_SUBSCR_NOTIFY_ADD,
        doc => _DOC_SUBSCR_NOTIFY_ADD
      ),
      array(
        name => 'subscr_notify_mod',
        type => 'checkbox',
        rval => 1,
        value => $this->get_property('subscr_notify_mod'),
        prompt => _PROMPT_SUBSCR_NOTIFY_MOD,
        doc => _DOC_SUBSCR_NOTIFY_MOD
      ),
      array(
        name => 'subscr_notify_frequency',
        type => 'select',
        options => $SUBSCR_NOTIFY_FREQUENCIES,
        value => $this->get_property('subscr_id') ?
                 $this->get_property('subscr_notify_frequency') :
                 'D',
        prompt => _PROMPT_SUBSCR_NOTIFY_FREQUENCY,
        doc => _DOC_SUBSCR_NOTIFY_FREQUENCY
      ),
      array(
        name => 'subscr_notify_type',
        type => 'select',
        options => $SUBSCR_NOTIFY_TYPES,
        value => $this->get_property('subscr_id') ?
                 $this->get_property('subscr_notify_type') :
                 $NOTIFY_DEFAULT_TYPE,
        prompt => _PROMPT_SUBSCR_NOTIFY_TYPE,
        doc => _DOC_SUBSCR_NOTIFY_TYPE
      ),
    );
    return $form;
  }

  // validate() - check subscription for okness
  function validate() {
    global $SUBSCR_ALL_OBJECTS;
    parent::validate();
    if ($this->get_property('subscr_owner_id')==0) {
      $this->add_error(_ERR_SUBSCR_NO_OWNER);
    }
    /* we'll allow 0 ID - this means "all of obj_type"
    if ($this->get_property('subscr_obj_id')==0) {
      $this->add_error(_ERR_SUBSCR_NO_OBJECT);
    }
    */
    if (!$this->get_property('subscr_notify_add') &&
        !$this->get_property('subscr_notify_mod')) {
      $this->add_error(_ERR_SUBSCR_NO_NOTIFY);
    }
    // do not allow immediate email notification on everything
    if (($this->get_property('subscr_notify_frequency')=='I') &&
        ($this->get_property('subscr_obj_id')==0) &&
        ($this->get_property('subscr_notify_type')=='E'))
      $this->add_error(_ERR_SUBSCR_NO_IMMEDIATE_ALL);
    // are "all documents" permitted?
    if (!$SUBSCR_ALL_OBJECTS && ($this->get_property('subscr_obj_id')==0))
      $this->add_error(_ERR_SUBSCR_ALL_NOTAUTH);
  }

} // end class Subscription

// notification insert SQL statement
define(NOTIFICATION_INSERT_SQL,<<<ENDNOTIFICATIONINSERTSQL
INSERT INTO notifications
(note_subscr_id,note_user_id,note_created,note_message,note_url,note_props)
VALUES
(%d, %d, NOW(), '%s', '%s', '%s')
ENDNOTIFICATIONINSERTSQL
);

// autoblock for active notifications
$AUTOBLOCK['active_notifications'] = 'fcn_active_notifications';
function fcn_active_notifications($arg) {
  global $DB;
  $q = sprintf('SELECT note_id FROM notifications '.
               'WHERE note_user_id=%d AND note_sent IS NULL '.
               'ORDER BY note_created',
               $arg);
  $r = $DB->read($q);
  if (!$r)
    siteframe_abort('unexpected error in active_notifications: %s',$DB->error());
  while(list($id) = $DB->fetch_array($r)) {
    $note = new subscrNotification($id);
    $out[] = $note->get_properties();
  }
  return $out;
}

// autoblock for all notifications
$AUTOBLOCK['all_notifications'] = 'fcn_all_notifications';
function fcn_all_notifications($arg) {
  global $DB;
  $q = sprintf('SELECT note_id FROM notifications '.
               'WHERE note_user_id=%d '.
               'ORDER BY note_created',
               $arg);
  $r = $DB->read($q);
  if (!$r)
    siteframe_abort('unexpected error in all_notifications: %s',$DB->error());
  while(list($id) = $DB->fetch_array($r)) {
    $note = new subscrNotification($id);
    $out[] = $note->get_properties();
  }
  return $out;
}

// class subscrNotification - handles notification messages
class subscrNotification extends Siteframe {

  // subscrNotification - create a new one
  function subscrNotification($id=0) {
    global $DB;
    if ($id) {
      $q = sprintf('SELECT * FROM notifications WHERE note_id=%d',$id);
      $r = $DB->read($q);
      $row = $DB->fetch_array($r);
      if (count($row)) {
        foreach($row as $n => $v)
          if (!is_numeric($n) && ($n!='note_props'))
            $this->set_property($n,stripslashes($v));
        $this->set_xml_properties($row['note_props']);
      }
    }
    else
      $this->set_property('version',SITEFRAME_VERSION);
  }

  // add notification
  function add() {
    global $DB,$SUBSCRIPTION_IMMEDIATE;
    if ($this->errcount())
      return;
    // to avoid duplicate notifications, we'll delete any existing ones
    @$DB->write(
      sprintf('DELETE FROM notifications WHERE note_sent IS NULL '.
              'AND note_subscr_id=%d AND note_message=\'%s\' '.
              'AND note_body-\'%s\'',
              $this->get_property('note_subscr_id'),
              addslashes($this->get_property('note_message')),
              addslashes($this->get_property('note_body')))
    );

    // go ahead and insert it
    $q = sprintf(NOTIFICATION_INSERT_SQL,
            $this->get_property('note_subscr_id'),
            $this->get_property('note_user_id'),
            addslashes($this->get_property('note_message')),
            addslashes($this->get_property('note_url')),
            addslashes($this->get_xml_properties()));
    $r = $DB->write($q);
    if (!$r)
      logmsg('error adding notification: %s',$DB->error());
    else
      $this->set_property('note_id',$DB->insert_id());
    if ($this->get_property('note_body')!='') {
      $q = sprintf('UPDATE notifications SET note_body=\'%s\' '.
                   'WHERE note_id=%d',
                   addslashes($this->get_property('note_body')),
                   $this->get_property('note_id'));
      $r = $DB->write($q);
      if (!$r)
        siteframe_abort('could note update note_body: %s',$DB->error());
    }
    if ($SUBSCRIPTION_IMMEDIATE) {
      $sub = new Subscription($this->get_property('note_subscr_id'));
      if (($sub->get_property('subscr_notify_type')=='E') &&
          ($sub->get_property('subscr_notify_frequency')=='I')) {
        // send immediate
        $this->notify_immediate($sub->get_property('subscr_owner_id'));
      }
    }
  }

  // delete
  function delete() {
    global $DB;
    $q = sprintf('DELETE FROM notifications WHERE note_id=%d',
          $this->get_property('note_id'));
    $r = $DB->write($q);
    if (!$r)
      siteframe_abort('problem deleting notification: %s',$DB->error());
  }

  // notify_immediate()
  function notify_immediate($uid) {
    global $PAGE,$SITE_NAME,$SITE_EMAIL,$TEMPLATES;
    $USER = new User($uid);
    $PAGE->set_array($this->get_properties());
    $PAGE->load_template('_immediate_',$TEMPLATES[Notify][immediate]);
    $message = new Email();
    $message->set_property('email_from',sprintf('%s <%s>',$SITE_NAME,$SITE_EMAIL));
    $message->set_property('email_subject',$SITE_NAME.' notification');
    $message->set_property('email_to',$USER->get_property('user_email'));
    $message->set_property('email_ascii',$PAGE->parse('_immediate_'));
    $message->send();
    $this->acknowledge();
  }

  // acknowledge - mark it as sent
  function acknowledge() {
    global $DB;
    $q = sprintf(
      'UPDATE notifications SET note_sent=NOW() WHERE note_id=%d',
      $this->get_property('note_id')
    );
    $r = $DB->write($q);
    if (!$r)
      siteframe_abort('unexpected error in subscrNotification::acknowledge(): %s',
        $DB->error());
  }

  // get all properties
  function get_properties() {
    $arr = parent::get_properties();
    $sub = new Subscription($arr['note_subscr_id']);
    foreach($sub->get_properties() as $name => $value)
      $arr["note_$name"] = $value;
    return $arr;
  }

  // get XML properties
  function get_xml_properties() {
    return parent::get_xml_properties('note_id|note_subscr_id|note_user_id|note_created|note_sent|note_message|note_url|note_body');
  }

} // end class subscrNotification

// primary setup tasks
$subscr = new Plugin('Subscriptions');
$subscr->set_global(
  'Subscriptions',
  'SUBSCRIPTION_ENABLE',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Enable subscriptions',
    doc => 'Check the box to allow subscriptions (event notifications) on your site'
  )
);
$subscr->set_global(
  'Subscriptions',
  'SUBSCRIPTION_IMMEDIATE',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Allow immediate notifications',
    doc => 'If checked, then users can request immediate notifications of events; this can degrade performance on the website if it is frequent.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'SUBSCR_ALL_OBJECTS',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Allow subscription to "all objects"',
    doc => 'If checked, then site members can subscribe to "all objects"; i.e., they will get notified if any object of a particular type is modified. Obviously, this can cause performance problems, so you should use this with caution.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'SUBSCR_NAV_NOTIFY',
  array(
    type => 'select',
    options => array(0 => 'None', 'top' => 'Top of menu', 'bottom' => 'Bottom of menu'),
    prompt => 'Add notification to navigation menu',
    doc => 'If checked, then <b>index.php</b> will automatically add a notification message to the NAVIGATION menu. You can choose to have no notifications, or to add it at the top or bottom of the menu.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'NOTIFY_HEIGHT',
  array(
    type => 'text',
    size => 4,
    prompt => 'Height of online notification window',
    doc => 'Enter the height (in pixels) of the online notification popup window.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'NOTIFY_WIDTH',
  array(
    type => 'text',
    size => 4,
    prompt => 'Width of online notification window',
    doc => 'Enter the width (in pixels) of the online notification popup window.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'NOTIFY_DEFAULT_TYPE',
  array(
    type => 'select',
    options => $SUBSCR_NOTIFY_TYPES,
    prompt => 'Default notification type',
    doc => 'Select the default type of notification; this can be changed by the user when they subscribe to something, but this provides a default value.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'SUBSCRIPTION_NOTIFY_TIME',
  array(
    type => 'select',
    options => array(0=>'midnight',1=>'01:00',2=>'02:00',3=>'03:00',4=>'04:00',
                     5=>'05:00',6=>'06:00',7=>'07:00',8=>'08:00',9=>'09:00',10=>'10:00',
                     11=>'11:00',12=>'12:00',13=>'13:00',14=>'14:00',
                     15=>'15:00',16=>'16:00',17=>'17:00',18=>'18:00',
                     19=>'19:00',20=>'20:00',21=>'21:00',22=>'22:00',23=>'23:00'),
    prompt => 'Notification Delivery Time',
    doc => 'Which hour of the day do you wish to have daily and weekly e-mail notifications delivered? The time is relative to the current time on the server, and is dependent upon the scheduling of the <b>hourly.php</b> script.'
  )
);
$subscr->set_global(
  'Subscriptions',
  'SUBSCRIPTION_NOTIFY_DAY',
  array(
    type => 'select',
    options => array(0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',
                     4=>'Thursday',5=>'Friday',6=>'Saturday'),
    prompt => 'Weekly Notification Delivery Day',
    doc => 'On which day of the week do you wish to have weekly notifications delivered? The day is relative to the current time on the server, and is dependent upon the scheduling of the <b>hourly.php</b> script.'
  )
);
$subscr->set_trigger('document','add','fcn_notify_mod_doc');
$subscr->set_trigger('document','update','fcn_notify_mod_doc');
$subscr->set_trigger('document','delete','fcn_notify_mod_doc');
$subscr->set_trigger('comment','add','fcn_notify_add_doc');
$subscr->set_trigger('folder','add','fcn_notify_mod_folder');
$subscr->set_trigger('folder','update','fcn_notify_mod_folder');
$subscr->set_trigger('folder','delete','fcn_notify_mod_folder');
$subscr->set_trigger('folder','doc_add','fcn_notify_add_folder');
$subscr->set_trigger('group','add','fcn_notify_mod_group');
$subscr->set_trigger('group','update','fcn_notify_mod_group');
$subscr->set_trigger('group','delete','fcn_notify_mod_group');
$subscr->set_trigger('group','user_add','fcn_notify_add_group_user');
$subscr->set_trigger('group','user_remove','fcn_notify_add_group_user');
$subscr->set_trigger('user','add','fcn_notify_mod_user');
$subscr->set_trigger('user','delete','fcn_notify_mod_user');
$subscr->register();

// trigger callback functions

// notify_mod when a document is updated
function fcn_notify_mod_doc(&$doc,$class,$event) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY1
SELECT *
FROM subscriptions
WHERE subscr_obj_type='D'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_mod='Y'
ENDQUERY1
;
  $r = $DB->read(sprintf($q,$doc->get_property('doc_id')));
  while($row = $DB->fetch_array($r)) {
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    if ($event=='add') $msg = _MSG_NOTIFY_MOD_DOC_ADD;
    else if ($event=='delete') $msg = _MSG_NOTIFY_MOD_DOC_DELETE;
    else $msg = _MSG_NOTIFY_MOD_DOC_UPDATE;
      $note->set_property('note_message',
        sprintf($msg,$doc->title()));
    if ($event!='delete')
      $note->set_property('note_url',
        sprintf('%s/document.php?id=%d',
          $SITE_URL,
          $doc->get_property('doc_id')));
    $note->add();
  }
}

// notify_add when a comment is added
function fcn_notify_add_doc(&$comment,$class,$event) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY2
SELECT *
FROM subscriptions
WHERE subscr_obj_type='D'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_add='Y'
ENDQUERY2
;
  $r = $DB->read(sprintf($q,$comment->get_property('comment_doc_id')));
  while($row = $DB->fetch_array($r)) {
    $doc = new Document($comment->get_property('comment_doc_id'));
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    $note->set_property('note_message',
      sprintf(_MSG_NOTIFY_ADD_DOC_COMMENT,
              $doc->title(),
              $comment->get_property('comment_subject')));
    $note->set_property('note_url',
      sprintf('%s/document.php?id=%d',
        $SITE_URL,
        $doc->get_property('doc_id')));
    $note->set_property('note_body',$comment->get_property('comment_body'),TRUE);
    $note->add();
  }
}

// notify_mod when a folder is updated
function fcn_notify_mod_folder(&$folder,$class,$event) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY3
SELECT *
FROM subscriptions
WHERE subscr_obj_type='F'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_mod='Y'
ENDQUERY3
;
  $r = $DB->read(sprintf($q,$folder->get_property('folder_id')));
  while($row = $DB->fetch_array($r)) {
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    if ($event=='add') $msg = _MSG_NOTIFY_MOD_FOLDER_ADD;
    else if ($event=='delete') $msg = _MSG_NOTIFY_MOD_FOLDER_DELETE;
    else $msg = _MSG_NOTIFY_MOD_FOLDER_UPDATE;
      $note->set_property('note_message',
        sprintf($msg,$folder->title()));
    if ($event!='delete')
      $note->set_property('note_url',
        sprintf('%s/folder.php?id=%d',
          $SITE_URL,
          $folder->get_property('folder_id')));
    $note->add();
  }
}

// notify_add when a doc is added to a folder
function fcn_notify_add_folder(&$doc,$class,$event) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY4
SELECT *
FROM subscriptions
WHERE subscr_obj_type='F'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_add='Y'
ENDQUERY4
;
  $r = $DB->read(sprintf($q,$doc->get_property('doc_folder_id')));
  while($row = $DB->fetch_array($r)) {
    $f = new Folder($doc->get_property('doc_folder_id'));
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    $note->set_property('note_message',
      sprintf(_MSG_NOTIFY_ADD_FOLDER_DOC,$doc->title(),$f->title()));
    $note->set_property('note_url',
      sprintf('%s/document.php?id=%d',
        $SITE_URL,
        $doc->get_property('doc_id')));
    $note->add();
  }
}

// notify_mod when a group is updated
function fcn_notify_mod_group(&$group,$class,$event) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY5
SELECT *
FROM subscriptions
WHERE subscr_obj_type='G'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_mod='Y'
ENDQUERY5
;
  $r = $DB->read(sprintf($q,$group->get_property('group_id')));
  while($row = $DB->fetch_array($r)) {
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    if ($event=='add') $msg = _MSG_NOTIFY_MOD_GROUP_ADD;
    else if ($event=='delete') $msg = _MSG_NOTIFY_MOD_GROUP_DELETE;
    else $msg = _MSG_NOTIFY_MOD_GROUP_UPDATE;
      $note->set_property('note_message',
        sprintf($msg,$group->title()));
    if ($event!='delete')
      $note->set_property('note_url',
        sprintf('%s/group.php?id=%d',
          $SITE_URL,
          $group->get_property('group_id')));
    $note->add();
  }
}

// notify_add when a user joins a group
function fcn_notify_add_group_user(&$group,$class,$event,$uid) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY4
SELECT *
FROM subscriptions
WHERE subscr_obj_type='G'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_add='Y'
ENDQUERY4
;
  $r = $DB->read(sprintf($q,$group->get_property('group_id')));
  while($row = $DB->fetch_array($r)) {
    $u = new User($uid);
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    if ($event=='user_add')
      $note->set_property('note_message',
        sprintf(_MSG_NOTIFY_ADD_GROUP_USER_JOIN,$u->title(),$group->title()));
    else
      $note->set_property('note_message',
        sprintf(_MSG_NOTIFY_ADD_GROUP_USER_REMOVE,$u->title(),$group->title()));
    $note->set_property('note_url',
      sprintf('%s/group.php?id=%d',
        $SITE_URL,
        $group->get_property('group_id')));
    $note->add();
  }
}

// notify_mod when a user is added or deleted
function fcn_notify_mod_user(&$user,$class,$event) {
global $DB,$SITE_URL;
$q = <<<ENDQUERY7
SELECT *
FROM subscriptions
WHERE subscr_obj_type='U'
  AND (subscr_obj_id=%d OR subscr_obj_id=0)
  AND subscr_notify_mod='Y'
ENDQUERY7
;
  $r = $DB->read(sprintf($q,$user->get_property('folder_id')));
  while($row = $DB->fetch_array($r)) {
    $note = new subscrNotification();
    $note->set_property('note_subscr_id',$row['subscr_id']);
    $note->set_property('note_user_id',$row['subscr_owner_id']);
    if ($event=='add') $msg = _MSG_NOTIFY_MOD_USER_ADD;
    else if ($event=='delete') $msg = _MSG_NOTIFY_MOD_USER_DELETE;
    else $msg = _MSG_NOTIFY_MOD_USER_UPDATE;
      $note->set_property('note_message',
        sprintf($msg,$user->title()));
    if ($event!='delete')
      $note->set_property('note_url',
        sprintf('%s/user.php?id=%d',
          $SITE_URL,
          $user->get_property('user_id')));
    $note->add();
  }
}

?>
