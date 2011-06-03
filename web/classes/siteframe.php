<?php
// siteframe.php
// $Id: siteframe.php,v 1.54 2007/09/13 03:15:56 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This file defines the core Siteframe class, which provides error-handling
// functions common to all other Siteframe objects
//
// NOTE: the difference between 'fields' and 'properties'. Basically, a 'field'
//   corresponds to a column in the database; you can sort by a field, or query
//   using a field in a WHERE clause. 'properties' are stored as a single XML
//   fragment in the 'user_properties' column. There are unlimited numbers of
//   properties, and they can be modified in real-time, but they are not as
//   functional as fields, though more flexible.

// This defined constant contains a list of properties that are defined in SQL
// columns instead of in the XML property section of the table. The fields
// are separated by columns so that it can be treated as regular expression
// string. 'created' and 'modified' are common properties used by every object
// in the system. A similar property should be defined for all subclasses
// of this base class;

define(SITEFRAME_FIELDS,'created|modified');

// Class: Siteframe
//   This is the root class of the Siteframe object hierarchy. Every other
//   class extends this one. It exists primarily to provide standarized
//   error- and property-handling functions.

class Siteframe {
var $errors,        /* holds an array of error messages */
    $errcount,      /* holds count of errors for this object */
    $_properties,   /* holds various properties */
    $_stack,        /* holds saved properties */
    $_stackptr=0;   /* stack pointer */

    /* Siteframe() - constructor function
    **   sets internal variables to initial values
    */
    function Siteframe() {
        $this->errcount = 0;
        $this->set_property('version',SITEFRAME_VERSION);
    }

    /* errcount() - returns count of errors
    */
    function errcount() {
        return $this->errcount;
    }

    /* add_error(msg) - adds an error message to the list
    */
    function add_error($msg,$a='',$b='',$c='',$d='') {
        if (trim($msg) != '') {
            $this->errors[] = sprintf($msg,$a,$b,$c,$d);
            $this->errcount++;
        }
    }

    /* get_errors() - returns a formatted list of error messages
    */
    function get_errors($delim="<br/>\n") {
        $out = "";
        if ($this->errcount) {
            foreach($this->errors as $line) {
                $out .= $line.$delim;
            }
        }
        return $out;
    }

    /* set_property(prop,value) - set a property
    **   you can override this function to provide specific value checking
    **   for incoming data
    */
    function set_property($prop,$value='',$append=0) {
        global $DEBUG;
        if ($DEBUG)
            logmsg("debug:set_property(%s,%s)",$prop,htmlentities($value));
        if ($append)
            $this->_properties[$prop] .= stripslashes($value);
        else
            $this->_properties[$prop] = stripslashes($value);
    }

    // set_global(var,value,append) - sets a variable
    // but makes it global
    function set_global($var,$value='',$append=0) {
        $this->set_property($var,$value,$append);
        for ($i=$this->_stackptr-1; $i>=0; $i--) {
            if ($append)
                $this->_stack[$i][$var] .= $value;
            else
                $this->_stack[$i][$var] = $value;
        }
    }

    // set_array($arr) - adds all values of array as variable
    function set_array($arr) {
        $this->_properties = array_merge($this->_properties,$arr);
    }

    /* set_xml_properties(xml) - parses the XML string
    **   and sets properties based on it
    */
    function set_xml_properties($xml) {
        $outer = '|<properties>(.*)</properties>|sU';
        $pattern = '|<([a-zA-Z0-9_]+)>(.*)</\1>|sU';
        $cdpat = '|<!\[CDATA\[(.*)\]\]>|sU';
        if (preg_match($outer,$xml,$match)) {
            if (preg_match_all($pattern,$match[1],$vals)) {
                for ($i=0; $i< count($vals[0]); $i++) {
                    $prop = $vals[1][$i];
                    $value = $vals[2][$i];
                    if (preg_match($cdpat,$value,$cdata))
                        $value = $cdata[1];
                    $this->_properties[strtolower($prop)]=stripslashes($value);
                }
            }
        }
    }

    /* get_property(prop) - return a single property value
    */
    function get_property($prop) {
        return $this->_properties[$prop];
    }

    /* get_properties() - returns an array(name,val) of all fields and properties
    */
    function get_properties() {
        return array_merge($this->_properties,$this->get_plugin_output_properties());
    }

    /* get_xml_properties($ignore) - return property string
    **   the string 'ignore' should contain a list of property names to exclude
    **   from the property XML string generation; i.e., if the property resides
    **   in an SQL table, then it doesn't need to be in this list.
    */
    function get_xml_properties($ignore) {

        foreach($this->_properties as $prop => $value) {
            //$prop = $prop;
            if (!preg_match("/$ignore/",$prop)) {
                if (strstr($value,'<'))
                    $out .= "<$prop><![CDATA[$value]]></$prop>";
                else
                    $out .= "<$prop>$value</$prop>";
            }
        }
        return "<properties>$out</properties>";
    }

