<?php
// Plugin class definition
// $Id: plugin.php,v 1.14 2004/10/24 05:33:51 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All Rights Reserved.
// See LICENSE.txt for licensing details.

// plugins are stored as object variables in the global
// array $_SITEFRAME_PLUGINS, indexed by the plugin name

// Plugin methods:
// ->set_input_property         : defines a new input property (form variable)
// ->set_output_property        : defines a new output (read-only) property
// ->get_input_properties       : return an array of input properties
// ->get_output_properties      : return an array of display properties
// ->set_autoblock              : defines a new autoblock
// ->set_template               : defines a new template
// ->set_macro                  : defines a new macro
// ->set_global                 : defines a new global property
// ->register()                 : "fixes" the plugin
//   register() must be called as the last method AFTER setting all other
//   plugin properties and values

class Plugin extends Siteframe {
var $input_properties,$output_properties,$plugin_name;

    // Plugin - create and register a new plugin
    function Plugin($name) {
        $this->plugin_name = $name;
    }

    // set_input_property
    // defines a new input property (form variable) for a class
    function set_input_property($class,$formarray) {
        $this->input_properties[strtolower($class)][] = $formarray;
    }

    // set_output_property
    // defines a new output (read-only) property
    function set_output_property($class,$formarray) {
        $this->output_properties[strtolower($class)][] = $formarray;
    }

    // set_autoblock
    // defines a new autoblock
    function set_autoblock($name,$select) {
        global $AUTOBLOCK;
        $AUTOBLOCK[$name] = $select;
    }

    // set_template
    // defines a new template
    function set_template($name,$filename) {
        global $TEMPLATES;
        $TEMPLATES[$name] = $filename;
    }

    // set_macro
    // defines a new macro
    function set_macro($name,$value) {
        global $MACROS;
        $MACROS[$name] = $value;
    }

    // set_trigger
    // defines a callback for a triggered event
    function set_trigger($class,$event,$callback) {
        $tr = new Trigger(0,'',$class,$event);
        $tr->add($callback);
        $this->add_error($tr->get_errors());
    }

    // set_global
    // defines a new global property
    function set_global($category,$name,$formarray) {
       global $CPGLOBAL;
       $CPGLOBAL[$category][$name] = $formarray;
    }

    // get_input_properties
    // returns all input properties for a class and parent classes
    // array is returned in standard input_form arrangement
    function get_input_properties(&$obj,$class='') {
        if ($class=='') $class=get_class($obj);
        $parent = get_parent_class($class);
        $props = $this->input_properties[strtolower($class)];
        if (is_array($props)) {
            foreach($props as $n => $v) {
                $props[$n][value] = $obj->get_property($props[$n][name]);
            }
        }
        else
            $props = array();
        if (is_array($props) && ($parent != '')) {
            return array_merge($this->get_input_properties($obj,$parent),$props);
        }
        else {
            return $props;
        }
    }

    // get_output_properties
    // returns (and instantiates) all display properties for a class
    // array is returned in the form array[name] = value
    function get_output_properties(&$obj,$class='') {
        if ($class=='') $class=get_class($obj);
        $parent = get_parent_class($class);
        $outprops = array();
        $props = $this->output_properties[strtolower($class)];
        if (count($props) > 0) {
            foreach($props as $n => $v) {
                $callback = $props[$n]['callback'];
                if ($callback!='')
                    $outprops[$props[$n]['name']] = $callback($obj);
                else
                    $outprops[$props[$n]['name']] = $props[$n]['value'];
            }
        }
        if ($parent!='')
            return array_merge($this->get_output_properties($obj,$parent),$outprops);
        else
            return $outprops;
    }

    // register()
    function register() {
        global $_SITEFRAME_PLUGINS;
        if ($this->errcount()) {
            siteframe_abort($this->get_errors());
        }
        $_SITEFRAME_PLUGINS[$this->plugin_name] =& $this;
    }

}
?>
