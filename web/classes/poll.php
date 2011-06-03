<?php
// poll.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: poll.php,v 1.16 2004/10/17 17:19:22 glen Exp $
//
// defines the Poll class

define(MAX_POLL_OPTIONS,10); // maximum number of options in a poll

class Poll extends Document {

    // Poll - constructor
    function Poll($id=0, $dbrow=0) {
        parent::Document($id, $dbrow);
        $this->set_property(doc_type,'Poll');
    }

    // delete()
    function delete() {
        global $DB;
        $DB->write("DELETE FROM poll_votes WHERE doc_id=".$this->get_property(doc_id));
        parent::delete();
    }

    // set_property(name,value) - error-checking
    function set_property($name,$value) {
        switch($name) {
        case 'poll_opt_1':
        case 'poll_opt_2':
            if (clean($value)=='') {
                $this->add_error(_ERR_BADPOLL);
            }
            else
                parent::set_property(clean($name),clean($value));
            break;
        case 'poll_opt_3':
        case 'poll_opt_4':
        case 'poll_opt_5':
        case 'poll_opt_6':
        case 'poll_opt_7':
        case 'poll_opt_8':
        case 'poll_opt_9':
        case 'poll_opt_10':
        case 'poll_opt_11':
        case 'poll_opt_12':
        case 'poll_opt_13':
        case 'poll_opt_14':
        case 'poll_opt_15':
        case 'poll_opt_16':
        case 'poll_opt_17':
        case 'poll_opt_18':
        case 'poll_opt_19':
        case 'poll_opt_20':
            if (clean($value)!='') {
                parent::set_property(clean($name),clean($value));
            }
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // get_properties() - add virtual property poll_display
    function get_properties() {
        global $CURUSER,$DB;
        $a = parent::get_properties();
        if ($CURUSER) {
            if ($_GET['revote']) {
                $q = sprintf("DELETE FROM poll_votes WHERE doc_id=%d AND user_id=%d",
                        $this->get_property(doc_id),
                        $CURUSER->get_property(user_id));
                $DB->write($q);
            }
            $q = sprintf("SELECT COUNT(*) FROM poll_votes ".
                         "WHERE doc_id=%d AND user_id=%d",
                         $this->get_property(doc_id),
                         $CURUSER->get_property(user_id));
            $r = $DB->read($q);
            list($num) = $DB->fetch_array($r);
            if ($num)
                $a[poll_display] = $this->poll_results();
            else
                $a[poll_display] = $this->poll_input_form();
        }
        else
            $a[poll_display] = $this->poll_input_form();
        return $a;
    }

    // poll_results() - display poll results
    function poll_results() {
        global $DB,$PAGE,$TEMPLATES;
        $q = sprintf("SELECT COUNT(*) FROM poll_votes WHERE doc_id=%d",
                     $this->get_property(doc_id));
        $r = $DB->read($q);
        list($total) = $DB->fetch_array($r);
        $PAGE->load_template(_results_,$TEMPLATES[PollResults]);
        $PAGE->set_property(poll_results,'');
        $PAGE->block(_results_,poll_results,poll_item);
        $PAGE->set_property(doc_id,$this->get_property(doc_id));
        $PAGE->set_property(poll_total,$total);
        for($i=1; $i<=MAX_POLL_OPTIONS; $i++) {
            if ($this->get_property("poll_opt_$i")!='') {
                $q = sprintf("SELECT COUNT(*) FROM poll_votes ".
                             "WHERE doc_id=%d AND question_id=%d",
                             $this->get_property(doc_id),
                             $i);
                $r = $DB->read($q);
                list($num) = $DB->fetch_array($r);
                if ($total == 0)
                    $percent = 0;
                else
                    $percent = ($num/$total)*100;
                $bar = '';
                for($y=0;$y<$percent;$y+=5)
                    $bar .= '&nbsp;';
                $PAGE->set_property(poll_option,$this->get_property("poll_opt_$i"));
                $PAGE->set_property(poll_votes,$num);
                $PAGE->set_property(poll_percent,sprintf("%5.1f",$percent));
                $PAGE->set_property(poll_bar,$bar);
                $PAGE->set_property(poll_results,$PAGE->parse(poll_item),true);
            }
        }
        return $PAGE->parse(_results_);
    }

    // poll_input_form() - vote
    function poll_input_form() {
        global $PAGE,$CURUSER,$DB,$_POST;
        $poll_answer = $_POST['poll_answer'];
        if ($_POST['submitted'] && $CURUSER && $_POST['poll_answer']) {
            $uid = $CURUSER->get_property(user_id);
            $q = sprintf("INSERT INTO poll_votes (doc_id,user_id,question_id) ".
                         "VALUES (%d,%d,%d)",
                         $this->get_property(doc_id),
                         $uid,
                         $_POST['poll_answer']);
            $DB->write($q);
            logmsg('%s voted %d, "%s," on poll "%s"',
                    $CURUSER->get_property(user_name),
                    $_POST['poll_answer'],
                    $this->get_property("poll_opt_$poll_answer"),
                    $this->get_property(doc_title));
            return $this->poll_results();
        }
        else if ($_POST['submitted'] && !$CURUSER) {
            $PAGE->set_property(error,_ERR_NOTREGISTERED);
        }
        else if ($_POST['submitted'] && $_POST['poll_answer']) {
            $PAGE->set_property(error,_ERR_NOANSWER);
        }
        else {
            $f[] = array(name => submitted,
                         type => hidden,
                         value => 1);
            $f[] = array(name => id,
                         type => hidden,
                         value => $this->get_property(doc_id));
            for($i=1;$i<=MAX_POLL_OPTIONS;$i++) {
                $a = $this->get_property("poll_opt_$i");
                if ($a!='')
                    $f[] = array(name => "poll_answer",
                                 type => radio,
                                 value => $i,
                                 prompt => $a);
            }
            $PAGE->set_property(form_name,'poll');
            $PAGE->set_property(form_action,"{site_path}/document.php");
            $PAGE->set_property(form_instructions,_MSG_POLL_INSTR);
            $tmpid = $PAGE->get_property(doc_id);
            $PAGE->set_property(doc_id,0);
            $PAGE->input_form(_poll_,$f,'');
            $PAGE->set_property(doc_id,$tmpid);
            return $PAGE->parse(_poll_);
        }
        return '';
    }

    // input_form_values() - generate an input form
    function input_form_values() {
        global $PUBLISH_MODEL,$CURUSER,$MAX_DOC_CATEGORIES;
        if ($PUBLISH_MODEL!='open') {
            if (!$CURUSER)
                $hidden_disabled = 1;
            else if (!isadmin())
                $hidden_disabled = 1;
            else
                $hidden_disabled = 0;
        }
        $a = array(
                array(name => doc_id,
                      type => hidden,
                      value => $this->get_property(doc_id)),
                array(name => doc_type,
                      type => hidden,
                      value => 'Poll'),
                array(name => doc_title,
                      type => text,
                      size => 250,
                      value => $this->get_property(doc_title),
                      prompt => _PROMPT_POLL_TITLE),
                array(name => doc_folder_id,
                      type => select,
                      options => folderlist($CURUSER->get_property(user_id),'Poll'),
                      value => $this->get_property(doc_folder_id),
                      prompt => _PROMPT_DOC_FOLDER),
                array(name => doc_body,
                      type => textarea,
                      value => $this->get_property(doc_body),
                      prompt => _PROMPT_POLL_BODY),
                array(name => doc_hidden,
                      type => checkbox,
                      rval => 1,
                      disabled => $hidden_disabled,
                      value => $this->get_property(doc_hidden),
                      prompt => _PROMPT_DOC_HIDDEN)
             );
        for($i=1;$i<=MAX_POLL_OPTIONS;$i++) {
            $a[] = array(
                    name => "poll_opt_$i",
                    type => text,
                    size => 100,
                    value => $this->get_property("poll_opt_$i"),
                    prompt => _PROMPT_POLL_OPTION.$i
                    );
        }
        $a =  array_merge($a,$this->custom_properties());
        $cats = doc_categories($this->get_property('doc_type'));
        for($i=1; $i<=$MAX_DOC_CATEGORIES; $i++) {
          $a[] = array(name => "doc_category_$i",
                       type => select,
                       options => $cats,
                       value => $this->get_property("doc_category_$i"),
                       prompt => sprintf(_PROMPT_DOC_CATEGORY,$i)
                      );
        }
        if (isadmin())
            $a[] = array(name => doc_tag,
                         type => text,
                         size => 32,
                         value => $this->get_property(doc_tag),
                         prompt => _PROMPT_DOC_TAG);
        else
            $a[] = array(name => doc_tag,
                         type => hidden,
                         value => $this->get_property(doc_tag));
        return $a;
    }
}

?>
