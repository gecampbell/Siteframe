<?php
// Class::File
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: file.php,v 1.14 2010/04/12 10:57:18 glen Exp $
//
// Defines the File class

// in older versions of PHP, "file"-type form fields returned "none"
// if no file was specified
define(NO_FILE_NAME,'');

class DocFile extends Document {

    // DocFile - constructor
    function DocFile($id=0, $dbrow=0) {
        parent::Document($id, $dbrow);
        $this->set_property('doc_type','DocFile');
        $this->set_property('doc_file_download_count',0);
    }

    // update - update file information
    function update() {
        if (!$this->get_property(doc_file_size))
            parent::set_property(doc_file_size,filesize($this->get_property(doc_file)));
        parent::update();
    }

    // delete - delete file
    function delete() {
        @unlink($this->get_property(doc_file));
        parent::delete();
    }

    // get_property
    function get_property($name) {
      switch($name) {
        case 'doc_summary':
          return parent::get_property('doc_body');
        default:
          return parent::get_property($name);
      }
    }

    // get_properties
    function get_properties() {
      $a = parent::get_properties();
      $a['doc_summary'] = $this->get_property('doc_body');
      return $a;
    }

    // input_form_values() - return input form array
    function input_form_values() {
        global $PUBLISH_MODEL,$CURUSER,$MAX_DOC_CATEGORIES;
        if ($PUBLISH_MODEL!='open') {
            if (!$CURUSER)
                $hidden_disabled = 1;
            else if (!isadmin())
                $hidden_disabled = 1;
            else
                $hidden_disabled = 0;
        }
        $a = array(
                array(name => doc_id,
                      type => hidden,
                      value => $this->get_property(doc_id)),
                array(name => doc_type,
                      type => hidden,
                      value => 'DocFile'),
                array(name => doc_title,
                      type => text,
                      size => 250,
                      value => $this->get_property(doc_title),
                      prompt => _PROMPT_DOC_TITLE),
                array(name => doc_folder_id,
                      type => select,
                      options => folderlist($CURUSER->get_property(user_id),'DocFile'),
                      value => $this->get_property(doc_folder_id),
                      prompt => _PROMPT_DOC_FOLDER),
                array(name => doc_file,
                      type => file,
                      value => $this->get_property(doc_file),
                      optional => $this->get_property(doc_id),
                      prompt => _PROMPT_DOC_FILE),
                array(name => doc_body,
                      type => textarea,
                      value => $this->get_property(doc_body),
                      rows => 15,
                      doc => _DOC_DOC_BODY,
                      prompt => _PROMPT_DOC_BODY),
                array(name => doc_hidden,
                      type => checkbox,
                      rval => 1,
                      disabled => $hidden_disabled,
                      value => $this->get_property(doc_hidden),
                      prompt => _PROMPT_DOC_HIDDEN),
                array(name => allow_ratings,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_ratings),
                      prompt => _PROMPT_DOC_RATING),
                array(name => allow_comments,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_comments),
                      prompt => _PROMPT_DOC_COMMENTS),
                array(name => copying_allowed,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(copying_allowed),
                      prompt => _PROMPT_DOC_COPYING)
             );
        $a =  array_merge($a,$this->custom_properties());
        $cats = doc_categories($this->get_property('doc_type'));
        for($i=1; $i<=$MAX_DOC_CATEGORIES; $i++) {
          $a[] = array(name => "doc_category_$i",
                       type => select,
                       options => $cats,
                       value => $this->get_property("doc_category_$i"),
                       prompt => sprintf(_PROMPT_DOC_CATEGORY,$i)
                      );
        }
        if (isadmin())
            $a[] = array(name => doc_tag,
                         type => text,
                         size => 32,
                         value => $this->get_property(doc_tag),
                         prompt => _PROMPT_DOC_TAG);
        else
            $a[] = array(name => doc_tag,
                         type => hidden,
                         value => $this->get_property(doc_tag));
        return $a;
    }

}

?>
