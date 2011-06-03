<?php
// comment.php
// $Id: comment.php,v 1.23 2005/05/06 18:48:57 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// defines the Comment and Rating classs

define(COMMENT_FIELDS,
    'comment_doc_id|comment_id|comment_reply_to|comment_owner_id|comment_created|comment_body');

class Comment extends Siteframe {
var $rating; // not stored as a property

    // Comment() - constructor
    function Comment($id=0,$docid=0) {
        global $DB;
        // invoke parent first
        parent::Siteframe();
        // if $id, query the database for the comment
        if ($id) {
            $q = "SELECT comment_doc_id,comment_id,comment_reply_to,
                         comment_owner_id,comment_created,comment_body,
                         comment_props 
                 FROM comments WHERE comment_id=$id";
            $r = $DB->read($q);
            list($docid,$cid,$replyto,$ownerid,$created,$body,$props) =
                $DB->fetch_array($r);
            $this->set_property(comment_doc_id,$docid);
            $this->set_property(comment_id,$cid);
            $this->set_property(comment_reply_to,$replyto);
            $this->set_property(comment_owner_id,$ownerid);
            $this->set_property(comment_created,$created);
            $this->set_property(comment_body,$body);
            $this->set_xml_properties($props);
        }
        else {
            $this->set_property(comment_doc_id,$docid);
            $this->set_property(comment_id,0);
        }
    }

