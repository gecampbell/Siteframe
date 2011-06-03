<?php
// email.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: email.php,v 1.15 2003/06/21 16:06:46 glen Exp $
//
// a class for defining and sending email messages

class Email extends Template {

// properties used by this class:
//  email_to:       "TO:" recipient
//  email_cc:       "CC:" recipients
//  email_bcc:      "BCC:" recipients
//  email_subject:  subject line
//  email_from:     "FROM:" line
//  email_reply:    "Reply-To:" header
//  email_ascii:    ASCII version of the message
//  email_html:     HTML version of the message (optional)
// methods:
//  email_ascii()   formatted ASCII message
//  email_html()    formatted HTML message
//  send()          sends message

    // constructor function
    function Email() {
        $this->set_property('email_created',date('d-m-Y H:iT'));
    }

    // set_property
    function set_property($name,$value) {
        switch($name) {
        /*
        case 'email_ascii':
            parent::set_property($name,wordwrap(strip_tags($value),64));
            break;*/
        case 'email_html':
            parent::set_property($name,wordwrap($value,64));
            break;
        case 'email_subject':
            parent::set_property($name,strip_tags($value));
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // add_address - add a new recipient
    // 'type' should be 'to', 'cc', or 'bcc'
    function add_address($emailaddr,$type='to') {
        $property = sprintf("email_%s",$type);
        $prefix = $this->get_property($property);
        if (strlen($prefix) > 64)
            $sep = ",\n\t";
        else if (strlen($prefix)==0)
            $sep = "";
        else
            $sep = ",";
        $this->set_property($property,$prefix.$sep.$emailaddr);
    }

    // clear_addresses - remove all addresses
    function clear_addresses() {
        $this->set_property('email_to','');
        $this->set_property('email_cc','');
        $this->set_property('email_bcc','');
    }

    // email_ascii() - returns formatted ASCII message
    function email_ascii() {
        global $TEMPLATES;
        if ($this->get_property('email_ascii')=='') {
            $this->add_error("No ASCII version of email defined");
            return;
        }
        $this->load_template('_email_ascii_',$TEMPLATES['Email']['ascii']);
        $this->set_property('email_body',$this->parse('email_ascii'));
        return strip_tags($this->parse('_email_ascii_'));
    }

    // email_html() - returns formatted HTML message
    function email_html() {
        global $TEMPLATES;
        if ($this->get_property('email_html')=='') {
            $this->add_error("No HTML version of email defined");
            return;
        }
        $this->load_template('_email_html_',$TEMPLATES['Email']['html']);
        $this->set_property('email_body',$this->parse('email_html'));
        return $this->parse('_email_html_');
    }

    // send() - sends the message
    function send() {
        global $PAGE;
        if ($this->errcount)
            die($this->get_errors());
        $this->set_array($PAGE->get_properties());
        $this->set_path($PAGE->path);
        $subj = $this->get_property('email_subject');
        $to = $this->get_property('email_to');
        $cc = $this->get_property('email_cc');
        $bcc = $this->get_property('email_bcc');
        $from = $this->get_property('email_from');
        $reply = $this->get_property('email_reply');
        $html = $this->get_property('email_html');
        $headers = sprintf("From: %s\n", $from);
        if ($reply!='') {
            $headers .= sprintf("Reply-To: %s\n", $reply);
        }
        if ($cc!='')
            $headers .= "CC:" . $cc . "\n";
        if ($bcc!='')
            $headers .= "BCC:" . $bcc . "\n";

        // determine whether to send HTML or ASCII message
        if ($html!='') {
            $boundary = sprintf("==Multipart_Boundary_%s",md5(time()));
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Content-Type: multipart/alternative;\n";
            $headers .= "\tboundary=\"$boundary\"\n";
            $msgbody = sprintf("%s\n\n--%s\n%s\n%s\n\n%s\n\n--%s\n%s\n%s\n\n%s",
                        "This is a multi-part message in MIME format.",
                        $boundary,
                        "Content-Type: text/plain; charset=\"iso-8859-1\"",
                        "Content-Transfer-Encoding: 7bit",
                        $this->email_ascii(),
                        $boundary,
                        "Content-Type: text/html; charset=\"iso-8859-1\"",
                        "Content-Transfer-Encoding: 7bit",
                        $this->email_html());
        }
        else { // ASCII-only message
            $msgbody = $this->email_ascii();
        }
        mail($to, $subj, $msgbody, $headers);
        logmsg("Sent email to %s",$to);
    }

}

?>
