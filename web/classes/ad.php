<?php
// Ad - an advertisement is a subclass of Notice
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: ad.php,v 1.9 2003/05/06 22:16:50 glen Exp $
//
// use this as a template

class Ad extends Notice {

  // Ad - constructor function
  function Ad($id=0, $dbrow=0) {
    parent::Notice($id,$dbrow);
    $this->set_property('doc_type','Ad');
  }

  // get_property
  function get_property($name) {
    switch($name) {
      case 'ad_url_link':
        $s = sprintf('<a class="x" href="%s/redirect.php?id=%d">%s</a>',
              $_GLOBALS['SITE_PATH'],
              parent::get_property('doc_id'),
              parent::get_property('ad_url'));
        return $s;
        break;
      default:
        return parent::get_property($name);
    }
  }

  // get_properties()
  function get_properties() {
    $a = parent::get_properties();
    $a['ad_url_link'] = $this->get_property('ad_url_link');
    return $a;
  }

  // set property
  function set_property($name,$value) {
    global $MAX_AD_DAYS,$MAX_AD_SIZE;
    switch($name) {
      case 'doc_body':
        if (!$MAX_AD_SIZE) $MAX_AD_SIZE=500;
        if (strlen($value) > $MAX_AD_SIZE)
          $this->add_error(_ERR_AD_SIZE,$MAX_AD_SIZE);
        else
          parent::set_property($name,$value);
        break;
      case 'notice_end_date':
        if ((strtotime($value) - strtotime($this->get_property('notice_begin_date')))
              > ($MAX_AD_DAYS*24*60*60))
          $this->add_error(_ERR_AD_DAYS,$MAX_AD_DAYS);
        else
          parent::set_property($name,$value);
        break;
      case 'ad_url':
        if (trim($value) == '')
          return;
        else if (!preg_match('|^http://|',$value))
          $this->add_error(_ERR_BAD_AD_URL,htmlentities($value));
        else
          parent::set_property($name,$value);
        break;
      default:
        parent::set_property($name,$value);
    }
  }

  // input_form_values() - construct information
  function input_form_values() {
    global $CURUSER,$MAX_AD_DAYS;
    $a = parent::input_form_values();
    foreach ($a as $name => $val) {
      switch($a[$name][name]) {
        case 'notice_begin_date':
          $a[$name][type] = 'hidden';
          if (!$this->get_property('doc_id'))
            $a[$name][value] = date('Y-m-d 00:00',time());
          break;
        case 'notice_end_date':
          $a[$name][type] = 'hidden';
          if (!$this->get_property('doc_id'))
            $a[$name][value] = date('Y-m-d 23:39',time()+(24*60*60*($MAX_AD_DAYS-1)));
          break;
      default:
      }
    }
    $a[] = array(
            name => doc_folder_id,
            type => hidden,
            value => $this->get_property(doc_folder_id));
    $a[] = array(
            name => ad_url,
            type => text,
            size => 250,
            value => $this->get_property('ad_url'),
            doc => _DOC_AD_URL,
            prompt => _PROMPT_AD_URL);
    $a = array_merge($a,$this->custom_properties());
    return $a;
  }

  // validate() - verify info
  function validate() {
    global $AD_FOLDER_REQUIRED;
    if ($AD_FOLDER_REQUIRED && ($this->get_property('doc_folder_id')==0)) {
      $this->add_error(_ERR_AD_FOLDER);
    }
    parent::validate();
  }

}

?>
