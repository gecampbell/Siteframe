<?php
// notice.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: notice.php,v 1.10 2003/06/14 17:08:34 glen Exp $
//
// A "notice" is a document that is valid for only a limited time

class Notice extends Article {

  // Notice() - constructor function
  function Notice($id=0, $dbrow=0) {
    global $NOTICES_ADMIN_ONLY;
    if ((get_class($this)=='Notice') && $NOTICES_ADMIN_ONLY && !isadmin())
      $this->add_error("Notices may only be created by site administrators");
    parent::Article($id,$dbrow);
    $this->set_property('doc_type','Notice');
    $this->set_property('doc_hidden',1);
  }

  // set property
  function set_property($name,$value) {
    switch($name) {
    case 'notice_end_date':
      if (strtotime($value) < strtotime($this->get_property('notice_begin_date')))
        $value = $this->get_property('notice_begin_date');
      parent::set_property($name,str_replace('00:00','23:59',$value));
      break;
    default:
      parent::set_property($name,$value);
    }
  }

  // input_form_values()
  function input_form_values() {
    $a = parent::input_form_values();
    foreach ($a as $name => $val) {
      switch($a[$name][name]) {
      case 'doc_summary':
      case 'doc_folder_id':
      case 'doc_hidden':
      case 'allow_ratings':
      case 'allow_comments':
      case 'doc_category_1':
      case 'doc_category_2':
      case 'doc_category_3':
      case 'doc_category_4':
      case 'doc_category_5':
      case 'doc_category_6':
      case 'doc_category_7':
      case 'doc_category_8':
      case 'doc_category_9':
      case 'doc_category_10':
      case 'copying_allowed':
        unset($a[$name]);
        break;
      default:
      }
    }
    $beg = trim($this->get_property('notice_begin_date'));
    $a[] = array(
            name => notice_begin_date,
            type => date,
            value => (($beg!='') ? $beg : date('Y-m-d 00:01')),
            prompt => _PROMPT_NOTICE_BEGIN_DATE,
            doc => _DOC_NOTICE_BEGIN_DATE
            );
    $a[] = array(
            name => notice_end_date,
            type => date,
            value => $this->get_property('notice_end_date'),
            prompt => _PROMPT_NOTICE_END_DATE,
            doc => _DOC_NOTICE_END_DATE
            );
    $a = array_merge($a,$this->custom_properties());
    return $a;
  }

  // notify() - don't send notifications for Notice documents
  function notify() {
    return;
  }

  // validate() - verify
  function validate() {
    global $DOC_REQUIRE_FOLDER;
    $DOC_REQUIRE_FOLDER=FALSE;
  }

}

?>
