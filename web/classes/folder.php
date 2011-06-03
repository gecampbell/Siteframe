<?php
// folder.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: folder.php,v 1.49 2005/03/08 04:35:27 glen Exp $

define(FOLDER_FIELDS,'folder_id|folder_created|folder_modified|folder_owner_id|folder_public|folder_parent_id|folder_children|folder_type|folder_name|folder_body');

class Folder extends Siteframe {

    // Folder(id) - constructor
    function Folder($id=0, $dbrow=0) {
        global $DB,$CURUSER,$ALLOW_RATINGS,$ALLOW_COMMENTS;
        if ($id||$dbrow) {
            if ($dbrow) {
                $f = $dbrow;
            }
            else {
                $q = "SELECT * FROM folders WHERE folder_id=$id";
                $r = $DB->read($q);
                $this->add_error($DB->error());
                $f = $DB->fetch_array($r);
            }
            $this->set_property('folder_id',        $f['folder_id']);
            $this->set_property('folder_created',   $f['created']);
            $this->set_property('folder_modified',  $f['modified']);
            $this->set_property('folder_owner_id',  $f['folder_owner_id']);
            $this->set_property('folder_parent_id', $f['folder_parent_id']);
            $this->set_property('folder_public',    $f['folder_public']);
            $this->set_property('folder_children',  $f['folder_children']);
            $this->set_property('folder_type',      $f['folder_type']);
            $this->set_property('folder_name',      $f['folder_name']);
            $this->set_property('folder_body',      $f['folder_body']);
            $this->set_xml_properties($f['folder_props']);
        }
        else {
            //parent::Document();
            parent::Siteframe();
            $this->set_property(folder_owner_id,$CURUSER->get_property(user_id));
            $this->set_property(folder_type,'Folder');
            $this->set_property(allow_ratings,$ALLOW_RATINGS);
            $this->set_property(allow_comments,$ALLOW_COMMENTS);
        }
    }

    // add() - add a new folder
    function add() {
        global $DB;
        if ($this->errcount())
            return;
        $this->validate();
        if ($this->errcount())
            return;
        // 2002/10/07 - added DELAYED
        // 2002/10/07 - removed it
        $q = "INSERT INTO folders ".
             "(created,modified,folder_owner_id,folder_parent_id,folder_public,".
             "  folder_children,folder_type,folder_name,folder_body,folder_props) ".
             "VALUES ".
             "  (NOW(),NOW(),%d,%d,%d,%d,'%s','%s','%s','%s')";
        $DB->write(sprintf($q,
                    $this->get_property(folder_owner_id),
                    $this->get_property(folder_parent_id),
                    $this->get_property(folder_public),
                    $this->get_property(folder_children),
                    $this->get_property(folder_type),
                    addslashes($this->get_property(folder_name)),
                    addslashes($this->get_property(folder_body)),
                    addslashes($this->get_xml_properties())));
        $this->add_error($DB->error());
        if (!$this->errcount()) {
            $this->set_property(folder_id,$DB->insert_id());
            logmsg('Added folder "%s"',$this->get_property(folder_name));
            // $this->notify("folder");
            $this->trigger_event('folder','add');
        }
        else {
            logmsg('Error adding folder "%s," error=%s',$this->get_property(folder_name),
                $this->get_errors());
        }
    }

    // update() - update folder information
    function update() {
        global $DB;
        $this->validate();
        if ($this->errcount())
          return;
        $q = "UPDATE folders SET modified=NOW(),folder_public=%d,".
             "  folder_parent_id=%d,".
             "  folder_children=%d,".
             "  folder_type='%s',".
             "  folder_name='%s',".
             "  folder_body='%s',folder_props='%s' ".
             "WHERE folder_id=%d";
        $DB->write(sprintf($q,
                    $this->get_property(folder_public),
                    $this->get_property(folder_parent_id),
                    $this->get_property(folder_children),
                    $this->get_property(folder_type),
                    addslashes($this->get_property(folder_name)),
                    addslashes($this->get_property(folder_body)),
                    addslashes($this->get_xml_properties()),
                    $this->get_property(folder_id)));
        $this->add_error($DB->error());
        if (!$this->errcount()) {
            logmsg('Updated folder %d, "%s"',$this->get_property(folder_id),
                $this->get_property(folder_name));
            $this->trigger_event('folder','update');
        }
        else {
            logmsg('Failed to update folder %d, "%s", error=%s',
                $this->get_property(folder_id),
                $this->get_property(folder_name),
                $this->get_errors());
        }
    }

