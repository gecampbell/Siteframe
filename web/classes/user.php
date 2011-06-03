<?php
/* user.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: user.php,v 1.64 2005/03/14 20:41:49 glen Exp $
**
** Defines the 'User' class. This base class represents a system user, and
** contains all tools for managing the user information
*/

define(USER_FIELDS,'user_id|user_created|user_modified|user_status|user_lastname|user_firstname|user_nickname|user_email|user_passwd|user_cookie');

class User extends Siteframe {

    // User - constructor function
    //   if ID is !=0, then an attempt is made to retrieve an existing user
    //   otherwise a new, blank user object is created
    function User($id=0, $dbrow=0, $cookie='') {
        global $DB;
        parent::Siteframe(); // invoke parent constructor function
        if ($id || $dbrow || ($cookie!='')) {
            if ($dbrow) {
                $u = $dbrow;
            }
            else {
                if ($cookie!='')
                    $q = "SELECT * FROM users WHERE user_cookie='$cookie'";
                else
                    $q = "SELECT * FROM users WHERE user_id=$id";
                $r = $DB->read($q);
                $this->add_error($DB->error());
                $u = $DB->fetch_array($r);
            }
            $this->_properties['user_id'] = $u['user_id'];
            $this->set_property('user_created',  $u['user_created']);
            $this->set_property('user_modified', $u['user_modified']);
            $this->set_property('user_status',   $u['user_status']);
            $this->set_property('user_lastname', stripslashes($u['user_lastname']));
            $this->set_property('user_firstname',stripslashes($u['user_firstname']));
            $this->set_property('user_nickname', stripslashes($u['user_nickname']));
            $this->set_property('user_email',    stripslashes($u['user_email']));
            $this->set_property('user_passwd',   stripslashes($u['user_passwd']));
            $this->set_property('user_cookie',   stripslashes($u['user_cookie']));
            $this->set_xml_properties($u['user_props']);
        }
        else {
            $this->_properties['user_status'] = 0;
            $this->_properties['user_lastname'] = '';
        }
    }

    // add() - with existing information in the object, add a new user
    //   to the system
    function add() {
        global $DB,$REGISTER_MODEL,$DEFAULT_USER_SUBSCRIBE,$ENCRYPTION;

        $this->validate();

        if ($this->errcount)
            return;

        switch($REGISTER_MODEL) {
        case 'open':
            $this->set_property('register_confirm','done');
            break;
        case 'confirm':
            $this->set_property('register_confirm',md5(time()));
            break;
        }

        $this->set_property('user_subscribe',$DEFAULT_USER_SUBSCRIBE);
        $this->set_property('user_notify_comments',1);

        // if the insert fails, it's usually because it's got duplicates on
        // the cookie or something else. Repeating it will usually fix it.
        $retries = 10;
        do {
            $q = "INSERT INTO users (user_created,user_modified,user_status,user_lastname,".
                 "  user_firstname,user_nickname,user_email,user_passwd,user_cookie,user_props) VALUES ".
                 "(NOW(),NOW(),%d,'%s','%s','%s','%s',%s('%s'),%s(now()),'%s')";
            $DB->write(sprintf($q,
                    $this->get_property('user_status'),
                    addslashes($this->get_property('user_lastname')),
                    addslashes($this->get_property('user_firstname')),
                    addslashes($this->get_property('user_nickname')),
                    addslashes($this->get_property('user_email')),
                    $ENCRYPTION,
                    addslashes($this->get_property('user_passwd')),
                    $ENCRYPTION,
                    addslashes($this->get_xml_properties())));
        } while (--$retries && $DB->errno());

        if ($DB->affected_rows()!=1)
            $this->add_error(_ERR_NOINSERT,$DB->error());
        else {
            $this->_properties[user_id] = $DB->insert_id();
            logmsg("Added user id=%d, %s",$this->get_property(user_id),
                $this->get_property(user_name));
            //$this->notify("user");
            $this->trigger_event('user','add');
        }
    }

    // update(passwd) - with existing information in the object, update a user's
    //   information. NOTE: this function does NOT update the password, which
    //   is handled by update_passwd() - password required to update, however
    function update($passwd = '') {
        global $DB,$ENCRYPTION;
        $q = "UPDATE users SET user_status=%d,user_email='%s',".
             "   user_modified=NOW(),".
             "   user_lastname='%s',user_firstname='%s',".
             "   user_nickname='%s',user_props='%s' ".
             "WHERE user_id=%d ";
        if ($passwd!='')
            $q .= " AND user_passwd=${ENCRYPTION}('$passwd')";
        $DB->write(sprintf($q,
                $this->get_property('user_status'),
                addslashes($this->get_property('user_email')),
                addslashes($this->get_property('user_lastname')),
                addslashes($this->get_property('user_firstname')),
                addslashes($this->get_property('user_nickname')),
                addslashes($this->get_xml_properties()),
                $this->get_property('user_id')));
        if ($DB->error()) {
            $this->add_error(_ERR_NOUPDATE,$DB->error());
            logmsg("DEBUG:update, no error, affected-rows=%d",
                $DB->affected_rows());
        }
        else {
            logmsg("Updated user id=%d, %s",$this->get_property(user_id),
                $this->get_property(user_name));
            $this->trigger_event('user','update');
        }
    }

