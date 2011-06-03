<?php
// uploadedfile.php
// $Id: uploadedfile.php,v 1.2 2003/06/05 05:37:36 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
//
// defines a class for uploaded files

class uploadedfile extends Siteframe {

    // create an input form
    function input_form_values() {
        $a = array(
            array(
                name => 'destination',
                type => 'select',
                options => array(
                    "../images/" => "Images ($SITE_PATH/images",
                    "../files/"  => "Files ($SITE_PATH/files)",
                    "../macros/" => "Macros ($SITE_PATH/macros)",
                    "../plugins/"=> "Plugins ($SITE_PATH/plugins)",
                    "../"        => "Web root ($SITE_PATH/)",
                    "../admin/"  => "Admin ($SITE_PATH/admin/)"
                ),
                value => '../images/',
                prompt => 'Destination path',
                doc => 'Select where you would like the file to end up'
            ),
            array(
                name => 'userfile',
                type => 'file',
                prompt => 'File',
                doc => 'Select the file to upload on your local filesystem'
            ),
            array(
                name => 'ignored',
                type => 'hidden',
                value => 'Ignore the man behind the curtain'
            )
        );
        return $a;
    }

    // sets input values
    function set_input_form_values($values,$prefix='') {
        global $_POST,$_FILES;
        foreach($values as $field) {
            $var = $prefix . $field[name];
            switch($field[name]) {
            case 'userfile':
                $this->save_file($field[name],
                    $_FILES[$var]['tmp_name'],
                    $this->get_property('destination').$_FILES[$var]['name'],
                    $_FILES[$var]['size'],
                    $_FILES[$var]['type']);
                break;
            default:
                $this->set_property($field[name],$_POST[$var]);
            }
        }
    }

    // save_file - moves an uploaded file someplace
    function save_file($property,$src_file,$dst_file,$size,$mimetype) {

        // verify that the requested file is an uploaded file
        if (!is_uploaded_file($src_file)) {
            $this->add_error(_ERR_BADFILE,$dst_file);
            return;
        }

        // remove any existing file
        @unlink($dst_file);

        // move the file
        move_uploaded_file($src_file,$dst_file);

        // check to see that it got there
        if (!file_exists($dst_file)) {
            $this->add_error(_ERR_BADMOVEFILE,$dst_file);
        }

        parent::set_property($property,$dst_file);
    }

}
?>
