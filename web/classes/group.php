<?php
// group.php
// $Id: group.php,v 1.8 2003/06/21 23:40:12 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.

// defines the GROUP class
// a "group" is like a folder for users

// types of groups
define(GROUP_OPEN,0);
define(GROUP_CLOSED,1);
define(GROUP_VIRTUAL,2);

// these fields are columns
define(GROUP_FIELDS, 'group_id|group_type|group_owner_id|group_created|group_modified|group_name|group_body|group_props');

// these will be moved into language files
define(_DOC_GROUP_BODY,'A description of the group\'s purpose or function');
define(_DOC_GROUP_NAME,'The common name by which the group is referred');
define(_DOC_GROUP_SQL,'For a virtual group, supply an SQL statement that will return the users who are a member of that group. For other types of groups, this field is ignored.');
define(_DOC_GROUP_TYPE,'An Open group can be joined by any site member, while members in a Closed group must be added by a group editor');
define(_ERR_ALREADYMEMBER,'You are already a member of this group');
define(_ERR_BADGROUPNAME,'The name of the group cannot be blank');
define(_ERR_GROUPNOTAUTHDEL,'You are not authorized to delete this group');
define(_ERR_NOCHANGEVIRT,'You can\'t change a group from Open or Closed to Virtual and vice versa');
define(_ERR_NOJOINAUTH,'You are not authorized to join this group');
define(_ERR_NOJOINVIRTUAL,'You cannot join a virtual group');
define(_PROMPT_GROUP_BODY,'Description');
define(_PROMPT_GROUP_NAME,'Group name');
define(_PROMPT_GROUP_SQL,'SQL statement');
define(_PROMPT_GROUP_TYPE,'Type of group');
define(_TITLE_EDIT_GROUP,'Add/Edit Group');
define(_TITLE_GROUPS,'Groups');
$GROUP_TYPES[GROUP_OPEN] = 'Open';
$GROUP_TYPES[GROUP_CLOSED] = 'Closed';

// these will be moved to autoblocks
$AUTOBLOCK[all_groups] = 'SELECT * FROM groups INNER JOIN users ON (groups.group_owner_id=users.user_id) ORDER BY group_name';
$AUTOBLOCK[group] = 'SELECT * FROM groups INNER JOIN group_members ON (groups.group_id=group_members.group_id) INNER JOIN users ON (group_members.group_user_id=users.user_id) WHERE groups.group_id=%d ORDER BY user_lastname,user_firstname';
$AUTOBLOCK[recent_groups] = 'SELECT * FROM groups INNER JOIN users ON (groups.group_owner_id=users.user_id) ORDER BY group_created DESC LIMIT %d';
$AUTOBLOCK[user_groups] = 'SELECT * FROM groups INNER JOIN group_members ON (groups.group_id=group_members.group_id) INNER JOIN users ON (group_members.group_user_id=users.user_id) WHERE users.user_id=%d ORDER BY group_name';

class Group extends Siteframe {

    // Group - create a new group
    function Group($id=0, $dbrow='') {
        global $DB,$CURUSER;

        if ($id) {
            $q = sprintf('SELECT * FROM groups WHERE group_id=%d',$id);
            $r = $DB->read($q);
            $this->add_error($DB->error());
            $dbrow = $DB->fetch_array($r);
        }
        if ($dbrow!='') {
            foreach($dbrow as $name => $value) {
                if (($name!='group_props') && (!is_numeric($name)))
                    $this->set_property($name,$value);
            }
            $this->set_xml_properties($dbrow['group_props']);
        }
        else if ($CURUSER) {
            $this->set_property('group_owner_id',$CURUSER->get_property('user_id'));
        }
    }

    // add - add group to database
    function add() {
        global $DB;
        if ($this->errcount()) return;
        $q = sprintf('INSERT INTO groups (group_created,group_modified,group_owner_id,group_type,group_name,group_body,group_props) '.
                     'VALUES (NOW(),NOW(),%d,%d,\'%s\',\'%s\',\'%s\')',
            $this->get_property('group_owner_id'),
            $this->get_property('group_type'),
            addslashes($this->get_property('group_name')),
            addslashes($this->get_property('group_body')),
            addslashes($this->get_xml_properties()));
        $r = $DB->write($q);
        if ($DB->affected_rows()!=1) {
            $this->add_error(_ERR_NOINSERT,$DB->error());
        }
        else {
            $this->_properties[group_id] = $DB->insert_id();
            logmsg("Added group id=%d, %s",$this->get_property(group_id),
                $this->get_property(group_name));
            $this->trigger_event('group','add');
        }
    }

    // update - modify group
    function update() {
        global $DB;
        if ($this->errcount()) return;
        $q = sprintf(   'UPDATE groups '.
                        'SET group_type=%d,group_name=\'%s\', '.
                        '    group_modified=NOW(), '.
                        '    group_body=\'%s\',group_props=\'%s\' '.
                        'WHERE group_id=%d',
            $this->get_property('group_type'),
            addslashes($this->get_property('group_name')),
            addslashes($this->get_property('group_body')),
            addslashes($this->get_xml_properties()),
            $this->get_property('group_id'));
        $r = $DB->write($q);
        if ($DB->affected_rows()!=1) {
            $this->add_error(_ERR_NOUPDATE,$DB->error());
        }
        else {
            logmsg("Updated group id=%d, %s",$this->get_property(group_id),
                $this->get_property(group_name));
            $this->trigger_event('group','update');
        }
    }

