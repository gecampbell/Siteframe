<?php
// Limited Folder - LFolder - open only between certain dates
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: lfolder.php,v 1.5 2003/05/06 22:16:51 glen Exp $
//
// Similar to a regular Folder, this folder is "public" based on
// a begin and end date range

Class LFolder extends Folder {

    // constructor
    function LFolder($id=0, $dbrow=0) {
        parent::Folder($id, $dbrow);
        $this->set_property(folder_type,'LFolder');
    }

    // define form values
    function input_form_values() {
        $a = parent::input_form_values();
        $beg = trim($this->get_property(folder_begin_date));
        $a[] = array(name => folder_begin_date,
                     type => date,
                     value => (($beg!='') ? $beg : date('Y-m-d 00:01')),
                     prompt => "First day of availability");
        $a[] = array(name => folder_end_date,
                     type => date,
                     value => $this->get_property(folder_end_date),
                     prompt => "Last day of availability");
        return $a;
    }

    // set property
    function set_property($name,$value) {
        switch($name) {
        case 'folder_public':
            break;
        case 'folder_begin_date':
            parent::set_property($name,$value);
            break;
        case 'folder_end_date':
            parent::set_property($name,str_replace('00:00','23:59',$value));
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // get property
    function get_property($name) {
        switch($name) {
        case 'folder_public':
            if ((time() > strtotime($this->get_property(folder_begin_date))) &&
                (time() < strtotime($this->get_property(folder_end_date))))
                return 1;
            else
                return 0;
            break;
        default:
            return parent::get_property($name);
        }
    }

    // get_properties
    function get_properties() {
        $a = parent::get_properties();
        $beg = $a[folder_begin_date];
        $end = $a[folder_end_date];
        $a[folder_begin_date_year] = date('Y',strtotime($beg));
        $a[folder_begin_date_month] = date('m',strtotime($beg));
        $a[folder_begin_date_day] = date('d',strtotime($beg));
        $a[folder_end_date_year] = date('Y',strtotime($end));
        $a[folder_end_date_month] = date('m',strtotime($end));
        $a[folder_end_date_day] = date('d',strtotime($end));
        $a[folder_public] = $this->get_property('folder_public');
        return $a;
    }

    // the validate() function corrects errors in data
    function validate() {
        parent::validate();
        if ($this->get_property(folder_end_date) <
            $this->get_property(folder_begin_date)) {
            $this->set_property(folder_end_date,$this->get_property(folder_begin_date));
        }
    }
}

?>
