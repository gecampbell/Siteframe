<?php
// bug.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: bug.php,v 1.5 2003/06/27 01:35:08 glen Exp $
//
// a bug report class

if ($ENABLE_BUG) {

// template requirements

$TEMPLATES[Bug] = 'bug';
$TEMPLATES[VFolder][Bug] = $TEMPLATES[Folder][Bug] = 'folder';
$TEMPLATES[CFolder][Bug] = 'cfolder';
$TEMPLATES[LFolder][Bug] = 'folder';
$TEMPLATES[SFolder][Bug] = 'sfolder';

// class definition

$CLASSES[Bug] = 'Bug Report';

// language strings

define(_PROMPT_BUG_BODY, 'Detailed description<br/>(include listings, if possible)');
define(_PROMPT_BUG_SEVERITY, 'Severity');
define(_PROMPT_BUG_STATUS, 'Status');
define(_PROMPT_BUG_SUMMARY, 'Summary');
define(_PROMPT_BUG_TITLE, 'Title');
define(_PROMPT_BUG_URL, 'Page URL');
$DOCSTRINGS[bug_severity] = 'How bad was the bug? Is this merely an enhancement request?';
$DOCSTRINGS[bug_status] = 'The current status of the bug.';
$DOCSTRINGS[bug_url] = 'If the bug occurs within a particular page or script, enter it here.';


// the Bug class definition

class Bug extends Document {

    // Bug - constructor
    function Bug($id=0, $dbrow=0) {
        parent::Document($id, $dbrow);
        $this->set_property(doc_type,'Bug');
    }

    // add() - add w/notification
    function add() {
        global $SITE_EMAIL,$SITE_NAME,$SITE_URL;
        $this->set_property(bug_status,'open');
        parent::add();
        mail($SITE_EMAIL,
             "Bug Report for $SITE_NAME",
             sprintf("$SITE_URL/document.php?id=%d\nSubject: %s\nSeverity: %s\n\nSummary: %s\n\n_____\nSent from $SITE_NAME ($SITE_URL)",
                $this->get_property(doc_id),
                $this->get_property(doc_title),
                $this->get_property(bug_severity),
                $this->get_property(doc_summary)),
             "From: $SITE_NAME <$SITE_EMAIL>");
    }

    // set_property()
    function set_property($name,$value) {
        global $CURUSER;
        switch($name) {
        case 'bug_status':
            if ($value!=$this->get_property(bug_status)) {
                parent::set_property($name,$value);
                if ($this->get_property(doc_id)) {
                    $co = new Comment(0,$this->get_property(doc_id));
                    $co->set_property(comment_subject,'Status Change');
                    $co->set_property(comment_body,
                        sprintf("Status changed to [%s] by %s",
                            $value,
                            $CURUSER->get_property(user_name)));
                    $co->set_property(comment_owner_id,$CURUSER->get_property(user_id));
                    $co->add();
                }
            }
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // input_form_values - define bug report properties
    function input_form_values() {
        global $ref;
        $a = array(
            array(name => doc_id,
                  type => hidden,
                  value => $this->get_property(doc_id)),
            array(name => doc_type,
                  type => hidden,
                  value => 'Bug'),
            array(name => doc_hidden,
                  type => hidden,
                  value => 1),
            array(name => doc_folder_id,
                  type => hidden,
                  value => 0),
            array(name => doc_title,
                  type => text,
                  size => 250,
                  value => $this->get_property(doc_title),
                  prompt => _PROMPT_BUG_TITLE),
            array(name => bug_status,
                  type => select,
                  options => array( "open" => "Open",
                                    "reviewed" => "Reviewed",
                                    "working" => "Fix in progress",
                                    "feedback" => "Waiting for user feedback",
                                    "closed" => "Closed"),
                  value => ($this->get_property(doc_id) ? $this->get_property(bug_status) : 'open'),
                  prompt => _PROMPT_BUG_STATUS),
            array(name => bug_severity,
                  type => select,
                  options => array( "enhancement" => "Enhancement Request",
                                    "bad" => "Bad, but I could continue working",
                                    "fatal" => "Everything crashed"),
                  value => ($this->get_property(doc_id) ? $this->get_property(bug_severity) : 'bad'),
                  prompt => _PROMPT_BUG_SEVERITY),
            array(name => bug_url,
                  type => text,
                  size => 250,
                  value => ($this->get_property(doc_id) ? $this->get_property(bug_url) : $ref),
                  prompt => _PROMPT_BUG_URL),
            array(name => doc_summary,
                  type => textarea,
                  value => $this->get_property(doc_summary),
                  prompt => _PROMPT_BUG_SUMMARY),
            array(name => doc_body,
                  type => textarea,
                  value => $this->get_property(doc_body),
                  rows => 15,
                  prompt => _PROMPT_BUG_BODY),
        );
        $a =  array_merge($a,$this->custom_properties());
        return $a;
    }
}

} // end $ENABLE_BUG

$BugReport = new Plugin('BugReport');
$BugReport->set_global('Bug Report','ENABLE_BUG',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Enable Bug Reports',
    doc => 'A bug report is a specialized type of document aimed at helping software developers track defects in their code and other products. Check this box to allow users to create Bug Reports.'
  ));
$BugReport->register();

?>
