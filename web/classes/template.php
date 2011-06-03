<?php
// template.php
// $Id: template.php,v 1.115 2003/09/22 03:03:53 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This file defines the Template class, which is used to generate
// page content via a templatizing process.
//
// Requires macro support (macros.php)

define(FOCUSSTR,<<<ENDFOCUSSTR
<script language="javascript">
%s.%s.focus();
</script>
ENDFOCUSSTR
);

class Template extends Siteframe {
var
  $path,                // path to template files
  $_templates,
  $_temp;

    // Template - constructor function; creates a new template
    function Template($name='',$text = '') {
        if ($name) {
            $this->_properties[$name] = $text;
            $this->path = '.';
        }
        $this->_properties['template_cvs_version'] =
            '$Id: template.php,v 1.115 2003/09/22 03:03:53 glen Exp $';
    }

    // load_file(name,filename) - loads a file as the template
    function load_file($name,$filename) {
        global $CURUSER;
        $pos = strpos($filename,'..');
        if ($pos === FALSE) {
            // ok
        }
        else {
            logmsg('SECURITY:attempt to access external file %s',$filename);
            if ($CURUSER)
              logmsg('Current user id=%d name=%s',
                $CURUSER->get_property('user_id'),
                $CURUSER->get_property('user_name'));
            siteframe_abort('SECURITY:Attempt to breach security by accessing files outside the web root directory');
        }
        if(trim($filename)=='')
            siteframe_abort('No template file specified!');
        $this->_properties[$name] = '';
        $fullfname = $this->path . '/' . $filename;
        $fp = fopen($fullfname,'r');
        if (!$fp) {
            siteframe_abort(_ERR_NOTEMPLATE,$fullfname);
        }
        while(!feof($fp)) {
            $this->_properties[$name] .= fgets($fp,2048);
        }
        fclose($fp);
    }

    // load_template(name,tplname)
    function load_template($name,$tplname) {
      $this->set_property($name,$this->get_template_body($tplname));
    }

    // set_path(newpath) - set path to template files
    function set_path($newpath) {
        $this->path = $newpath;
    }

    // input_form - this uses an array of properties as set
    //   by the various document objects. For each property, it creates several
    //   template variables:
    //      {input_form_prompt} the prompt string
    //      {input_form_field}  the field HTML
    //      {hidden_form_field} a hidden field's HTML
    function input_form($var,$a,$prefix='',$submit='') {
        global $DOCSTRINGS,$SITE_PATH;
        // erase existing values
        $this->set_property('input_form','');
        $this->set_property('input_form_hidden','');

        // generate a form name if necessary
        if ($this->get_property('form_name')=='')
          $this->set_property('form_name','noformname');

        // create a unique template variable name
        $formname = sprintf("_inform%08d",rand());
        $this->set_property($formname,$this->get_property('_in_form'));

        // _in_form is loaded in ./web/siteframe.php
        $this->block($formname,'input_form','input_form_item');
        $this->block($formname,'input_form_hidden','input_form_hidden_item');
        foreach($a as $field) {
            $this->set_property('input_form_name',$field[name]);
            if ($field[type] != 'readonly') {
                $this->set_property('input_form_prompt',$field[prompt]);
                if ($field[help]!='') {
                  $this->set_property('input_form_prompt',
                    sprintf(' [<a href="#" '.
                      'onClick="javascript:window.open(\'%s/help.php?#%s\','.
                      '\'help\',\'height=400,width=300,scrollbars=yes\');">?</a>]',
                      $SITE_PATH,
                      $field[help]),
                    TRUE
                  );
                }
                $this->set_property('input_form_type',$field[type]);
                if ($field[doc]=='')
                  $this->set_property('input_form_doc',$DOCSTRINGS[$field[name]]);
                else
                  $this->set_property('input_form_doc',$field[doc]);
            }
            // if the field has no value, check for a session variable
            if ((trim($field[value])=='')&&(session_id()!='')) {
              $field[value] = $_SESSION[$prefix.$field[name]];
            }
            $infield = $this->input_form_field($field,$prefix);
            if ($field[focus] && ($this->get_property('form_name')!=''))
              $infield .= $this->focus($prefix.$field[name]);
            if ($field[type] == 'hidden') {
                $this->set_property(hidden_form_field,$infield);
                $this->set_property(input_form_hidden,
                  $this->parse(input_form_hidden_item),true);
            }
            else {
                $this->set_property(input_form_field,$infield);
                $this->set_property(input_form,$this->parse(input_form_item),true);
            }
        }
        if ($submit == '')
            $this->set_property('input_form_submit','Submit');
        else
            $this->set_property('input_form_submit',$submit);
        $this->set_property($var,$this->parse($formname));
    }

