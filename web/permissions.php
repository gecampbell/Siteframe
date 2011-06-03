<?php
// permissions.php
// $Id: permissions.php,v 1.6 2003/06/09 13:15:12 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this file is used to maintain editors for documents, folders, and groups
// it is also used to designate submittors for folders

require "siteframe.php";

$PAGE->set_property('page_title','Maintain Permissions');

$class = ($_GET['class']!='') ? $_GET['class'] : $_POST['class'];
$id = ($_GET['id']) ? $_GET['id'] : $_POST['id'];

// make sure everything has been specified
if ($class == '') {
    $PAGE->set_property('error','You have no class');
    $PAGE->pparse('page');
    exit;
}
else if (!$id) {
    $PAGE->set_property('error','No ID specified');
    $PAGE->pparse('page');
    exit;
}

// validate class ID
switch($class) {
    case 'document':
        $dclass = doctype($id);
        if ($dclass=='') {
            $PAGE->set_property('error','No document with that ID');
            $PAGE->pparse('page');
            exit;
        }
        $obj = new $dclass($id);
        $obj_id = $obj->get_property('doc_id');
        $title = $obj->get_property('doc_title');
        break;
    case 'folder':
        $fclass = foldertype($id);
        if ($fclass=='') {
            $PAGE->set_property('error','No folder with that ID');
            $PAGE->pparse('page');
            exit;
        }
        $obj = new $fclass($id);
        $obj_id = $obj->get_property('folder_id');
        $title = $obj->get_property('folder_name');
        break;
    case 'group':
        $obj = new Group($id);
        $obj_id = $obj->get_property('group_id');
        if (!$obj_id) {
            $PAGE->set_property('error','No group with that ID');
            $PAGE->pparse('page');
            exit;
        }
        $title = $obj->get_property('group_name');
        break;
    default:
        $PAGE->set_property('error','Invalid value for class');
        $PAGE->pparse('page');
        exit;
}

// check to see if user is a valid editor
if (!iseditor($obj_id,$class)) {
    $PAGE->set_property('error','You are not authorized to edit that '.$class);
    $PAGE->pparse('page');
    exit;
}

// get a list of groups
$grouplist[0] = '[None]';
$r = $DB->read('SELECT group_id,group_name FROM groups ORDER BY group_name');
while(list($gid,$gname) = $DB->fetch_array($r)) {
    $grouplist[$gid] = $gname;
}

// now, set the proper page title
$PAGE->set_property('page_title',
    sprintf('"%s" permissions',$title));

// handle submissions from the top part of the form
if ($_POST['submitted'] == 1) {

    // new group
    if ($_POST['newgroup']) {
        $q = sprintf('INSERT INTO permissions '.
        '(obj_type,obj_id,editor_type,editor_id,can_edit,can_submit) VALUES '.
        '(\'%s\',%d,\'%s\',%d,%d,%d)',
            $class,
            $obj_id,
            'G',
            $_POST['newgroup'],
            ($class=='document' ? 1 : 0),
            ($class=='document' ? 0 : 1));
        $DB->write($q);
    }

    // multiple new users
    if (count($_POST['newuserlist'])) {
        foreach($_POST['newuserlist'] as $uid) {
            $q = sprintf('INSERT INTO permissions '.
            '(obj_type,obj_id,editor_type,editor_id,can_edit,can_submit) VALUES '.
            '(\'%s\',%d,\'%s\',%d,%d,%d)',
                $class,
                $obj_id,
                'U',
                $uid,
                ($class=='document' ? 1 : 0),
                ($class=='document' ? 0 : 1));
            $DB->write($q);
        }
    }
}