    // delete() - delete a folder and all documents in it
    function delete() {
        global $DB;
        $fid=$this->get_property(folder_id);
        // first, delete all subfolders
        if ($fid) {
            $q = sprintf("SELECT folder_id FROM folders WHERE folder_parent_id=%d",$fid);
            $r = $DB->read($q);
            while(list($ffid) = $DB->fetch_array($r)) {
                $f = new Folder($ffid);
                $f->delete();
            }
        }
        // next, delete the folder itself, and all its docs
        $r = $DB->read(sprintf("SELECT doc_id FROM docs WHERE doc_folder_id=%d",$fid));
        while(list($id) = $DB->fetch_array($r)) {
            $class = doctype($id);
            $doc = new $class($id);
            $doc->delete();
        }
        $DB->write(sprintf("DELETE FROM folders WHERE folder_id=%d",$fid));
        $this->add_error($DB->error());
        if (!$this->errcount()) {
            logmsg('Deleted folder %d, "%s"',$this->get_property(folder_id),
                $this->get_property(folder_name));
            $this->trigger_event('folder','delete');
        }
        else {
            logmsg('Error deleting folder %d, "%s," error=%s',
                $this->get_property(folder_id),
                $this->get_property(folder_name),
                $this->get_errors());
        }
    }

    // set_property(name,val) - perform error-checking and cleanup
    function set_property($name,$value) {
        global $TOP_FOLDERS_ADMIN_ONLY;
        switch($name) {
        case 'folder_body':
            parent::set_property($name,clean_html($value));
            break;
        case 'folder_name':
            if (clean($value) == '')
                $this->add_error(_ERR_BADNAME);
            else {
                parent::set_property($name,clean($value));
            }
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // get_property
    function get_property($name) {
        global $PUBLIC_FOLDER_PREFIX, $PUBLIC_FOLDER_SUFFIX;
        switch($name) {
        case 'folder_name_display':
            if ($this->get_property(folder_public))
                return $PUBLIC_FOLDER_PREFIX .
                       parent::get_property('folder_name') .
                       $PUBLIC_FOLDER_SUFFIX;
            else
                return parent::get_property('folder_name');
            break;
        default:
            return parent::get_property($name);
        }
    }

    // get_xml_properties() - set properties from doc_props
    function get_xml_properties() {
        return parent::get_xml_properties(FOLDER_FIELDS);
    }

    // get_properties() - add some new properties
    function get_properties() {
        global $DB,$CLASSES,$PUBLIC_FOLDER_PREFIX, $PUBLIC_FOLDER_SUFFIX;
        $a = parent::get_properties();
        $u = new User($this->get_property(folder_owner_id));
        foreach ($u->get_properties() as $name => $value) {
            $a["folder_$name"] = $value;
        }
        $lim = $this->get_property(folder_limit_type);
        $a[folder_limit_type_display] = $CLASSES[$lim];
        $a['folder_name_display'] = $this->get_property('folder_name_display');
        return $a;
    }

    // input_form_values() - return input form array
    function input_form_values() {
        global $DB,$CURUSER,$CLASSES;
        $types = $CLASSES;
        $types[''] = " No Restriction";
        $types['none'] = " Subfolders only, no documents";
        asort($types);
        // get personal folders, then parent folders
        $parents[0] = "Not inside another folder";
        $thisid = $this->get_property(folder_id);
        if ($CURUSER->get_property(user_id)) {
            $q = sprintf("SELECT folder_id,folder_name FROM folders ".
                         "WHERE folder_owner_id=%d AND folder_public=0 ".
                         "  AND folder_children=1 ".
                         "ORDER BY folder_name",
                         $CURUSER->get_property(user_id));
            $r = $DB->read($q);
            while(list($fid,$fname) = $DB->fetch_array($r)) {
                if ($fid!=$thisid) {
                    $parents[$fid] = $fname;
                }
            }
        }
        $r = $DB->read("SELECT folder_id,folder_name FROM folders ".
                        "WHERE folder_public!=0 AND folder_children=1 ".
                        "ORDER BY folder_name");
        while(list($fid,$fname) = $DB->fetch_array($r)) {
            if ($fid!=$thisid) {
                $parents[$fid] = $fname;
            }
        }
        // build array
        $a = array(
                array(name => folder_id,
                      type => hidden,
                      value => $this->get_property(folder_id)),
                array(name => folder_name,
                      type => text,
                      size => 250,
                      value => $this->get_property(folder_name),
                      prompt => _PROMPT_FOLDER_TITLE),
                array(name => folder_parent_id,
                      type => select,
                      options => $parents,
                      value => $this->get_property(folder_parent_id),
                      prompt => _PROMPT_FOLDER_PARENT),
                array(name => folder_body,
                      type => textarea,
                      value => $this->get_property(folder_body),
                      doc => _DOC_DOC_BODY,
                      prompt => _PROMPT_FOLDER_BODY),
                array(name => folder_limit_type,
                      type => select,
                      options => $types,
                      value => $this->get_property(folder_limit_type),
                      prompt => _PROMPT_FOLDER_TYPE),
                array(name => folder_sorted,
                      type => select,
                      options => array(
                        '' => 'Title',
                        'user' => 'Owner name',
                        'type' => 'Document type',
                        'created' => 'Date (oldest first)',
                        '-created' => 'Date (most recent first)'
                      ),
                      value => $this->get_property(folder_sorted),
                      doc => 'Choose how documents in the folder are sorted',
                      prompt => 'Sort documents by'),
                array(name => folder_group,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(folder_group)+0,
                      doc => 'Check this box to group folder items by the sort fields',
                      prompt => 'Group items'),
                array(name => folder_pages,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(folder_pages)+0,
                      doc => 'Check this box to display folder contents in pages',
                      prompt => 'Folder pages'),
                array(name => folder_public,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(folder_public),
                      doc => _DOC_FOLDER_PUBLIC,
                      prompt => _PROMPT_FOLDER_PUBLIC),
                array(name => folder_user_limit,
                      type => text,
                      size => 3,
                      value => $this->get_property(folder_user_limit),
                      doc => _DOC_FOLDER_USERLIMIT,
                      prompt => _PROMPT_FOLDER_USERLIMIT),
                array(name => folder_children,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(folder_children),
                      doc => _DOC_FOLDER_CHILDREN,
                      prompt => _PROMPT_FOLDER_CHILDREN),
                array(name => allow_ratings,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_ratings),
                      doc => _DOC_DOC_RATING,
                      prompt => _PROMPT_DOC_RATING),
                array(name => allow_comments,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_comments),
                      doc => _DOC_DOC_COMMENTS,
                      prompt => _PROMPT_DOC_COMMENTS),
             );
        return array_merge($a,$this->custom_properties());
    }

