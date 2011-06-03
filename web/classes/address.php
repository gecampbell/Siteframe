<?php
// address.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: address.php,v 1.3 2003/05/06 22:16:50 glen Exp $
//
// Defines the Address class, for holding a person's name and address information

class Address extends Document {

    // Address(id) - constructor
    function Address($id=0) {
        parent::Document($id);
        $this->set_property(doc_type,'Address');
    }
    
    // input_form_values() - return stuff for an input form
    function input_form_values() {
    }
}

?>