// handle submissions from the bottom
if ($_POST['submitted'] == 2) {

    // process groups
    if (is_array($_POST['groups']))
    foreach($_POST['groups'] as $ovoid) {
        if (is_array($_POST['gdeleteit']) && in_array($ovoid,$_POST['gdeleteit'])) {
            // delete row
            $q = sprintf(
                'DELETE FROM permissions WHERE obj_type=\'%s\' AND '.
                'obj_id=%d AND editor_type=\'G\' AND editor_id=%d',
                $class,
                $obj_id,
                $ovoid);
            $DB->write($q);
        }
        else {
            if (is_array($_POST['gcanedit']) && in_array($ovoid,$_POST['gcanedit']))
                $canedit = 1;
            else
                $canedit = 0;

            if (is_array($_POST['gcansubmit']) && in_array($ovoid,$_POST['gcansubmit']))
                $cansubmit = 1;
            else
                $cansubmit = 0;

            $q = sprintf(
                'UPDATE permissions SET can_edit=%d,can_submit=%d '.
                'WHERE obj_type=\'%s\' AND obj_id=%d AND editor_type=\'G\' '.
                ' AND editor_id=%d',
                $canedit,
                $cansubmit,
                $class,
                $obj_id,
                $ovoid);
            $DB->write($q);
        }
    }

    // process users
    if (is_array($_POST['users']))
    foreach($_POST['users'] as $ovoid) {
        if (is_array($_POST['udeleteit']) && in_array($ovoid,$_POST['udeleteit'])) {
            // delete row
            $q = sprintf(
                'DELETE FROM permissions WHERE obj_type=\'%s\' AND '.
                'obj_id=%d AND editor_type=\'U\' AND editor_id=%d',
                $class,
                $obj_id,
                $ovoid);
            $DB->write($q);
        }
        else {
            if (is_array($_POST['ucanedit']) && in_array($ovoid,$_POST['ucanedit']))
                $canedit = 1;
            else
                $canedit = 0;

            if (is_array($_POST['ucansubmit']) && in_array($ovoid,$_POST['ucansubmit']))
                $cansubmit = 1;
            else
                $cansubmit = 0;

            $q = sprintf(
                'UPDATE permissions SET can_edit=%d,can_submit=%d '.
                'WHERE obj_type=\'%s\' AND obj_id=%d AND editor_type=\'U\' '.
                ' AND editor_id=%d',
                $canedit,
                $cansubmit,
                $class,
                $obj_id,
                $ovoid);
            $DB->write($q);
        }
    }

}

// some instructions
$instructions = <<<ENDINSTRUCTIONS
<p><b>Add new users or groups</b><br/>
Use the top part of this page to add new users or groups
to the permissions list for this object. By default, new
groups will have <i>submit</i> access for folders and groups, and
<i>editor</i> access for documents. You can modify these
settings from the list at the bottom of the page.</p>
ENDINSTRUCTIONS;
$instructions .= sprintf(
                  '<p class="action">Back to "<a href="%s/%s.php?id=%d">%s</a>"</p>',
                  $SITE_PATH,
                  $class,
                  $obj_id,
                  $title);

// the form has two parts; the top is to add NEW users/groups to the list
// the bottom has the list of existing users/groups

$form = array(
    array(
        name => 'class',
        type => 'hidden',
        value => $class
    ),
    array(
        name => 'id',
        type => 'hidden',
        value => $id
    ),
    array(
        name => 'newgroup',
        type => 'select',
        options => $grouplist,
        prompt => 'Add new group',
        doc => 'Select a group to add to the permissions list'
    ),
);
if ($_POST['newusername']!='') { // build a list of users
    $search = $_POST['newusername'];
    if ($search != '') {
        $q = sprintf('SELECT * FROM users WHERE '.
                     'MATCH(user_firstname,user_lastname,user_nickname,user_props) '.
                     'AGAINST(\'%s\')',addslashes($search));
        $r = $DB->read($q);
        $found = 0;
        while ($dbrow = $DB->fetch_array($r)) {
            ++$found;
            $newuser = new User(0,$dbrow);
            $newid = $newuser->get_property('user_id');
            $fullname = sprintf('%s %s',
                $newuser->get_property('user_firstname'),
                $newuser->get_property('user_lastname'));
            $nickname = $newuser->get_property('user_nickname');
            $userlist[$newid] = sprintf(
                '%s %s',
                $nickname,
                ($fullname == $nickname) ? '' : '('.$fullname.')');
            /*
            $memlist .= sprintf(
                '<p class="field"><input type="checkbox" value="%d" name="add[]"/> %s %s</p>'."\n",
                $newid,
                $fullname,
                ($fullname == $nickname) ? '' : '('.$nickname.')'
                );
            */
        }
        if ($found > 1) {
            /*
            $newmembers = '<p class="prompt">Select the matched member(s) below:</p>'
                          . "\n" . $memlist . "\n" .
                          '<p class="doc">Check the members to add to the group.</p>';
            $PAGE->set_property('newmemberlist',$newmembers);
            */
            $form[] = array(
                name => 'newuserlist',
                type => 'checkboxarray',
                options => $userlist,
                prompt => 'Select user(s)',
                doc => 'There were multiple matches for the name found; check the box '.
                       'next to the desired member(s).'
            );
        }
        else if ($found == 0) {
            $msg .= 'No members found that matched the given name<br/>'."\n";
        }
        else {
            // add the member to the list
            /*
            $gr->join($newid);
            */
            $q = sprintf('INSERT INTO permissions '.
            '(obj_type,obj_id,editor_type,editor_id,can_edit,can_submit) VALUES '.
            '(\'%s\',%d,\'%s\',%d,%d,%d)',
                $class,
                $obj_id,
                'U',
                $newid,
                ($class=='document' ? 1 : 0),
                ($class=='document' ? 0 : 1));
            $DB->write($q);
        }
    }
}

