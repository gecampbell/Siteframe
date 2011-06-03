<?php
// Virtual Folder
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: vfolder.php,v 1.6 2003/06/11 06:39:04 glen Exp $

class VFolder extends Folder {

    // VFolder - constructor
    function VFolder($id=0, $dbrow=0) {
        parent::Folder($id,$dbrow);
        parent::set_property('folder_type','VFolder');
        parent::set_property('folder_public',0);
        parent::set_property('folder_children',0);
    }

    // delete() - for virtual folders, only delete the folder!!!!
    function delete() {
        global $DB;
        $fid=$this->get_property(folder_id);
        $DB->write(sprintf("DELETE FROM folders WHERE folder_id=%d",$fid));
        $this->add_error($DB->error());
        if (!$this->errcount()) {
            logmsg('Deleted folder %d, "%s"',$this->get_property(folder_id),
                $this->get_property(folder_name));
        }
        else {
            logmsg('Error deleting folder %d, "%s," error=%s',
                $this->get_property(folder_id),
                $this->get_property(folder_name),
                $this->get_errors());
        }
    }

    // set_property
    function set_property($name,$value) {
        switch($name) {
        case 'folder_type':
        case 'folder_public':
        case 'folder_children':
            break;
        case 'folder_sql':
            // this allows HTML tags within the field
            Siteframe::set_property($name,$value);
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // input_form_values - return string of fields
    function input_form_values() {
        $a = parent::input_form_values();
        $a[] = array(
                name => folder_sql,
                type => textarea,
                value => $this->get_property('folder_sql'),
                prompt => "SQL string",
                doc => "Enter the SQL used to retrieve documents from the folder; ".
                       "Use a %s symbol for the ORDER BY clause. If you need a literal ".
                       "percent sign (for example, in a LIKE clause), then use %%."
                );
        return $a;
    }

    // add_doc - don't allow this
    function add_doc(&$doc) {
        $doc->add_error('Sorry, you cannot add documents to this folder');
    }

    // folder_docs_sql - returns the SQL statement to fetch all docs in the folder
    function folder_docs_sql() {
        global $PAGE;
        $s = sprintf(
                $this->get_property('folder_sql'),
                $this->docs_orderby());
        $PAGE->set_property('__folder_docs_sql__',$s);
        return $PAGE->parse('__folder_docs_sql__');
    }
}

?>