    // input_form_field(field)
    // process one field
    function input_form_field($field,$prefix='') {
        switch($field[type]) {

        case 'sidebyside':
            // recursive array of subfields
            $infield = "<table class=\"fieldarray\">\n";
            for($i=1; $i<=$field[count]; $i++) {
                $row = '<tr>';
                foreach($field[fields] as $newfield)
                    $row .= "  <td>" . $newfield[prompt] . ": " .
                            $this->input_form_field($newfield) . "</td>\n";
                $row .= "</tr>\n";
                $infield .= $row;
            }
            $infield .= "</table>\n";
            break;

        case 'hr':
            $infield = "<hr/>";
            break;

        case 'checkbox':
            $infield = sprintf("<input type=\"checkbox\" name=\"$prefix%s\" value=\"%s\"%s%s/>",
                            $field[name],$field[rval],($field[value]+0 ? ' checked="checked"' : ''),
                            ($field[disabled] ? ' disabled="disabled"' : ''));
            break;

        case 'checkboxarray': // an array of check boxes
            // this uses the 'options' array, like select
            foreach($field[options] as $value => $prompt) {
                $infield .=
                    sprintf('<input type="checkbox" name="%s%s[]" value="%s"%s/>&nbsp;%s<br/>'."\n",
                        $prefix,
                        $field[name],
                        $value,
                        ($field[values][$value]==$value) ? ' checked="checked"':'',
                        $prompt);
            }
            break;

        case 'textarray': // an array of text boxes
            // this uses the 'options' array, like select
            foreach($field[options] as $value => $prompt) {
                $infield .=
                    sprintf('<input type="text" name="%s%s[]" value="%s"/> %s<br/>'."\n",
                        $prefix,
                        $field[name],
                        $field[values][$value],
                        $prompt);
            }
            break;

        case 'date':
        case 'datetime':
            if (trim($field[value]) != '')
                $dtval = getdate(strtotime($field[value]));
//printf("<!-- dtval=%s(%d) -->",$field[value],strtotime($field[value]));
            $infield = sprintf("<input type=\"text\" name=\"$prefix%s_yr\" value=\"%s\" size=\"5\" maxlength=\"5\"/>\n",
                $field[name],$dtval[year] ? $dtval[year] : '');
            $mon = array(
                "Month" => 0,
                "January" => 1,
                "February" => 2,
                "March" => 3,
                "April" => 4,
                "May" => 5,
                "June" => 6,
                "July" => 7,
                "August" => 8,
                "September" => 9,
                "October" => 10,
                "November" => 11,
                "December" => 12
            );
            $infield .= sprintf("<select name=\"%s_mo\">\n",$field[name]);
            foreach($mon as $n => $v) {
                $infield .= sprintf("<option value=\"%d\"%s>%s</option>\n",$v,
                    $dtval[mon] == $v ? " selected" : "",
                    $n);
            }
            $infield .= "</select>\n";
            $infield .= sprintf("<select name=\"%s_dy\">\n",$field[name]);
            $infield .= "<option value=\"0\">Day</option>\n";
            for($i=1;$i<=31;$i++) {
                $infield .= sprintf("<option value=\"%d\"%s>%02d</option>\n",$i,
                    $dtval[mday]==$i ? ' selected="selected"' : "",
                    $i);
            }
            $infield .= "</select>\n";
            if ($field[type] == "datetime") {
                $infield .= sprintf("<select name=\"%s_hr\">\n",$field[name]);
                for($i=0;$i<24;$i++)
                    $infield .= sprintf("<option value=\"%d\"%s>%02d</option>\n",
                        $i,$dtval[hours]==$i ? ' selected="selected"' : "",$i);
                $infield .= "</select>\n";
                $infield .= sprintf("<select name=\"%s_mi\">\n",$field[name]);
                for($i=0;$i<60;$i+=15)
                    $infield .= sprintf("<option value=\"%d\"%s>%02d</option>\n",
                        $i,$dtval[minutes]==$i ? ' selected="selected"' : "",$i);
                $infield .= "</select>\n";
            }
            break;

        case 'password':
        case 'text':
            if ($field[size] < 30)
                $sizeval = sprintf(' size="%d" ',$field[size]+1);
            else
                $sizeval = ' size="30" ';
            $infield = sprintf("<input type=\"%s\" name=\"$prefix%s\" $sizeval maxlength=\"%s\" value=\"%s\"%s/>",
                            $field[type],$field[name],$field[size],
                            htmlentities($field[value]),
                            ($field[disabled] ? ' disabled="disabled"' : ''));
            break;

        case 'file':
            $infield = sprintf("<input type=\"file\" name=\"$prefix%s\"%s/>",
                            $field[name],($field[disabled] ? ' disabled="disabled"' : ''));
            break;

        case 'hidden':
            $infield = sprintf("<input type=\"hidden\" name=\"$prefix%s\" value=\"%s\"/>",
                            $field[name],$field[value]);
            break;

        case 'radio':
            $infield = sprintf("<input type=\"radio\" name=\"$prefix%s\" value=\"%s\"/> %s",
                            $field[name],$field[value],$field[context]);
            break;

        case 'select':
            $optlist = '';
            if (!count($field[options]))
              siteframe_abort('Field %s has no options',$field[name]);
            foreach($field[options] as $code => $prompt) {
                $optlist .= sprintf("  <option value=\"%s\"%s>%s</option>\n",
                                $code,(($code==$field[value]) ? ' selected="selected"' : ""),$prompt);
            }
            $infield = sprintf("<select name=\"$prefix%s\"%s>\n$optlist</select>",
                                $field[name],($field[disabled] ? ' disabled="disabled"' : ''));
            break;

        case 'textarea':
            $infield = sprintf("<textarea name=\"$prefix%s\" class=\"%s\" rows=\"%d\" cols=\"%d\"%s>%s</textarea>",
                            $field[name],"inputbox",
                            ($field[rows]+0 ? $field[rows] : 5),
                            ($field[cols]+0 ? $field[cols] : 50),
                            ($field[disabled] ? ' disabled' : ''),
                            htmlentities($field[value]));
            break;

        case 'readonly':
        case 'ignore':
            break;

        default:
            siteframe_abort('Unrecognized form type %s',$field[type]);
        }
        return $infield;
    }