    // display(template) - returns formatted document based on template
    function display($template) {
        global $DB,$PAGE,$PHP_SELF;
        $PAGE->load_template(folder,$template);
        foreach($this->get_properties() as $name => $value) {
            $PAGE->set_property($name,$value);
        }
        return $PAGE->parse(folder);
    }

    // add_doc(obj) - given an object pointer, adds the object to the folder
    //   actually just performs validation
    function add_doc(&$doc) {
        global $DB;
        // validate the folder limit type
        $type = $this->get_property(folder_limit_type);
        if (($type!="") && ($doc->get_property(doc_type)!=$type)) {
            $doc->add_error(_ERR_BADTYPE,$type);
            $doc->set_property(doc_folder_id,0);
        }
        else if (!$this->get_property(folder_id)) {
            siteframe_abort(_ERR_NOFOLDER);
        }
        else {
            // validate the user limit
            if ($this->get_property(folder_user_limit)) {
                $r = $DB->read(sprintf("SELECT COUNT(*) FROM docs WHERE doc_folder_id=%d ".
                                        "   AND doc_id!=%d ".
                                        "   AND doc_owner_id=%d",
                                        $this->get_property(folder_id),
                                        $doc->get_property(doc_id),
                                        $doc->get_property(doc_owner_id)));
                list($count) = $DB->fetch_array($r);
                if ($count >= $this->get_property('folder_user_limit')) {
                    $doc->set_property(doc_folder_id,0);
                    $doc->set_property(doc_hidden,1);
                    // $doc->update();
                    $doc->add_error(_ERR_FOLDERLIMIT);
                }
            }
            if ($doc->errcount())
                return;
            $doc->set_property(doc_folder_id,$this->get_property(folder_id));
            $doc->set_property(allow_ratings,$this->get_property(allow_ratings));
            $doc->set_property(allow_comments,$this->get_property(allow_comments));
        }
    }

    // del_doc(doc) - remove a document from a folder
    function del_doc(&$doc) {
        $doc->set_property('doc_folder_id',0);
    }

