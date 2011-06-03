<?php
// article.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: article.php,v 1.11 2003/06/24 04:12:31 glen Exp $

// an article is basically a document that includes a summary

class Article extends Document {

    // Article - constructor
    function Article($id=0, $dbrow=0) {
        parent::Document($id, $dbrow);
        $this->set_property('doc_type','Article');
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
                /*
                array(name => doc_type,
                      type => hidden,
                      value => 'Article'),
                */
                array(name => doc_title,
                      type => text,
                      size => 250,
                      focus => TRUE,
                      value => $this->get_property(doc_title),
                      prompt => _PROMPT_DOC_TITLE),
                array(name => doc_folder_id,
                      type => select,
                      help => 'docfolder',
                      options => folderlist($CURUSER->get_property(user_id),'Article'),
                      value => $this->get_property(doc_folder_id),
                      prompt => _PROMPT_DOC_FOLDER),
                array(name => doc_summary,
                      type => textarea,
                      help => 'summary',
                      value => $this->get_property(doc_summary),
                      rows => 4,
                      prompt => _PROMPT_DOC_SUMMARY),
                array(name => doc_body,
                      type => textarea,
                      value => $this->get_property(doc_body),
                      rows => 15,
                      help => 'autoformat',
                      doc => _DOC_DOC_BODY,
                      prompt => _PROMPT_DOC_BODY),
                array(name => doc_hidden,
                      type => checkbox,
                      rval => 1,
                      help => 'hidden',
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

        $a = array_merge($a,$this->custom_properties());

        // categories
        $cats = doc_categories('Article');
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
                         help => 'doctag',
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