    // focus - set the focus to the current form
    function focus($fieldname) {
      if ($this->get_property('form_name')=='')
        return '';
      return sprintf(
        FOCUSSTR,
        $this->get_property('form_name'),
        $fieldname
      );
    }

    // block(parent,block,var) - replace {BEGIN:block} in parent with {block},
    //   move contents of block to {var}
    function block($parent,$block,$var,$ignore=0) {
        //$searchfor = "\{BEGIN:$block\}(.+)\{END:$block\}";
        //if (ereg($searchfor,$this->_properties[$parent],$match)) {
        $searchfor = "/\{BEGIN:$block\}(.+)\{END:$block\}/ms";
        //$searchfor = sprintf('/\{BEGIN\:%s\}(.+)\{END:%s\}/ms',$block,$block);
        if (preg_match($searchfor,$this->_properties[$parent],$match)) {
            $this->set_property($var,$match[1]);
            $this->set_property($parent,preg_replace($searchfor,"{".$block."}",$this->_properties[$parent]));
        }
        else if (!$ignore) {
            siteframe_abort(_ERR_NOBLOCK,$block,$parent);
        }
    }

    // autoblock(parent) - search parent for auto-blocks
    //   and replace with queries from $AUTOBLOCK
    function autoblock($parent) {
        // $pat = '/\{BEGIN:([^\s\}]+)\s*([^\s\}]*|\'[^\']*\'|"[^"]*")\s*(|[^\s\}]*)\s*\}(.*?)\{END:\1\}/s';
//good one: $pat = '/\{BEGIN:([^\s\}]+)\s*(\'[^\']*\'|"[^"]*"|[^\s\}]*)\s*(|[^\s\}]*)\s*\}(.*?)\{END:\1\}/s';
$pat = '/\{BEGIN:([^\s\}]+)\s*(\'[^\']*\'|"[^"]*"|[^\s\}]*)\s*(|[^\s\}]*)\s*\}(.*?)\{END:\1\}/s';

        $out = preg_replace_callback(
          $pat,
          autoblock_replace,
          $this->get_property($parent)
        );
        $this->set_property($parent,$out);
    }

