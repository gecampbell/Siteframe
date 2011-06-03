<?php
// editmembers.php
// $Id: editmembers.php,v 1.4 2003/05/13 04:36:25 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this page is used to add and remove members from a group

require "siteframe.php";

$ptemplate = <<<ENDPTEMPLATE
<p><b>To remove members from the group:</b><br/>
Check the box in the "Delete" column beside the member(s) you want to remove,
and then press Submit. Please note that there is no confirmation of this
action; members will be immediately removed from the group if selected.</p>
<p><b>To add members to the group:</b><br/>
Type a name in the "New Member Name" box and press Submit.
If there is only one match for the user name, then the user is
added to the group. If the are multiple matches, all the possible
matches are displayed and you can select the ones you wish to
have added.<br/>

<p class="action"><a href="{site_path}/group.php?id={group_id}">Return to group listing</a></p>
<form method="post" action="$PHP_SELF" enctype="multipart/form-data">

<a name="newmembers"></a>
{newmemberlist}
<p class="prompt">New Member Name:</p>
<p class="field"><input type="text" maxlength="250" size="50" name="newmember"/></p>
<p class="doc">Enter the name (or nickname) of a new member here;
if there are multple matches for the name, you can select the one(s)
intended from the following page.</p>

<p class="prompt">Current Members:</p>
<table class="field">
<tr><th style="width:200px;">Member </th><th>Delete </th></tr>
{BEGIN:group group_id}
<tr class="{row_class}">
<td><a href="{site_path}/user.php?id={group_user_id}">{group_user_nickname}</a>
{!if '"{group_user_nickname}"!="{group_user_name}"' '({group_user_name})'!}
</td>
<td style="text-align:center;"><input name="remove[]" type="checkbox" value="{group_user_id}"/> </td>
</tr>
{END:group}
</table>
<p class="doc">Check the box under "Delete" beside the members you
wish to remove from the group.</p>

<input type="hidden" name="submitted" value="1"/>
<input type="hidden" name="group_id" value="{group_id}"/>
<input type="submit" name="submit" value="Submit"/>
<input type="reset" name="reset"/>
</form>

ENDPTEMPLATE;

$PAGE->set_property('page_title','Add/Edit Group Members');
$id = $_GET['id'] ? $_GET['id'] : $_POST['group_id'];
if (!$id) {
    $PAGE->set_property('error','You must supply a group ID');
    $PAGE->pparse('page');
    exit;
}
else {
    $gr = new Group($id);
    if ($gr->get_property('group_type')==GROUP_VIRTUAL) {
        $PAGE->set_property('error','You cannot add members to or remove members from a virtual group');
        $PAGE->pparse('page');
        exit;
    }
    $PAGE->set_property('page_title',
        sprintf('Members in "%s"',$gr->get_property('group_name')));
}
$PAGE->set_property('group_id',$id);
if ($_POST['submitted']) { // process changes
    //$PAGE->set_property('body','<pre>'.print_r($_POST,true).'</pre>');
    if (count($_POST['remove'])) {
        foreach($_POST['remove'] as $uid) {
            $u = new User($uid);
            $gr->remove($uid);
            $msg .= sprintf('Removed member %s<br/>'."\n",$u->get_property('user_name'));
        }
    }
    $search = $_POST['newmember'];
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
            $memlist .= sprintf(
                '<p class="field"><input type="checkbox" value="%d" name="add[]"/> %s %s</p>'."\n",
                $newid,
                $fullname,
                ($fullname == $nickname) ? '' : '('.$nickname.')'
                );
        }
        if ($found > 1) {
            $newmembers = '<p class="prompt">Select the matched member(s) below:</p>'
                          . "\n" . $memlist . "\n" .
                          '<p class="doc">Check the members to add to the group.</p>';
            $PAGE->set_property('newmemberlist',$newmembers);
        }
        else if ($found == 0) {
            $msg .= 'No members found that matched the given name<br/>'."\n";
        }
        else {
            $gr->join($newid);
            $msg .= sprintf('Added member %s<br>',$newuser->get_property('user_name'));
        }
    }
    if (count($_POST['add'])) {
        foreach($_POST['add'] as $newuid) {
            $u = new User($newuid);
            $ngr = new Group($id);
            $ngr->join($newuid);
            if ($ngr->errcount())
                $msg .= $ngr->get_errors();
            else
                $msg .= sprintf('Added member %s<br/>'."\n",$u->get_property('user_name'));
        }
    }
    $PAGE->set_property('error',$msg);
}
$PAGE->set_property('_group_',$ptemplate);
$PAGE->set_property('body',$PAGE->parse('_group_'));
$PAGE->pparse('page');

?>
