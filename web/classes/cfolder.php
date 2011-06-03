<?php
// cfolder - competition folder class
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: cfolder.php,v 1.10 2004/04/08 17:23:18 glen Exp $
//
// a competition folder - competition folders can contain multiple
// documents; they include a built-in poll where members can vote on
// their favorite document in the folder.

define(VOTE_VAL,6);

class CFolder extends LFolder {

  // CFolder - constructor function
  function CFolder($id=0,$dbrow=0) {
    parent::LFolder($id, $dbrow);
    $this->set_property(folder_type,'CFolder');
  }

  // update - check for valid dates
  // cannot modify folder after voting has begun
  function update() {
    if (!isadmin() && (time() > strtotime($this->get_property(folder_begin_voting))))
      $this->add_error(_ERR_CFOLDER_VOTING);
    else
      parent::update();
  }

  // get property
  function get_property($name) {
    switch($name) {
    case 'folder_voting':
      if ((time() > strtotime($this->get_property(folder_begin_voting))) &&
          (time() < strtotime($this->get_property(folder_end_voting))))
          return 1;
      else
          return 0;
      break;
    case 'folder_competition_active':
      if (time() > strtotime($this->get_property(folder_end_voting)))
        return 0;
      else
        return 1;
    default:
        return parent::get_property($name);
    }
  }

  // get_properties
  function get_properties() {
    $a = parent::get_properties();
    $beg = $a[folder_begin_voting];
    $end = $a[folder_end_voting];
    $a[folder_begin_voting_year] = date('Y',strtotime($beg));
    $a[folder_begin_voting_month] = date('m',strtotime($beg));
    $a[folder_begin_voting_day] = date('d',strtotime($beg));
    $a[folder_end_voting_year] = date('Y',strtotime($end));
    $a[folder_end_voting_month] = date('m',strtotime($end));
    $a[folder_end_voting_day] = date('d',strtotime($end));
    $a[folder_voting] = $this->get_property('folder_voting');
    $a[folder_competition_active] = $this->get_property('folder_competition_active');
    return $a;
  }

  // set_property() - check for valid properties
  function set_property($name,$value) {
    switch($name) {
    case 'folder_begin_voting':
      parent::set_property($name,$value);
      break;
    case 'folder_end_voting':
      parent::set_property($name,str_replace('00:00','23:59',$value));
      break;
    case 'folder_limit_type':
      switch($value) {
      case 'Article':
      case 'Image':
        parent::set_property($name,$value);
        break;
      default:
        $this->add_error(_ERR_CFOLDER_BAD_LIMIT,$value);
      }
      break;
    default:
      parent::set_property($name,$value);
    }
  }

  // validate() - correct any errors
  function validate() {
    $this->set_property('folder_children',0);
    $this->set_property('allow_ratings',0);
    // creator chooses whether to allow comments or not
    // $this->set_property('allow_comments',0);
    parent::validate();
    if ($this->get_property(folder_begin_voting) <
        $this->get_property(folder_begin_date)) {
        $this->set_property(folder_begin_voting,$this->get_property(folder_begin_date));
    }
    if ($this->get_property(folder_end_voting) <
        $this->get_property(folder_end_date)) {
        $this->set_property(folder_end_voting,$this->get_property(folder_end_date));
    }
    if ($this->get_property(folder_end_voting) <
        $this->get_property(folder_begin_voting)) {
        $this->set_property(folder_end_voting,$this->get_property(folder_begin_voting));
    }
  }

  // add_doc() - add a document to the folder
  // this sets the "competition_entry" flag to "1"
  function add_doc(&$doc) {
    global $DB,$RATINGS,$DEFAULT_CFOLDER_HIDDEN;
    $doc->set_property('competition_entry',1);
    $doc->set_property('allow_ratings',0);
    $doc->set_property('doc_hidden',$DEFAULT_CFOLDER_HIDDEN);
    // creator chooses whether to allow comments or not
    // $doc->set_property('allow_comments',0);
    parent::add_doc($doc);
    // delete any existing ratings
    @$DB->write(sprintf("DELETE FROM ratings WHERE doc_id=%d",
                  $doc->get_property('doc_id')));
  }

  // del_doc(doc) - remove a document from a folder
  function del_doc(&$doc) {
    $doc->set_property('competition_entry',0);
    parent::del_doc($doc);
  }

  // input_form_values() - return input form array
  function input_form_values() {
    $a = parent::input_form_values();
    $beg = trim($this->get_property(folder_begin_voting));
    $a[] = array(
      name => folder_begin_voting,
      type => date,
      value => (($beg!='') ? $beg : date('Y-m-d 00:01')),
      prompt => "First day of competition");
    $a[] = array(
      name => folder_end_voting,
      type => date,
      value => $this->get_property(folder_end_voting),
      prompt => "Last day of competition");
    $a[] = array(
      name => 'folder_competition_type',
      type => 'select',
      options => array(
                  "max" => "Highest total rating wins",
                  "maxavg" => "Highest average rating wins",
                  "vote" => "Most single votes wins"
                  ),
      value => $this->get_property('folder_competition_type'),
      prompt => _PROMPT_CFOLDER_COMPETITION_TYPE,
      );
    return $a;
  }

  // process() - process votes/ratings for folder
  function process() {
    global $CURUSER,$DB,$_POST;
    if (!$CURUSER)
      return;
    if (!$this->get_property('folder_voting'))
      return;
    // process new votes
    $q = sprintf("SELECT * FROM docs WHERE doc_folder_id=%d",
          $this->get_property('folder_id'));
    $r = $DB->read($q);
    $class = $this->get_property('folder_limit_type');
    while($dbrow = $DB->fetch_array($r)) {
      $doc = new $class(0,$dbrow);
      $q = sprintf("DELETE FROM ratings WHERE user_id=%d AND doc_id=%d",
            $CURUSER->get_property('user_id'),
            $doc->get_property('doc_id'));
      @$DB->write($q);
      $prop = sprintf('doc_rating_%d',$doc->get_property('doc_id'));
      if ($this->get_property('folder_competition_type') == "vote") {
        if ($_POST["vote"] == $doc->get_property('doc_id')) {
          $q = sprintf("INSERT INTO ratings (user_id,doc_id,rating) VALUES ".
                       "(%d,%d,%d)",
                       $CURUSER->get_property('user_id'),
                       $doc->get_property('doc_id'),
                       VOTE_VAL);
          @$DB->write($q);
        }
      }
      else if ($_POST[$prop]) {
        $q = sprintf("INSERT INTO ratings (user_id,doc_id,rating) VALUES ".
                     "(%d,%d,%d)",
                     $CURUSER->get_property('user_id'),
                     $doc->get_property('doc_id'),
                     $_POST[$prop]);
        @$DB->write($q);
      }
    }
    logmsg("user %d, %s voted in folder %d, %s",
      $CURUSER->get_property('user_id'),
      $CURUSER->get_property('user_name'),
      $this->get_property('folder_id'),
      $this->get_property('folder_name'));
  }

}

?>