    // set_input_form_values
    //   takes an array (input_form_values) and prefix, and sets properties
    //   based on the global variable settings
    function set_input_form_values($values,$prefix='') {
        global $_POST,$_FILES;
        foreach($values as $field) {
            $var = $prefix . $field[name];
            switch($field[type]) {
            case 'date':
            case 'datetime':
                $y = "${var}_yr";
                $m = "${var}_mo";
                $d = "${var}_dy";
                $h = "${var}_hr";
                $i = "${var}_mi";
                $val = sprintf("%04d-%02d-%02d %02d:%02d",
                        $_POST[$y],$_POST[$m],$_POST[$d],$_POST[$h],$_POST[$i]);
                $this->set_property($field[name],$val);
                break;
            case 'readonly':
                break;
            case 'file':
                if ($field['optional'] && (trim($_FILES[$var]['name']=='')))
                    break;
                if (isset($field[fcn_val])) {
                    if ($field[fcn_val]($this,$field[name],$_POST[$var]))
                        $this->save_file($field[name],
                            $_FILES[$var]['tmp_name'],
                            $_FILES[$var]['name'],
                            $_FILES[$var]['size'],
                            $_FILES[$var]['type']);
                }
                else {
                    $this->save_file($field[name],
                        $_FILES[$var]['tmp_name'],
                        $_FILES[$var]['name'],
                        $_FILES[$var]['size'],
                        $_FILES[$var]['type']);
                }
                break;
            default:
                if (isset($field[fcn_val])) {
                    if ($field[fcn_val]($this,$field[name],$_POST[$var]))
                        $this->set_property($field[name],$_POST[$var]);
                }
                else {
                    $this->set_property($field[name],$_POST[$var]);
                }
            }
        }
    }

    // notify
    //  send notification emails on successful new object creation
    function notify($class) {
        global $PHP_SELF,$SITE_URL,$SITE_NAME,$SITE_EMAIL,$DB;
        if ($this->errcount())
            return;
        switch($class) {
        case 'document':
            $id = $this->get_property('doc_id');
            break;
        case 'folder':
            $id = $this->get_property('folder_id');
            break;
        case 'user':
            $id = $this->get_property('user_id');
            break;
        case 'group':
            $id = $this->get_property('group_id');
            break;
        default:
            die("Invalid class specified in Siteframe::notify().\n".
                "Please inform the website administrator:\n".
                "$PHP_SELF:classes/siteframe.php");
        }
        $note = new Notification($class,$id);
        if (!$note->errcount())
            $note->send();
    }

    // push - save properties on stack
    function push() {
        // increment stack pointer
        $temp = $this->_properties;
        $this->_stack[$this->_stackptr] = $temp;
        $this->_stackptr++;

        // save current properties
        //foreach($this->_properties as $prop => $value) {
        //    $this->_stack[$this->_stackptr][$prop] = $value;
        //}
    }

    // pop - restore old properties
    function pop() {
        // restore properties
        //foreach($this->_stack[$this->_stackptr] as $prop => $value) {
        //    $this->_properties[$prop] = $value;
        //}
        // decrement stack pointer
        --$this->_stackptr;
        $temp = $this->_stack[$this->_stackptr];
        $this->_properties = $temp;
    }

    // validate() - perform cross-field validation
    function validate() {
      // does not do anything for this class;
      return;
    }

    // custom_properties () - returns additional properties
    // for the current object class
    function custom_properties() {
      global $DB;
      // get plugin-defined properties
      $formvals = $this->get_plugin_input_properties();
      // get user-defined properties
      $q = sprintf("SELECT * FROM obj_props ".
           "LEFT JOIN objs ON (objs.obj_id=obj_props.obj_id) ".
           "WHERE obj_class='%s' ".
           "ORDER BY obj_prop_seq,obj_prop_name",
           get_class($this));
      $r = $DB->read($q);
      while($data = $DB->fetch_array($r)) {
        if (!isadmin() && $data['obj_prop_admin']) {
          //
        }
        else {
          unset($optlist);
          $opts = split(';',$data['obj_prop_options']);
          foreach($opts as $a)
            $optlist[$a] = $a;
          $formvals[] = array(
            name => $data['obj_prop_name'],
            type => $data['obj_prop_type'],
            rval => $data['obj_prop_type']=='checkbox' ? 1 : '',
            size => $data['obj_prop_size'],
            prompt => $data['obj_prop_prompt'],
            doc => $data['obj_prop_doc'],
            options => $optlist,
            value => $this->get_property($data['obj_prop_name'])
          );
        }
      }
      return $formvals;
    }

