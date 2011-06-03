<?php
// pfolder.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: pfolder.php,v 1.5 2003/06/14 05:18:58 glen Exp $

class PFolder extends Folder {

    // PFolder(id) - constructor
    function PFolder($id=0) {
        parent::Folder($id);
        $this->set_property(folder_type,'PFolder');
    }

    // set_property(name,val) - perform error-checking and cleanup
    function set_property($name,$value) {
        switch($name) {
        case 'folder_user_limit':
            if ($value < 0)
                $this->add_error(_ERR_BADVALUE,$name,$value);
            else
                parent::set_property($name,$value);
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // input_form_values() - return input form array
    function input_form_values() {
        global $CURUSER;
        $a = parent::input_form_values();
        $a[] = array(name => folder_public,
                      type => hidden,
                      value => 1);
        $a[] = array(name => folder_user_limit,
                      type => text,
                      size => 3,
                      value => $this->get_property(folder_user_limit),
                      prompt => _PROMPT_FOLDER_USERLIMIT);
        return $a;
    }

    // add_doc(obj) add a document to this folder
    function add_doc(&$doc) {
        global $DB;
        parent::add_doc($doc);
        if ($this->get_property(folder_user_limit)) {
            $r = $DB->read(sprintf("SELECT COUNT(*) FROM docs WHERE doc_folder_id=%d ".
                                    "   AND doc_owner_id=%d",
                                    $this->get_property(folder_id),
                                    $doc->get_property(doc_owner_id)));
            list($count) = $DB->fetch_array($r);
            if ($count > $this->get_property(folder_user_limit)) {
                $doc->set_property(doc_folder_id,0);
                $doc->set_property(doc_hidden,1);
                $doc->update();
                $doc->add_error(_ERR_FOLDERLIMIT);
            }
        }
    }

}

?>