    // delete - remove a group
    function delete($reason='') {
        global $DB;
        /*
        if (!iseditor($this->get_property('group_id'),'group')) {
            $this->add_error(_ERR_GROUPNOTAUTHDEL);
            return;
        }
        */
        $this->trigger_event('group','delete');
        @$DB->write(sprintf('DELETE FROM group_members WHERE group_id=%d',
                    $this->get_property('group_id')));
        @$DB->write(sprintf('DELETE FROM groups WHERE group_id=%d',
                    $this->get_property('group_id')));
        @$DB->write(sprintf('DELETE FROM permissions WHERE obj_type=\'group\' '.
                            'AND obj_id=%d',
                    $this->get_property('group_id')));
        $this->add_error($DB->error());
        logmsg("Deleted group \"%s\" (id=%d), reason=%s",
            $this->get_property('group_name'),
            $this->get_property('group_id'),
            $reason);
    }

    // set_property - set a property
    function set_property($name,$value) {
        switch($name) {
        case 'group_name':
            if (clean($value) == '')
                $this->add_error(_ERR_BADGROUPNAME);
            else
                parent::set_property($name,clean($value));
            break;
        case 'group_body':
            parent::set_property($name,clean_html($value));
            break;
        case 'group_type':
            switch($value) {
            case GROUP_VIRTUAL:
                if (!isadmin())
                    $this->add_error(_ERR_NOTADMIN);
                else if ($this->get_property('group_id') &&
                         isset($this->_properties['group_type']) &&
                         ($this->get_property('group_type')!=GROUP_VIRTUAL))
                    $this->add_error(_ERR_NOCHANGEVIRT);
                else
                    parent::set_property($name,$value+0);
                break;
            default:
                if ($this->get_property('group_type')==GROUP_VIRTUAL)
                    $this->add_error(_ERR_NOCHANGEVIRT);
                else
                    parent::set_property($name,$value+0);
            }
            break;
        case 'group_sql':
            if ($this->get_property('group_type')==GROUP_VIRTUAL) {
                parent::set_property($name,$value);
            }
            else if (($value!='') && (!$this->get_property('group_id'))) {
                parent::set_property('group_type',GROUP_VIRTUAL);
                parent::set_property($name,$value);
            }
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // get_xml_properties - return XML string
    function get_xml_properties() {
        $x = parent::get_xml_properties(GROUP_FIELDS);
        return $x;
    }

    // input_form_values - for prompts
    function input_form_values() {
        global $GROUP_TYPES;
        if (isadmin())
            $GROUP_TYPES[GROUP_VIRTUAL] = 'Virtual';
        $a = array(
            array(
                name => group_id,
                type => hidden,
                value => $this->get_property('group_id')
            ),
            array(
                name => group_name,
                type => text,
                size => 250,
                value => $this->get_property('group_name'),
                prompt => _PROMPT_GROUP_NAME,
                doc => _DOC_GROUP_NAME
            ),
            array(
                name => group_body,
                type => textarea,
                value => $this->get_property('group_body'),
                prompt => _PROMPT_GROUP_BODY,
                doc => _DOC_GROUP_BODY
            ),
            array(
                name => group_type,
                type => select,
                value => $this->get_property('group_type'),
                options => $GROUP_TYPES,
                prompt => _PROMPT_GROUP_TYPE,
                doc => _DOC_GROUP_TYPE
            )
        );
        if (isadmin())
            $a[] = array(
                name => group_sql,
                type => textarea,
                value => $this->get_property('group_sql'),
                prompt => _PROMPT_GROUP_SQL,
                doc => _DOC_GROUP_SQL
            );
        return array_merge($a,$this->custom_properties());
    }

    // join() - add a member to a group
    function join($uid) {
        global $DB;
        $ok = false;
        $gid = $this->get_property('group_id');

        // can't join virtual groups
        if ($this->get_property('group_type') == GROUP_VIRTUAL)
            $this->add_error(_ERR_NOJOINVIRTUAL);

        // can't join a non-existent group
        else if (!$gid)
            $this->add_error('This group does not exist!');

        // owners can always join their own group
        else if ($uid == $this->get_property('group_owner_id'))
            $ok = true;

        // members can't join again
        else if (ismember($gid,$uid))
            $this->add_error(_ERR_ALREADYMEMBER);

        // editors can join their grop
        else if (iseditor($gid,'group'))
            $ok = true;

        // anyone can join if the group is open
        else if ($this->get_property('group_type')==GROUP_OPEN)
            $ok = true;

        // if we got this far, we're probably ok
        if ($ok) {
            $q = sprintf('INSERT INTO group_members (group_id,group_user_id,date_added) '.
                         'VALUES (%d,%d,NOW())',
                    $this->get_property('group_id'),
                    $uid);
            $DB->write($q);
            $this->add_error($DB->error());
            $this->trigger_event('group','user_add',$uid);
        }
        else
            $this->add_error(_ERR_NOJOINAUTH);
    }

    // remove() - remove a member from a group
    function remove($uid,$reason='') {
        global $DB;
        $q = sprintf('DELETE FROM group_members WHERE group_id=%d AND group_user_id=%d',
                $this->get_property('group_id'),
                $uid);
        $DB->write($q);
        $this->add_error($DB->error());
        $this->trigger_event('group','user_remove',$uid);
        if ($reason!='')
            logmsg('User %d left group %s, reason=%s',
                $uid,
                $this->get_property('group_name'),
                $reason);
    }

    // get_members() - return an array of the user IDs of group members
    function get_members() {
        global $DB;
        $q = sprintf('SELECT group_user_id FROM group_members WHERE group_id=%d',
                $this->get_property('group_id'));
        $r = $DB->read($q);
        $a = array();
        while(list($uid) = $DB->fetch_array($r))
            $a[] = $uid;
        return $a;
    }

    // title() - a descriptive string
    function title() {
      return $this->get_property('group_name');
    }

} // end class Group

?>