    // set_custom_properties() - sets the custom properties
    function set_custom_properties($prefix='') {
      global $DB,$_POST;
      $q = sprintf("SELECT * FROM obj_props ".
           "LEFT JOIN objs ON (objs.obj_id=obj_props.obj_id) ".
           "WHERE obj_class='%s' ".
           "ORDER BY obj_prop_seq,obj_prop_name",
           get_class($this));
      $r = $DB->read($q);
      while($data = $DB->fetch_array($r)) {
        $postval = $prefix.$data['obj_prop_name'];
        if (!isadmin() && $data['obj_prop_admin']) {
            // don't set it
        }
        else
            $this->set_property($data['obj_prop_name'],$_POST[$postval]);
      }
    }

    // get_plugin_input_properties - recursively determine plugin properties
    // returns an array of properties
    function get_plugin_input_properties($class='') {
        global  $_SITEFRAME_PLUGINS;
        if (!count($_SITEFRAME_PLUGINS)) return array();
        $props = array();
        foreach($_SITEFRAME_PLUGINS as $plugin) {
            $in_props = $plugin->get_input_properties($this);
            if (count($in_props)) {
                foreach($in_props as $arr) {
                    if (function_exists($arr['fcn_opt']))
                      $arr['options'] = $arr['fcn_opt']($this);
                    $props[] = $arr;
                }
            }
        }
        return $props;
    }

    // get_plugin_output_properties - recursively get all output properties
    function get_plugin_output_properties() {
        global $_SITEFRAME_PLUGINS;
        if (!count($_SITEFRAME_PLUGINS)) return array();
        $props = array();
        foreach($_SITEFRAME_PLUGINS as $plugin) {
            $out_props = $plugin->get_output_properties($this);
            if (count($out_props)) {
                foreach($out_props as $name => $value) {
                    $props[$name] .= $value;
                }
            }
        }
        return $props;
    }

    // trigger_event() - executes trigger functions
    // now, really, this should create a new Trigger object
    // and call through that. But, for performance sake,
    // we're going to bypass that and go directly to the
    // array of functions
    function trigger_event($class,$event,$opt=0) {
        global $_TRIGGER_FCN;
        $functions = $_TRIGGER_FCN[$class][$event];
        if (count($functions)) {
            foreach($functions as $fname) {
                $fname($this,$class,$event,$opt);
            }
        }
    }

    // save_file - moves an uploaded file
    function save_file($property,$src_file,$dst_file,$size,$mimetype,$path='') {
        global $MAX_FILE_SIZE,$CURUSER;

        // set allowable file extensions
        $EXT['image/gif'] = '.gif';
        $EXT['image/x-png'] = '.png';
        $EXT['image/png'] = '.png';
        $EXT['image/pjpeg'] = '.jpg';
        $EXT['image/jpeg'] = '.jpg';
        $EXT['image/jpg'] = '.jpg';

        // check for illegal file extensions
        if (preg_match(sprintf('/(%s)$/',NOUPLOADFILES),strtolower($dst_file))) {
          $this->add_error('Uploading of executable content is not allowed');
          return;
        }

        // check for logged-in user
        if (!$CURUSER)
            return;
        $uid = $CURUSER->get_property('user_id');

        // check for a valid destination filename
        if ($dst_file == '') {
            if (get_class($this)=='user')
                return;
            $this->add_error(_ERR_NOFILE);
            return;
        }

        // verify that the requested file is an uploaded file
        if (!is_uploaded_file($src_file)) {
            $this->add_error(_ERR_BADFILE,$dst_file);
            return;
        }

        // check for maximum file size
        if ($MAX_FILE_SIZE) {
          if (($size/1024) > $MAX_FILE_SIZE) {
            $this->add_error(_ERR_BADSIZE,$MAX_FILE_SIZE,($size / 1024),$dst_file);
            return;
          }
        }

        // has a path been provided?
        if ($path != '') {
            $newname = $path . siteframe_filename($dst_file,$EXT[$mimetype]);
        }
        else {
            // create the target directory name
            $newdir = siteframe_userdir($uid);
            if (!is_dir($newdir)) {
                if (!mkdir($newdir,0777)) {
                    $this->add_error(_ERR_BADDIR,$newdir);
                }
            }

            // create the new filename
            $newname = $newdir . siteframe_filename($dst_file,$EXT[$mimetype]);
            while (file_exists($newname)) {
                $newname = sprintf("%s%d%s",
                            $newdir,
                            ++$count,
                            siteframe_filename($dst_file,$EXT[$mimetype]));
            }

            // remove the old file, if possible
            if ($this->get_property('doc_id')) {
                @unlink($this->get_property($property));
                //$this->set_property($property,'');
            }
        }

        // move the file
        move_uploaded_file($src_file,$newname);
        chmod($newname, 0666);

        // check to see that it got there
        if (!file_exists($newname)) {
            $this->add_error(_ERR_BADMOVEFILE,$dst_file);
        }
        else {
            $this->set_property("${property}_mime_type",$mimetype);
            $this->set_property("${property}_size",filesize($newname));
            $this->set_property($property,$newname);
        }
    }

