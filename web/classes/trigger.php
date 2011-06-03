<?php
// trigger.php - Trigger class (for monitoring activity)
// $Id: trigger.php,v 1.5 2003/06/20 05:40:03 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All Rights Reserved.
// see LICENSE.txt for complete details.
//
// INTRODUCTION
// A "trigger" is a system event that results in a notification or
// other action. Users can subscribe to triggers (we actually say
// we're "watching" events) and will receive notifications when such
// an event occurs. Plugins can "hook" into triggers and perform
// actions upon the receipt of the event.
//
// N.B. The term "Trigger" is used; I wanted to use "Event", but it's
// already the name of a document class.

// user events
$TRIGGERS['user']['add'] = 'User created';
$TRIGGERS['user']['delete'] = 'User deleted';
$TRIGGERS['user']['document_add'] = 'User creates new document';
$TRIGGERS['user']['folder_add'] = 'User creates new folder';
$TRIGGERS['user']['comment_add'] = 'User creates new comment';

// document events
$TRIGGERS['document']['add'] = 'Document created';
$TRIGGERS['document']['update'] = 'Document updated';
$TRIGGERS['document']['delete'] = 'Document deleted';
$TRIGGERS['comment']['add'] = 'Comment added to document';
$TRIGGERS['comment']['delete'] = 'Comment deleted from document';

// folder events
$TRIGGERS['folder']['add'] = 'Folder created';
$TRIGGERS['folder']['update'] = 'Folder updated';
$TRIGGERS['folder']['delete'] = 'Folder deleted';
$TRIGGERS['folder']['doc_add'] = 'Document added to folder';
$TRIGGERS['folder']['doc_remove'] = 'Document removed from folder';

// group events
$TRIGGERS['group']['add'] = 'Group created';
$TRIGGERS['group']['update'] = 'Group updated';
$TRIGGERS['group']['delete'] = 'Group deleted';
$TRIGGERS['group']['user_add'] = 'User added to group';
$TRIGGERS['group']['user_remove'] = 'User removed from group';

// messages
define(_ERR_NOTRIGGER,  'Unable to find trigger with ID [%d]');
define(_ERR_TRNOCALLBACK,'Callback function [%s] is not defined');
define(_ERR_TRNOCLASS,  'Invalid class [%s] for trigger');
define(_ERR_TRNOEVENT,  'Invalid event [%s] for trigger class [%s]');
define(_ERR_TRNOADD,    'Unable to add trigger, [%s]');
define(_ERR_TRNOUPD,    'Unable to update trigger, [%s]');
define(_ERR_TRNODEL,    'Unable to delete trigger, [%s]');

// the Trigger class
class Trigger extends Siteframe {

    // Trigger - create a new trigger
    function Trigger($id=0,$dbrow='',$class='',$event='') {
        if ($id) {
            $q = sprintf('SELECT * FROM triggers WHERE tr_id=%d',$id);
            $r = $DB->read($q);
            if ($return = $DB->error()) {
                $this->add_error(_ERR_NOTRIGGER,$id);
            }
            else
                $dbrow = $DB->fetch_array($r);
        }
        if ($dbrow!='') {
            foreach($dbrow as $name => $value)
                $this->set_property($name,$value);
        }
        else if (!$id) { // new, blank trigger
            $this->set_property('tr_class',strtolower($class));
            $this->set_property('tr_event',strtolower($event));
        }
    }

    // add() - adds a new trigger
    function add($callback='') {
        global $_TRIGGER_FCN;
        if ($callback=='') {
            $q = sprintf(
                    'INSERT INTO triggers '.
                    '(tr_user_id,tr_obj_id,tr_created,tr_event_class,tr_event_type) '.
                    'VALUES (%d,%d,NOW(),\'%s\',\'%s\')',
                    $this->get_property('tr_user_id'),
                    $this->get_property('tr_obj_id'),
                    $this->get_property('tr_class'),
                    $this->get_property('tr_event'));
            $r = $DB->write($q);
            if ($DB->errno())
                $this->add_error(_ERR_TRNOADD,$DB->error());
        }
        else { // add callback function
            $class = $this->get_property('tr_class');
            $event = $this->get_property('tr_event');
            if ($class=='') {
                $this->add_error(_ERRTRNOCLASS,$class);
            }
            else if ($event=='') {
                $this->add_error(_ERRTRNOEVENT,$class,$event);
            }
            else
                $_TRIGGER_FCN[$class][$event][] = $callback;
        }
    }

    // update() - modifies an existing trigger
    function update() {
    }

    // delete() - removes a trigger
    function delete() {
        $id = $this->get_property('tr_id');
        if ($id) {
            $DB->write(sprintf('DELETE FROM triggers WHERE tr_id=%d',$id));
        }
    }

    // set_property
    function set_property($name,$value) {
        global $TRIGGERS;
        switch($name) {
        case 'tr_class':
            if (isset($TRIGGERS[$value]))
                parent::set_property($name,$value);
            else
                $this->add_error(_ERR_TRNOCLASS,$value);
            break;
        case 'tr_event':
            if (isset($TRIGGERS[$class=$this->get_property('tr_class')][$value]))
                parent::set_property($name,$value);
            else
                $this->add_error(_ERR_TRNOEVENT,$value,$class);
            break;
        default:
            parent::set_property($name,$value);
        }
    }

}

?>