    // validate - ensure correctness
    function validate() {
        global $TOP_FOLDERS_ADMIN_ONLY;
        // check for valid owner
        if (!$this->get_property('folder_owner_id')) {
          $this->add_error('Invalid folder owner ID');
        }
        // correct children for subfolders-only folders
        if ($this->get_property(folder_limit_type) == 'none') {
            $this->set_property(folder_children,1);
        }
        // check for folder path validity
        if ((!$this->get_property('folder_parent_id')) &&
             $TOP_FOLDERS_ADMIN_ONLY &&
             (!isadmin())) {
            $this->add_error(_ERR_FOLDERADMIN);
        }
        if ($this->get_property(folder_id) &&
                 descendent($this->get_property(folder_id),$value)) {
            $this->add_error(_ERR_FOLDERRECURSIVE);
        }
    }

    // folder_path - returns a navigation string
    function folder_path($sep=' &rarr; ', $prefix='<div class="path">', $suffix='</div>') {
        global $SITE_PATH;
        $path = '';
        $fid = $this->get_property(folder_id);
        do {
            $folder = new Folder($fid);
            $url = sprintf('<a href="%s/folder.php?id=%d">%s</a>',
                    $SITE_PATH,
                    $fid,
                    $folder->get_property(folder_name));
            $path = $url . $path;
            $fid = $folder->get_property(folder_parent_id);
            if ($fid!=0) {
                $path = $sep . $path;
            }
        } while ($fid!=0);
        return $prefix . $path . $suffix;
    }

    // subfolder_orderby - generate ORDER BY clause for subfolders
    function subfolder_orderby() {
        $sorted = $this->get_property('folder_sorted');
        switch ($sorted) {
        case 'user':
            $sortby = 'users.user_lastname,users.user_firstname,folders.folder_name';
            break;
        case 'created':
            $sortby = 'folders.created,folders.folder_name';
            break;
        case '-created':
            $sortby = 'folders.created DESC,folders.folder_name';
            break;
        default:
            $sortby = 'folders.folder_name';
        }
        return $sortby;
    }

    // doc_orderby - generate ORDER BY clause for documents
    function docs_orderby() {
        $sorted = $this->get_property('folder_sorted');
        switch ($sorted) {
        case 'user':
            $sortby = 'users.user_lastname,users.user_firstname,docs.doc_title';
            break;
        case 'type':
            $sortby = 'docs.doc_type,docs.doc_title';
            break;
        case 'created':
            $sortby = 'docs.doc_created,docs.doc_title,users.user_lastname,users.user_firstname';
            break;
        case '-created':
            $sortby = 'docs.doc_created DESC,docs.doc_title,users.user_lastname,users.user_firstname';
            break;
        default:
            $sortby = 'docs.doc_title,users.user_lastname,users.user_firstname';
        }
        return $sortby;
    }

    // folder_docs_sql - returns the SQL statement to fetch all docs in the folder
    function folder_docs_sql() {
        return sprintf(
                "SELECT * FROM docs INNER JOIN users ON (docs.doc_owner_id=users.user_id) WHERE doc_folder_id=%d AND doc_hidden=0 ORDER BY %s",
                $this->get_property('folder_id'),
                $this->docs_orderby());
    }

    // title
    function title() {
      return $this->get_property('folder_name');
    }

}

// a PicFolder is one that is limited to images
class PicFolder extends Folder {

    // PicFolder - constructor
    function PicFolder($id=0, $dbrow=0) {
        parent::Folder($id, $dbrow);
        parent::set_property('folder_children',0);
        parent::set_property('folder_limit_type','Image');
    }

    // set_property
    function set_property($name,$value) {
        switch($name) {
        case 'folder_children':
        case 'folder_limit_type':
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // input_form_values
    function input_form_values() {
        $a = parent::input_form_values();
        foreach ($a as $name => $value) {
            switch($a[$name]['name']) {
            case 'folder_children':
            case 'folder_limit_type':
                $a[$name]['type'] = 'hidden';
                $a[$name]['prompt'] = 0;
                $a[$name]['doc'] = 0;
            }
        }
        return $a;
    }

} // end class Folder

// returns true if $child is a descendant of $parent
function descendent($parent,$child) {
    global $DB;
    $r = $DB->read("SELECT folder_id FROM folders ".
                    "WHERE folder_parent_id=$parent");
    $status = false;
    while(list($fid) = $DB->fetch_array($r)) {
        if ($fid == $child)
            return true;
        $status = $status || descendent($fid,$child);
    }
    return $status;
}


?>