    // update_passwd(old,new) - if the old password matches, then it is
    //   updated with the new password
    function update_passwd($old,$new) {
        global $DB,$ENCRYPTION;
        $q = "UPDATE users SET user_modified=NOW(),".
             "  user_passwd=%s('%s') ".
             "WHERE user_id=%d AND user_passwd=%s('%s')";
        $DB->write(sprintf($q,
                $ENCRYPTION,
                $new,
                $this->get_property(user_id),
                $ENCRYPTION,
                $old));
        if ($DB->affected_rows()!=1)
            $this->add_error(_ERR_NOUPDATEPASSWD,$DB->error());
        else
            logmsg("Updated password for user id=%d, %s",
                $this->get_property(user_id),
                $this->get_property(user_name));
    }

    // delete() - deletes the specified user from the system
    function delete() {
        global $DB;
        $uid = $this->get_property(user_id);
        $this->trigger_event('user','delete');
        $DB->write("DELETE FROM users WHERE user_id=$uid");
        if ($DB->affected_rows()!=1)
            $this->add_error(_ERR_NODELETE,$DB->error());
        else
            logmsg("Deleted user id=%d, %s",
                $this->get_property(user_id),
                $this->get_property(user_name));
        $r = $DB->read(sprintf("SELECT doc_id,doc_type FROM docs WHERE doc_owner_id=%d",
                        $this->get_property(user_id)));
        while(list($docid,$doctype) = $DB->fetch_array($r)) {
            $doc = new $doctype($docid);
            $doc->delete();
        }
        @$DB->write("DELETE FROM comments WHERE owner_id=$uid");
        @$DB->write("DELETE FROM ratings WHERE user_id=$uid");
        @$DB->write("DELETE FROM poll_votes WHERE user_id=$uid");
        // delete all folders (and all docs in those folders)
        $r = $DB->read("SELECT folder_id FROM folders WHERE folder_owner_id=$uid");
        while(list($fid) = $DB->fetch_array($r))
        {
            $f = new Folder($fid);
            $f->delete();
        }
        // @$DB->write("DELETE FROM folders WHERE folder_owner_id=$uid");
        @$DB->write("DELETE FROM group_members WHERE group_user_id=$uid");
        @$DB->write("DELETE FROM editors WHERE editor_id=$uid");
        @$DB->write("DELETE FROM subscriptions WHERE subscr_owner_id=$uid");
        @$DB->write("DELETE FROM notifications WHERE note_user_id=$uid");
    }

    // get_property(name) - return dynamic properties as well
    function get_property($name) {
        global $COUNTRY_CODES;
        switch($name) {
        case 'user_name':
            $out = sprintf("%s %s",parent::get_property(user_firstname),
                                   parent::get_property(user_lastname));
            break;
        case 'user_nickname':
            if (!$this->get_property('user_id'))
              return '';
            $n = parent::get_property('user_nickname');
            if ($n != '') return $n;
            else
                $out = sprintf("%s %s",parent::get_property(user_firstname),
                                       parent::get_property(user_lastname));
            break;
        case 'user_country_name':
            $out = $COUNTRY_CODES[parent::get_property(user_country)];
            break;
        case 'user_last_login':
            $val = parent::get_property('user_last_login');
            if (trim($val) == '')
                $out = '-';
            else
                $out = $val;
            break;
        default:
            $out = parent::get_property($name);
        }
        return $out;
    }

    // get_properties()
    function get_properties() {
        $a = parent::get_properties();
        $a['user_name'] = $this->get_property('user_name');
        $a['user_nickname'] = $this->get_property('user_nickname');
        $a['user_country_name'] = $this->get_property('user_country_name');
        $a['user_last_login'] = $this->get_property('user_last_login');
        return $a;
    }

