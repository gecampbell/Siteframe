<?php
// Class Notification
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: notification.php,v 1.10 2003/05/27 03:42:56 glen Exp $
//
// A Notification is a special email that is sent when triggered by
// a siteframe activity such as the creation of a new document,
// folder, user, etc.

class Notification extends Email {

    // constructor
    function Notification($type,$id) {
        global $DB,$PAGE,$PHP_SELF,$SITE_EMAIL,$SITE_NAME,
               $SITE_TEMPLATES,$TEMPLATES;
        switch($type) {
        case 'document':
            $prop  = 'user_notify_document';
            $temp  = $TEMPLATES[Notification][document];
            $prog  = 'document.php';
            $class = doctype($id);
            if ($class=='') {
              die("$PHP_SELF:/notification.php:No class specified, type=$type, id=$id\n");
            }
            $obj   = new $class($id);
            $title = $obj->get_property('doc_title');
            break;
        case 'folder':
            $prop  = 'user_notify_folder';
            $temp  = $TEMPLATES[Notification][folder];
            $prog  = 'folder.php';
            $class = foldertype($id);
            $obj   = new $class($id);
            $title = $obj->get_property('folder_name');
            break;
        case 'user':
            $prop  = 'user_notify_user';
            $temp  = $TEMPLATES[Notification][user];
            $prog  = 'user.php';
            $class = 'User';
            $obj   = new $class($id);
            $title = $obj->get_property('user_name');
            break;
        default:
            $this->add_error("Invalid Notification type specified");
        } 
        $r = $DB->read("SELECT * FROM users");
        while($arr = $DB->fetch_array($r)) {
            $u = new User(0,$arr);
            if ($u->get_property($prop)) {
                $this->add_address($u->get_property('user_email'),'bcc');
                $n++;
            }
        }
        if (!$n) { // no recipients
          return;
        }
        $PAGE->load_template('_msg_',$TEMPLATES[Notification][$type]);
        // set object properties
        $PAGE->set_array($obj->get_properties());
        $PAGE->set_property('notify_id',$id);
        $PAGE->set_property('notify_program',$prog);
        $PAGE->set_property('notify_type',$type);
        $PAGE->set_property('notify_title',$title);
        // set email properties
        $this->set_property('email_from',
            sprintf('%s <%s>',$SITE_NAME,$SITE_EMAIL));
        $this->set_property('email_subject',
            sprintf('[%s] Notification of new %s',$SITE_NAME,$type));
        $this->set_property('email_ascii',$PAGE->parse('_msg_'));
    }

}

?>