    // table(output,result,offset,self)
    //   using the DB result, generate a table for file 'self' and store it
    //   in 'output'
    function table($output,$result,$offset,$self) {
        global $DB,$LINES_PER_PAGE,$FIELDS;
        $this->set_global(recordset,recordset($result,$self,$offset,$LINES_PER_PAGE));
        $pos = 0;
        while ($pos < $offset) {
            $DB->fetch_array($result);
            $pos++;
        }
        $pos = 0;
        $out = "<table>\n";
        $n = $DB->num_fields($result);
        while(($arr = $DB->fetch_array($result)) && ($pos++ < $LINES_PER_PAGE)) {
            if ($pos == 1) {
                $out .= "<tr>";
                for($i=0;$i<$n;$i++) {
                    $name = $FIELDS[$DB->field_name($result,$i)];
                    if (trim($name) == '')
                        $name = $DB->field_name($result,$i);
                    $out .= "<th>$name</th>\n";
                }
                $out .= "</tr>\n";
            }
            if ($pos % 2)
                $out .= "<tr class=\"odd\">\n";
            else
                $out .= "<tr class=\"even\">\n";
            for($i=0;$i<$n;$i++) {
                $out .= sprintf("<td class=\"%s\">",$DB->field_name($result,$i));
                $out .= $arr[$i];
                $out .= "</td>\n";
            }
            $out .= "</tr>\n";
            }
        $out .= "</table>\n";
        $this->set_property($output,$out);
    }

    // table_block(parent,block,var,class,result,offset,self,prefix)
    //   using the DB result, generate a block for file 'self' and store it
    //   in 'output' using class and the block information
    function table_block($parent,$block,$var,$class,$result,$offset,$self,$prefix='',$ipp=0) {
        global $DB,$LINES_PER_PAGE,$FIELDS;
        if (!$ipp)
            $ipp = $LINES_PER_PAGE;
        $this->block($parent,$block,$var);
        $this->set_global(recordset,recordset($result,$self,$offset,$ipp));
        $pos = 0;
        while ($pos < $offset) {
            $DB->fetch_array($result);
            $pos++;
        }
        $pos = 0;
        $n = $DB->num_fields($result);
        $count = 0;
        while((list($id) = $DB->fetch_array($result)) && ($pos++ < $ipp)) {
            ++$count;
            $this->set_property(row_number,$count);
            $this->set_property(row_class,($count%2 ? "odd" : "even"));
            $obj = new $class($id);
            foreach($obj->get_properties() as $name => $value) {
                $this->set_property("$prefix$name",$value);
            }
            $this->set_property($block,$this->parse($var),true);
        }
    }

    // get_template_body(name)
    function get_template_body($name) {
      if (isset($this->_templates[$name])) {
        return $this->_templates[$name];
      }
      else {
        if ($this->load_content_template($name))
          return $this->_templates[$name];
        else
          return sprintf('{template ERROR:"%s" not found}',$name);
      }
    }

    // parse(var) - parses variables, replacing all values
    function parse($var) {

      // abort if VAR is not set
      if (!isset($this->_properties[$var])) {
        siteframe_abort(_ERR_NOTPLVAL,$var);
      }

      //set a variable for mime_timecode
      $this->set_property(mime_timecode,md5(time()));

      // parse autoblocks
      $this->autoblock($var);

      // by performing the macro function BEFORE the parsing, this eliminates
      // extra macro delimiters in the expanded macro fields
      $out = macro($this->get_property($var));

      // this pattern will replace all variables like this:
      //   {varname}
      // or this:
      //   {varname:format}
      // where 'format' is a valid printf/sprintf format string
      // the variable name or format cannot contain a ! character

      $out =  preg_replace_callback(
        "/{([^\s!%}:]+)(|:([^!}]+))}/",
        array($this,parse_callback),
        $out
      );

      return $out;
    }

