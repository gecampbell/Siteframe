<?php
// event.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: event.php,v 1.10 2003/05/06 22:16:51 glen Exp $
//
// Holds event documents

define(EVENT_FIELDS,'doc_id|doc_created|doc_modified|doc_type|doc_folder_id|doc_owner_id|doc_tag|doc_hidden|doc_title|doc_body|event_begin|event_end');

class Event extends Document {

    // Event(id) - constructor
    function Event($id=0, $dbrow=0) {
        global $DB;
        parent::Document($id, $dbrow);
        if ($dbrow) {
          $id = $dbrow['doc_id'];
        }
        if ($id) {
            $q = sprintf("SELECT event_begin,event_end FROM events ".
                         "WHERE doc_id=%d",$id);
            $r = $DB->read($q);
            list($beg,$end) = $DB->fetch_array($r);
            $this->set_property(event_begin,$beg);
            $this->set_property(event_end,$end);
        }
        else
            $this->set_property(doc_type,'Event');
    }

    // add() - insert new event record
    function add() {
        global $DB;
        parent::add();
        if (!$this->errcount()) {
            $q = sprintf("INSERT INTO events (doc_id,event_begin,event_end) ".
                         "VALUES (%d,'%s','%s')",
                         $this->get_property(doc_id),
                         $this->get_property(event_begin),
                         $this->get_property(event_end));
            $DB->write($q);
            $this->add_error($DB->error());
        }
    }

    // update() - update event record
    function update() {
        global $DB;
        parent::update();
        if (!$this->errcount()) {
            $q = sprintf("UPDATE events SET event_begin='%s', event_end='%s' ".
                         "WHERE doc_id=%d",
                         $this->get_property(event_begin),
                         $this->get_property(event_end),
                         $this->get_property(doc_id));
            $DB->write($q);
            $this->add_error($DB->error());
        }
    }

    // delete() - delete event record
    function delete() {
        global $DB;
        if (!$this->errcount()) {
            $DB->write(sprintf("DELETE FROM events WHERE doc_id=%d",
                        $this->get_property(doc_id)));
            $this->add_error($DB->error());
            parent::delete();
        }
    }

    // get_xml_properties()
    function get_xml_properties() {
        return parent::get_xml_properties(EVENT_FIELDS);
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

    // get_properties() - return property list
    function get_properties() {
        $a = parent::get_properties();
        $beg = $a[event_begin];
        $end = $a[event_end];
        $a[event_begin_year] = date('Y',strtotime($beg));
        $a[event_begin_month] = date('m',strtotime($beg));
        $a[event_begin_day] = date('d',strtotime($beg));
        $a[event_begin_hour] = date('H',strtotime($beg));
        $a[event_begin_minute] = date('i',strtotime($beg));
        $a[event_end_year] = date('Y',strtotime($end));
        $a[event_end_month] = date('m',strtotime($end));
        $a[event_end_day] = date('d',strtotime($end));
        $a[event_end_hour] = date('H',strtotime($end));
        $a[event_end_minute] = date('i',strtotime($end));
        $a[doc_summary] = $this->get_property('doc_summary');
        return $a;
    }

    // set_property(name,value)
    function set_property($name,$value) {
        switch($name) {
        case 'event_end':
            if ((trim($value)=='') ||
                    (strtotime($value) < strtotime($this->get_property(event_begin))))
                parent::set_property($name,$this->get_property(event_begin));
            else
                parent::set_property($name,$value);
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // input_form_values()
    function input_form_values() {
        $beg = trim($this->get_property(event_begin));
        $a[] = array(
            name => event_begin,
            type => datetime,
            value => (($beg!='') ? $beg : date('Y-m-d 00:00')),
            prompt => _PROMPT_EVENT_BEGIN
        );
        $a[] = array(
            name => event_end,
            type => datetime,
            value => $this->get_property(event_end),
            prompt => _PROMPT_EVENT_END
        );
        $a[] = array(
            name => event_recurs,
            type => select,
            options => array(0 => "No repeat",
                        "day" => "Daily",
                        "wday" => "Week Days",
                        "month" => "Monthly",
                        "year" => "Annually"
                       ),
            value => $this->get_property(event_recurs),
            prompt => "Repeating Event"
        );
        $a[] = array(
            name => event_record,
            type => checkbox,
            value => $this->get_property(event_record),
            prompt => "Record event in history"
        );
        $a = array_merge($a,parent::input_form_values());
        $cats = doc_categories($this->get_property('doc_type'));
        for($i=1; $i<=$MAX_DOC_CATEGORIES; $i++) {
          $a[] = array(name => "doc_category_$i",
                       type => select,
                       options => $cats,
                       value => $this->get_property("doc_category_$i"),
                       prompt => sprintf(_PROMPT_DOC_CATEGORY,$i)
                      );
        }
        $a =  array_merge($a,$this->custom_properties());
        return $a;
    }

}

?>