$PAGE->set_property('error',$msg);

$form[] = array(
    name => 'newusername',
    type => 'text',
    size => 250,
    prompt => 'Add new user',
    doc => 'Type a member\'s name in the box; if there are multiple '.
           'matches, you will be able to select from them on the '.
           'next page'
);

// build the list of existing users/groups
$AUTOBLOCK['group_permissions'] =
    sprintf(
        'SELECT * FROM permissions,groups '.
        'WHERE permissions.editor_id=groups.group_id '.
        'AND   obj_type=\'%s\' AND '.
        '      obj_id=%d AND '.
        '      editor_type=\'G\' '.
        'ORDER BY group_name',
        $class,
        $obj_id);
$AUTOBLOCK['user_permissions'] =
    sprintf(
        'SELECT * FROM permissions,users '.
        'WHERE permissions.editor_id=users.user_id '.
        'AND   obj_type=\'%s\' AND '.
        '      obj_id=%d AND '.
        '      editor_type=\'U\' '.
        'ORDER BY user_lastname,user_firstname',
        $class,
        $obj_id);
$permform = <<<ENDPERMFORM
<form method="post" action="{site_path}/permissions.php" enctype="multipart/form-data">
<p class="prompt">Existing Permissions</p>
<table>
{BEGIN:group_permissions}
{!if '{row_number}==1'
'<tr><th>Group </th><th>Edit </th><th>Submit </th><th>Delete? </th></tr>
'!}
<tr class="{row_class}">
 <td><a href="{site_path}/group.php?id={group_id}">{group_name}</a>
 <input type="hidden" name="groups[]" value="{group_id}"/>
 </td>
 <td width="10%" align="center"><input type="checkbox" name="gcanedit[]"   value="{group_id}" {!if '{can_edit}' 'checked="checked"'!}/></td>
 <td width="10%" align="center"><input type="checkbox" name="gcansubmit[]" value="{group_id}" {!if '{can_submit}' 'checked="checked"'!}/></td>
 <td width="10%" align="center"><input type="checkbox" name="gdeleteit[]"  value="{group_id}"/>
</tr>
{END:group_permissions}
{BEGIN:user_permissions}
{!if '{row_number}==1'
'<tr><th>User </th><th>Edit </th><th>Submit </th><th>Delete? </th></tr>'!}
<tr class="{row_class}">
 <td><a href="{site_path}/user.php?id={group_user_id}">{group_user_nickname}</a>
     {!if '"{group_user_name}"!="{group_user_nickname}"' '({group_user_name})'!}
 <input type="hidden" name="users[]" value="{group_user_id}"/>
 </td>
 <td width="10%" align="center"><input type="checkbox" name="ucanedit[]"   value="{group_user_id}" {!if '{can_edit}' 'checked="checked"'!}/></td>
 <td width="10%" align="center"><input type="checkbox" name="ucansubmit[]" value="{group_user_id}" {!if '{can_submit}' 'checked="checked"'!}/></td>
 <td width="10%" align="center"><input type="checkbox" name="udeleteit[]"  value="{group_user_id}"/>
</tr>
{END:user_permissions}
</table>
<p class="doc">Use the check boxes to modify the permissions as necessary.
The "Delete" column will remove the user or group from the permissions
list.</p>
<input type="hidden" name="class" value="$class"/>
<input type="hidden" name="id" value="$id"/>
<input type="hidden" name="submitted" value="2"/>
<input type="submit" value="Submit"/>
<input type="reset"/>
</form>
ENDPERMFORM;


// build the input form
$PAGE->set_property('form_instructions',$instructions);
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->input_form('body',$form);
$PAGE->set_property('_perms_',$permform);
$PAGE->set_property('body',$PAGE->parse('_perms_'),true);

$PAGE->pparse('page');

?>
