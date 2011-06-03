<?php
// $Id: nickname.php,v 1.1 2003/05/03 04:56:42 glen Exp $
// this is an example plugin that adds the property "user_nickname"
// to the user object
$Nickname = new Plugin("Nickname");     // defines the plugin
$Nickname->set_input_property(          // creates a new input property
    'User',                             // defines the class for the property
    array(                              // defines the property's properties
        name => 'user_nickname',
        type => text,
        size => 50,
        prompt => 'Nickname',
        doc => "Enter a nickname; this can be used (if defined) in place of ".
               "the user's full name.")
);
$Nickname->register();                  // registers the plugin
?>