    // set_property(name,value) - sets the specified property with the
    //   requested value; performs error checking and sets errors if
    //   necessary
    function set_property($name,$value) {
    global $DB,$_FILES,$FILEPATH,$IMAGE_QUALITY,$SELFPORTRAIT_SIZE,$USE_GD18;
        switch($name) {
        case 'user_id':
            $uid = $this->get_property(user_id);
            if ($uid && ($value!=$uid))
                $this->add_error(_ERR_NOUSERIDCHANGE);
            else
                parent::set_property($name,$value);
            break;
        case 'user_status':
            parent::set_property($name,clean($value));
            break;
        case 'user_lastname':
            if (trim($value)=='')
                $this->add_error(_ERR_BADLASTNAME);
            else {
                parent::set_property($name,clean($value));
            }
            break;
        case 'user_firstname':
            parent::set_property($name,clean($value));
            break;
        case 'user_email':
            if (!preg_match('/.+@.+\..+/',$value))
                $this->add_error(_ERR_BADEMAIL);
            else
                parent::set_property($name,clean($value));
            break;
        case 'user_homepage':
            if ($value=='http://')
              $value = '';
            if (trim($value)=='')
                parent::set_property(user_homepage,clean($value));
            else if (!preg_match('/^http:\/\//',$value))
                $this->add_error(_ERR_BADURL,_PROMPT_USER_HOMEPAGE);
            else
                parent::set_property(user_homepage,clean($value));
            break;
        case 'user_description':
            parent::set_property(user_description,clean_html($value));
            break;
        case 'user_passwd':
            if (trim($value) == '')
                $this->add_error(_ERR_BADPASSWORD);
            else
                parent::set_property(user_passwd,clean($value));
            break;
        case 'user_props':
            if (($value=='')&&($this->get_property('user_selfportrait')!=''))
                @unlink($this->get_property('user_selfportrait'));
            parent::set_property($name,$value);
            break;
        case 'user_selfportrait':
            @unlink($this->get_property('user_selfportrait'));
            parent::set_property($name,
                $this->resize_image($value,
                    $this->get_property('user_selfportrait_mime_type'),
                    $SELFPORTRAIT_SIZE));
            if ($this->get_property('user_selfportrait')!=$value)
                @unlink($value);
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // input_form_values() - return input form values
    function input_form_values() {
        global $COUNTRY_CODES;
        $a = array(
            // mandatory properties
            array(name => "user_id",
                  type => "hidden",
                  value => $this->get_property(user_id)),
            array(name => "user_email",
                  prompt => _PROMPT_USER_EMAIL,
                  type => "text",
                  size => 250,
                  help => 'email',
                  focus => 1,
                  value => $this->get_property(user_email)),
            array(name => "user_firstname",
                  prompt => _PROMPT_USER_FIRSTNAME,
                  type => "text",
                  size => 250,
                  value => $this->get_property(user_firstname)),
            array(name => "user_lastname",
                  prompt => _PROMPT_USER_LASTNAME,
                  type => "text",
                  size => 250,
                  value => $this->get_property(user_lastname)),
            // optional properties - comment out those not needed
            array(name => "user_nickname",
                  prompt => _PROMPT_USER_NICKNAME,
                  type => "text",
                  size => 250,
                  help => 'nickname',
                  value => $this->get_property(user_nickname)),
            array(name => "user_description",
                  prompt => _PROMPT_USER_DESCRIPTION,
                  type => "textarea",
                  value => $this->get_property(user_description)),
            array(name => "user_homepage",
                  prompt => _PROMPT_USER_HOMEPAGE,
                  type => "text",
                  size => 250,
                  value => $this->get_property('user_id') ?
                           $this->get_property(user_homepage) :
                           'http://'),
            array(name => "user_selfportrait",
                  type => "file",
                  optional => 1,
                  prompt => _PROMPT_USER_SELFPORTRAIT,
                  disabled => !$this->get_property(user_id)),
            array(name => "user_country",
                  prompt => _PROMPT_USER_COUNTRY,
                  type => "select",
                  options => $COUNTRY_CODES,
                  value => (($this->get_property(user_country)!='') ?
                             $this->get_property(user_country) : 'US'))
        );
        return array_merge($a,$this->custom_properties());
    }

    // get_xml_properties()
    function get_xml_properties($no='') {
        if ($no=='')
          return parent::get_xml_properties(USER_FIELDS);
        else
          return parent::get_xml_properties($no);
    }

    // validate - perform validation
    function validate() {
      global $DB;
      // check for duplicate nicknames
      if ($this->get_property('user_nickname') != '') {
        $q = sprintf('SELECT COUNT(*) FROM users WHERE user_nickname=\'%s\' '.
                     'AND user_id!=%d',
                     addslashes($this->get_property('user_nickname')),
                     $this->get_property('user_id'));
        $r = $DB->read($q);
        list($num) = $DB->fetch_array($r);
        if ($num) {
            $this->add_error('Duplicate nicknames are not allowed');
        }
      }
    }

    // title - return a common name
    function title() {
      return $this->get_property('user_name');
    }

} // end clas User
?>