    // callback function for preg_replace_callback
    function parse_callback($arr) {
        global $TPL_FLAG_UNDEFINED,$DB,$TRUNCATE_SIZE,$SYMBOLS;
        $v = $arr[1];

        // parse the {template:name} slot
        if ($arr[1] == 'template') {
            // this gets pretty complicated
            $s = $this->get_template_body($arr[3]);
            // if we don't have an {template:x} slots, just return it
            if (!preg_match('/{template:\w+}/',$s))
              return $s;
            // otherwise, recursively replace the templates
            else
              return preg_replace_callback(
                "/{([^\s!%}:]+)(|:([^!}]+))}/",
                array($this,parse_callback),
                $s
              );
        }
        else if ((!isset($this->_properties[$v]))&&$TPL_FLAG_UNDEFINED)
            return sprintf('&#123;%s:UNDEFINED}',$v);
        else if ($arr[2]!='') {
            switch(strtolower($arr[3])) {
            case 'noquote':
                return str_replace('"','&quot;',nl2br($this->parse($v)));
            case 'nohtml':
                return wordwrap(htmlspecialchars($this->parse($v)));
            case 'striptags':
                return wordwrap(strip_tags($this->parse($v)));
            case 'truncate':
                $x = strip_tags($this->parse($v));
                if (strlen($x) > ($TRUNCATE_SIZE ? $TRUNCATE_SIZE : 50)) {
                  $x = substr($x,0,$TRUNCATE_SIZE ? $TRUNCATE_SIZE : 50) . "...";
                }
                return $x;
            default:
                return sprintf($arr[3],$this->_properties[$v]);
            }
        }
        else if ($v == '%%LIST%%') {
            $x = $this->_properties;
            ksort($x);
            foreach($x as $name => $value) {
                $out .= sprintf("<b>%s</b> = %s<br/>\n",$name,htmlentities($value));
            }
            return $out;
        }
        else {
            switch($v) {
            case 'num_users':
                $r = $DB->read("SELECT COUNT(*) FROM users");
                list($num_users) = $DB->fetch_array($r);
                return $num_users;
                break;
            case 'num_documents':
                $r = $DB->read("SELECT COUNT(*) FROM docs");
                list($num_documents) = $DB->fetch_array($r);
                return $num_documents;
                break;
            case 'num_folders':
                $r = $DB->read("SELECT COUNT(*) FROM folders");
                list($num_folders) = $DB->fetch_array($r);
                return $num_folders;
                break;
            case 'comment_body':
            case 'doc_comment_body':
            case 'doc_summary':
            case 'doc_doc_summary':
            case 'doc_body':
            case 'doc_doc_body':
            case 'email_body':
            case 'folder_body':
            case 'doc_folder_body':
            case 'category_description':
            case 'user_description':
            case 'user_user_description':
            case 'group_body':
            case 'group_group_body':
            case 'note_body':
                return parse_text($this->_properties[$v]);
            default:
                if (($SYMBOLS[$v]!='')&&$this->_properties[$v])
                  return $SYMBOLS[$v];
                else if ($SYMBOLS[$v]!='')
                  return '';
                else
                  return $this->_properties[$v];
            }
        }
    }

    // pparse(var) - parse and print parsed variable
    function pparse($var) {
      print($this->parse($var));
    }

    // load_theme - load all theme templates
    // load 'default' if not found
    function load_theme($theme) {
      global $DB;
      $querystring =
        'SELECT tpl_name,tpl_body FROM templates INNER JOIN themes '.
        'ON (tpl_theme_id=theme_id) '.
        'WHERE theme_name=\'%s\'';
      $q = sprintf($querystring,addslashes($theme));
      $r = $DB->read($q);
      if (!$DB->num_rows($r)) {
        $q = sprintf($querystring,'default');
        $r = $DB->read($q);
      }
      while(list($name,$body) = $DB->fetch_array($r)) {
        $this->_templates[stripslashes($name)] = stripslashes($body);
      }
      $this->set_property('page',       $this->_templates[TPL_PAGE]);
      $this->set_property('popup',      $this->_templates[TPL_POPUP]);
      $this->set_property('_in_form',   $this->_templates[TPL_FORM]);
      $this->set_property('_poll_form', $this->_templates[TPL_FORM]);
    }

    // load_content_template() - load a content template
    function load_content_template($name) {
      global $DB;
      $q = 'SELECT tpl_body FROM templates WHERE tpl_theme_id=0 AND tpl_name=\'%s\'';
      $r = $DB->read(sprintf($q,addslashes($name)));
      if (!$DB->num_rows($r))
        return 0;
      list($body) = $DB->fetch_array($r);
      $this->_templates[$name] = stripslashes($body);
      return 1;
    }

}

?>