    // add() - save the comment
    function add() {
        global $DB,$COMMENT_EMAIL,$COMMENT_SUBJECTS,$SITE_NAME,
            $SITE_URL,$SITE_EMAIL,$COMMENT_DOC,$PAGE,$TEMPLATES,
            $SITE_TEMPLATES,$CURUSER;
        if (!$this->get_property('comment_owner_id')) {
            // anonymous comments
            if (trim($this->get_property(comment_email))=='') {
                $this->add_error("An email address is required for anonymous comments.");
            }
        }
        if ($this->errcount())
            return;

        // if rated, then insert the rating
        if ($this->rating)
          $this->add_rating($this->rating);

        // check for new errors
        if ($this->errcount() || trim($this->get_property('comment_body')==''))
          return;

        // insert the comment
        $q = sprintf("INSERT INTO comments (comment_created,comment_doc_id,
                      comment_reply_to,comment_owner_id,
                      comment_body,comment_props) 
                      VALUES (NOW(),%d,%d,%d,'%s','%s')",
                        $this->get_property(comment_doc_id),
                        $this->get_property(comment_reply_to),
                        $this->get_property(comment_owner_id),
                        addslashes($this->get_property(comment_body)),
                        addslashes($this->get_xml_properties()));
        $DB->write($q);
        if ($DB->error()!='') {
            $this->add_error(_ERR_NOADDCOMMENT,$DB->error());
            logmsg($this->get_errors());
        }
        else {
            $this->set_property(comment_id,$DB->insert_id());
            $doc = new Document($this->get_property(comment_doc_id));
            if ($this->get_property('comment_owner_id')) {
                $user = new User($this->get_property(comment_owner_id));
                $this->set_property(comment_user_name,$user->get_property(user_name));
            }
            logmsg('Comment added by %s on doc id=%d, "%s"',
                $this->get_property(comment_user_name),
                $this->get_property(comment_doc_id),
                $doc->get_property(doc_title));
            $this->trigger_event('comment','add');
            if (($COMMENT_EMAIL!='')
               || ($user&&$user->get_property('user_notify_comments')))
            {
                $user = new User($doc->get_property('doc_owner_id'));
                $email = new Email();
                $email->set_path($SITE_TEMPLATES);
                $email->set_array($PAGE->get_properties());
                $email->set_array($user->get_properties());
                $email->set_array($doc->get_properties());
                $email->set_array($this->get_properties());
                $email->set_property('email_from',
                    sprintf("%s <%s>",$SITE_NAME,$SITE_EMAIL));
                if (is_object($CURUSER))
                    $email->set_property('email_reply',$CURUSER->get_property(user_email));
                $email->set_property('email_subject',
                    sprintf('Comment on "%s"',$doc->get_property(doc_title)));
                $email->load_template(_ascii_,$TEMPLATES[Comment][ascii]);
                $email->set_property('email_ascii',$email->parse(_ascii_));
                if ($COMMENT_EMAIL!='') {
                    $email->add_address($COMMENT_EMAIL,'to');
                    $email->send();
                }
                if ($user->get_property('user_notify_comments')) {
                    $email->clear_addresses();
                    $email->add_address($user->get_property('user_email'),'to');
                    if (!$user->get_property('no_html_email')) {
                        $email->load_template('_html_',$TEMPLATES[Comment][html]);
                        $email->set_property('email_html',$email->parse('_html_'));
                    }
                    $email->send();
                }
            }
        }
    }

    // delete() - delete the comment
    function delete() {
        global $DB;
        $q = sprintf("DELETE FROM comments WHERE comment_id=%d",
                        $this->get_property(comment_id));
        $DB->write($q);
        if ($DB->error()!='') {
            $this->add_error(_ERR_NODELCOMMENT,$DB->error());
            logmsg($this->get_errors());
        }
        else {
            logmsg('Deleted comment %d',$this->get_property(comment_id));
            $this->trigger_event('comment','delete');

        }
    }

    // set_property - perform error checking
    function set_property($name,$value) {
        global $ANONYMOUS_COMMENTS,$COMMENT_EMAIL;
        switch($name) {
        case 'comment_subject':
            parent::set_property($name,htmlspecialchars(clean($value),ENT_QUOTES));
            break;
        case 'comment_body':
            if ((clean($value)=='') && ($this->rating==0)) {
                $this->add_error(_ERR_NOCOMMENT);
            }
            else
                parent::set_property($name,clean_html($value));
            break;
        case 'comment_owner_id':
            if ($value==0&&(!$ANONYMOUS_COMMENTS)) {
                $this->add_error(_ERR_NOANONYMOUS);
            }
            else
                parent::set_property($name,$value);
            break;
        case 'rating':
            $this->rating = $value;
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // get_properties()  - return array of properties
    function get_properties() {
        $a = parent::get_properties();
        if ($this->get_property(comment_owner_id)) {
            $u = new User($this->get_property(comment_owner_id));
            foreach($u->get_properties() as $name => $value) {
                $a["comment_$name"] = $value;
            }
        }
        else {
            $a[comment_owner_name] = $this->get_property(comment_name);
        }
        $class = doctype($this->get_property(comment_doc_id));
        $doc = new $class($this->get_property(comment_doc_id));
        foreach($doc->get_properties() as $name => $value) {
            $a["comment_$name"] = $value;
        }
        return $a;
    }

    // get_xml_properties() - simple override
    function get_xml_properties() {
        return parent::get_xml_properties(COMMENT_FIELDS);
    }

    // input_form_values() - for input
    function input_form_values() {
        global $COMMENT_SUBJECTS,$COMMENT_EMAIL,$ANONYMOUS_COMMENTS,
          $ALLOW_RATINGS,$RATING,$RATE_COMMENT_LIMIT,$DB,
          $SELF_RATING_ALLOWED;
        $docid = $this->get_property(comment_doc_id);
        if (!$docid)
            $this->add_error(_ERR_NODOCID);
        $a = array(
            array(name => comment_reply_to,
                  type => hidden,
                  value => $this->get_property(comment_reply_to)),
            array(name => comment_owner_id,
                  type => hidden,
                  value => $this->get_property(comment_owner_id)),
            array(name => comment_doc_id,
                  type => hidden,
                  value => $this->get_property(comment_doc_id)));

        $doc = new Document($docid);
        $ok = $SELF_RATING_ALLOWED ||
              ($this->get_property('comment_owner_id') !=
               $doc->get_property('doc_owner_id'));
        if ($doc->get_property('allow_ratings') &&
           $this->get_property('comment_owner_id') &&
           $ok)
        {
          $q = sprintf('SELECT rating FROM ratings WHERE doc_id=%d AND user_id=%d',
                $this->get_property('comment_doc_id'),
                $this->get_property('comment_owner_id'));
          $r = $DB->read($q);
          list($rating) = $DB->fetch_array($r);
          //arsort($RATING);
          $a[] = array(
            name => 'rating',
            type => 'select',
            options => array_merge(array(0=>'Select Rating'),$RATING),
            value => $rating,
            help => 'rating',
            prompt => _PROMPT_RATING,
            doc => 'Select a rating from the drop-down list.'.
                    ($RATE_COMMENT_LIMIT ?
                      sprintf(' Ratings less than or equal to %d must be accompanied by a comment.',$RATE_COMMENT_LIMIT) : '')
          );
        }

        if ($ANONYMOUS_COMMENTS && (!$this->get_property(comment_owner_id))) {
            $a[] = array(name => comment_name,
                         type => text,
                         size => 250,
                         value => $this->get_property(comment_name),
                         prompt => "Name");
            $a[] = array(name => comment_email,
                         type => text,
                         size => 250,
                         value => $this->get_property(comment_email),
                         prompt => "Email address");
            $a[] = array(name => comment_url,
                         type => text,
                         size => 250,
                         value => $this->get_property(comment_url),
                         prompt => "Web address (URL)");
        }
        if ($COMMENT_SUBJECTS) {
            $a[] = array(name => comment_subject,
                         type => text,
                         size => 250,
                         value => $this->get_property(comment_subject),
                         prompt => _PROMPT_COMMENT_SUBJECT);
        }
        $a[] = array(name => comment_body,
                     type => textarea,
                     value => $this->get_property(comment_body),
                     doc => 'The main body of your comment.',
                     help => 'autoformat',
                     prompt => _PROMPT_COMMENT_BODY);
        return $a;
    }

    // add_rating - adds a rating
    function add_rating($value) {
      global $DB,$CURUSER,$SELF_RATING_ALLOWED,$RATE_COMMENT_LIMIT;
      $uid = $CURUSER->get_property('user_id');
      $did = $this->get_property('comment_doc_id');
      // check for member self-rating
      if (!$SELF_RATING_ALLOWED) {
        $doc = new Document($did);
        if ($uid==$doc->get_property('doc_owner_id')) {
          $this->add_error(_ERR_COMMENT_NO_SELF_RATING);
        }
      }

      // check for min rating requires comment
      if ($RATE_COMMENT_LIMIT && ($value<=$RATE_COMMENT_LIMIT) &&
         (trim($this->get_property('comment_body')=='')))
      {
        $this->add_error(_ERR_COMMENT_RATE_LIMIT,$RATE_COMMENT_LIMIT);
      }

      // if ok, delete existing rating & add new one
      if (!$this->errcount()) {
        $q = sprintf('DELETE FROM ratings WHERE user_id=%d AND doc_id=%d',
                      $uid, $did);
        @$DB->write($q);

        $q = sprintf('INSERT INTO ratings (user_id,doc_id,rating) VALUES (%d,%d,%d)',
              $uid,$did,$value);
        $r = $DB->write($q);

        if ($r)
          logmsg('Rating: user=%d, doc=%d',$uid,$did);
        else
          logmsg('Rating error: %s',$DB->error());
        $this->add_error($DB->error());
      }
    }

} // end class Comment

?>
