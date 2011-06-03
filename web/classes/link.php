<?php
/* link.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: link.php,v 1.9 2003/05/06 22:16:51 glen Exp $
**
** A "link" is a URL with attached description.
*/

class Link extends Document {

    // Link - constructor
    function Link($id=0, $dbrow=0) {
        parent::Document($id, $dbrow);
        $this->set_property(doc_type,'Link');
        $this->set_property(doc_summary,'');
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
      $a['doc_summary'] = $this->get_property('doc_summary');
      return $a;
    }

    // set_property(name,value)
    function set_property($name,$value) {
        switch($name) {
        case 'doc_link_url':
            if (clean($value)=='')
                $this->add_error(_ERR_BADURL,_PROMPT_LINK_URL);
            else
                parent::set_property($name,clean($value));
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // input_form_values() - return input form array
    function input_form_values() {
        global $PUBLISH_MODEL,$CURUSER,$MAX_DOC_CATEGORIES;
        $a = array(
                array(name => doc_id,
                      type => hidden,
                      value => $this->get_property(doc_id)),
                array(name => doc_title,
                      type => text,
                      size => 250,
                      value => $this->get_property(doc_title),
                      prompt => _PROMPT_LINK_TITLE),
                array(name => doc_folder_id,
                      type => select,
                      options => folderlist($CURUSER->get_property(user_id),'Link'),
                      value => $this->get_property(doc_folder_id),
                      prompt => _PROMPT_DOC_FOLDER),
                array(name => doc_link_url,
                      type => text,
                      value => $this->get_property(doc_link_url),
                      size => 250,
                      prompt => _PROMPT_LINK_URL),
                array(name => doc_body,
                      type => textarea,
                      value => $this->get_property(doc_body),
                      rows => 5,
                      doc => _DOC_DOC_BODY,
                      prompt => _PROMPT_DOC_BODY),
                array(name => doc_hidden,
                      type => checkbox,
                      rval => 1,
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