    // function resize_image
    // creates a scaled-down version of an image
    // returns the new file name
    // if crop==0, then don't scale, just crop out the center of an image
    function resize_image($filename,$mimetype,$res,$crop=0) {
        global $IMAGE_QUALITY,$IMAGE_PROOF;

        ini_set("memory_limit", "32M");
        $USE_GD18 = !function_exists(ImageCreateTrueColor);

        // check the size of the original image
        $size = GetImageSize($filename);
        $width = $size[0];
        $height = $size[1];

        // if either is 0, something's wrong
        if ($width==0||$height==0)
            $this->add_error(_ERR_BADIMAGE,$filename);

        // if both the with and the height are less than the
        // requested resolution, then just return the original image
        if (($width <= $res) && ($height <= $res))
            return $filename;

        // make sure IMAGE_QUALITY exists
        if (!$IMAGE_QUALITY)
            $IMAGE_QUALITY = 90;

        // check the mime type and construct a mental image
        switch($mimetype) {
        case 'image/pjpeg':
        case 'image/jpeg':
            $im = ImageCreateFromJPEG($filename);
            break;
        case 'image/gif':
            $im = ImageCreateFromGIF($filename);
            break;
        case 'image/x-png':
        case 'image/png':
            $im = ImageCreateFromPNG($filename);
            break;
        default:
            $this->add_error(_ERR_BADMIME,$mimetype);
            return "";
        }

        // finally, resize everything
        $x = ImageSX($im);
        $y = ImageSY($im);
        if ($x > $y)
            $scale = $res / $x;
        else
            $scale = $res / $y;
        $new_w = $x * $scale;
        $new_h = $y * $scale;
        if ($scale > 1) {
            return $filename;
        }
        else {
            // generate the new filename
            if ($crop) {
                $resized_file = preg_replace('/\.([^\.]+)$/',
                                    sprintf('%04dC.\1',$res),
                                    $filename);
            }
            else {
                $resized_file = preg_replace('/\.([^\.]+)$/',
                                    sprintf('%04d.\1',$res),
                                    $filename);
            }
            if ($crop) {
                if ($USE_GD18)
                    $out = ImageCreate($res,$res);
                else
                    $out = ImageCreateTrueColor($res,$res);
                ImageCopy($out,$im,0,0,($x/2)-($res/2),($y/2)-($res/2),$res,$res);
            }
            else {
                if ($USE_GD18) {
                    $out = ImageCreate($new_w,$new_h);
                    ImageCopyResized($out,$im,0,0,0,0,$new_w,$new_h,$x,$y);
                }
                else {
                    $out = ImageCreateTrueColor($new_w,$new_h);
                    ImageCopyResampled($out,$im,0,0,0,0,$new_w,$new_h,$x,$y);
                }
                ImageInterlace($out, 1);
            }

            // add "proof" to image
            if ($IMAGE_PROOF && ($res > 150)) {
              $size = min($new_h,$new_w);
              $color = 0xDDDDDD;
              $rootpath = $_SERVER['PATH_TRANSLATED'];
              $rootpath = substr($rootpath,0,strrpos($rootpath,'/'));
              ImageAlphaBlending($out,TRUE);
              ImageColorAllocateAlpha($out,0xDD,0xDD,0xDD,50);
              ImageTtfText(
                $out,             // output image
                $size/4,          // size
                30,               // angle
                $new_w/5,         // X
                $new_h-($size/5), // Y
                $color,           // color
                $rootpath.'/georgia.ttf',  // font file
                'Proof'           // text
              );
            }

            // save the file
            switch($mimetype) {
            case "image/gif":
                ImageGIF($out, $resized_file);
                break;
            case "image/x-png":
            case "image/png":
                ImagePNG($out, $resized_file);
                break;
            default:
                ImageJPEG($out, $resized_file, $IMAGE_QUALITY);
            }
            //chmod($resized_file,0777);
            ImageDestroy($out);
        }
        ImageDestroy($im);

        // finally, return the new filename
        return $resized_file;
    }

}

?>
