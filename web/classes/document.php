<?php
/* document.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: document.php,v 1.81 2006/05/25 13:38:20 glen Exp $
**
** Defines the base Document class, from which all the other document types
** are subclassed.
*/

define(DOC_FIELDS,'doc_id|doc_created|doc_modified|doc_type|doc_folder_id|doc_owner_id|doc_tag|doc_hidden|doc_title|doc_body|doc_category_1|doc_category_2|doc_category_3|doc_category_4|doc_category_5|doc_category_6|doc_category_7|doc_category_8|doc_category_9');

class Document extends Siteframe {

    // Document(opt id, opt dbrow) - constructor
    function Document($id=0, $dbrow=0) {
        global $DB,$PUBLISH_MODEL,$ALLOW_RATINGS,$ALLOW_COMMENTS,$MAX_DOC_CATEGORIES;
        parent::Siteframe();
        if ($id || $dbrow) { // retrieve document from DB
            if ($dbrow) {
                $docs = $dbrow;
            }
            else {
                $q = "SELECT * FROM docs WHERE doc_id=$id";
                $r = $DB->read($q);
                $docs = $DB->fetch_array($r);
            }
            $this->set_property(doc_id,$docs[doc_id]);
            $this->set_property(doc_created,$docs[doc_created]);
            $this->set_property(doc_modified,$docs[doc_modified]);
            $this->set_property(doc_tag,$docs[doc_tag]);
            $this->set_property(doc_folder_id,$docs[doc_folder_id]);
            $this->set_property(doc_type,$docs[doc_type]);
            $this->set_property(doc_owner_id,$docs[doc_owner_id]);
            $this->set_property(doc_hidden,$docs[doc_hidden]);
            $this->set_property(doc_title,$docs[doc_title]);
            $this->set_property(doc_body,$docs[doc_body]);
            $this->set_xml_properties($docs[doc_props]);
            if ($MAX_DOC_CATEGORIES) {
              $qc = sprintf("SELECT * FROM doc_categories ".
                            "LEFT JOIN categories ON ".
                            "(doc_categories.doc_cat_id=categories.cat_id) ".
                            "WHERE doc_id=%d ".
                            "ORDER BY cat_name",
                    $this->get_property('doc_id'));
              $rc = $DB->read($qc);
              $i = 1;
              while($arr = $DB->fetch_array($rc)) {
                $this->set_property(sprintf("doc_category_%d",$i++),$arr['doc_cat_id']);
              }
            }

        }
        else { // new (blank) document
            $this->set_property(doc_type,'Document');
            $this->set_property(doc_hidden,$PUBLISH_MODEL=='open' ? 0 : 1);
            $this->set_property(allow_ratings,$ALLOW_RATINGS);
            $this->set_property(allow_comments,$ALLOW_COMMENTS);
        }
    }

    // add() - add a new document
    function add() {
        global $DB,$CURUSER,$PUBLISH_MODEL,$NOTIFY_EMAIL,
               $SITE_NAME,$SITE_EMAIL,$SITE_URL,$MAX_DOC_PER_DAY;
        if ($MAX_DOC_PER_DAY) {
            $q = sprintf("SELECT COUNT(*) FROM docs ".
                         "WHERE doc_owner_id=%d AND ".
                         " doc_created > DATE_SUB(NOW(),INTERVAL 1 DAY) ",
                         $this->get_property('doc_owner_id'));
            $r = $DB->read($q);
            list($num_docs) = $DB->fetch_array($r);
            if ($num_docs >= $MAX_DOC_PER_DAY) {
                $this->add_error("Members of this site are only allowed to create %d documents per day (within the last 24 hours)",
                                   $MAX_DOC_PER_DAY);
            }
        }
        $this->validate();
        // exit if any errors exist
        if ($this->errcount())
            return;
        // check publication model
        if ($PUBLISH_MODEL!='open') {
            if (!isadmin($CURUSER->get_property(user_id)))
                $this->set_property(doc_hidden,1);
        }
        $otitle = $this->get_property(doc_title);
        $ocount = 2;
        // 2002/10/07 - added DELAYED
        // 2002/10/07 - removed it
        $q = "INSERT INTO docs (doc_created,doc_modified,doc_type,doc_tag,doc_folder_id,".
             "doc_owner_id,doc_hidden,doc_title,doc_body,doc_props) ".
             "VALUES (NOW(),NOW(),'%s','%s',%d,%d,%d,'%s','%s','%s')";
        do {
            $DB->write(sprintf($q,
                        $this->get_property(doc_type),
                        $this->get_property(doc_tag),
                        0, // update later $this->get_property(doc_folder_id),
                        $this->get_property(doc_owner_id),
                        $this->get_property(doc_hidden),
                        addslashes($this->get_property(doc_title)),
                        addslashes($this->get_property(doc_body)),
                        addslashes($this->get_xml_properties())));
            if ($DB->errno()==1062) {
                $this->set_property(doc_title,
                    sprintf("%s (%d)",$otitle,$ocount++));
            }
        } while ($DB->errno()==1062);
        $this->add_error($DB->error());
        // if successful, save the document ID
        if (!$this->errcount()) {
            $this->set_property('doc_id',$DB->insert_id());
            // this adds the document to the requested folder
            if ($this->get_property('doc_folder_id')) {
                $this->update(); // this updates the folder id
            }
            else {
              // generate RSS
              $this->genrss();
            }
            // add document categories
            $this->add_categories();
            logmsg("Added %s \"%s\", folder=%d",
                $this->get_property('doc_type'),
                $this->get_property('doc_title'),
                $this->get_property('doc_folder_id'));
            // this notifies weblogs.com of changes
            pingWeblogs();
            // notify users
            //$this->notify("document");
            $this->trigger_event('document','add');
        }
        else {
            logmsg("Failed adding document \"%s\", error=\"%s\"",
                $this->get_property(doc_title),
                $this->get_errors());
        }
        // if not open, then notify someone of the document
        if (($PUBLISH_MODEL!='open') && ($NOTIFY_EMAIL!='')) {
            mail($NOTIFY_EMAIL,
                "$SITE_FRAME document submitted",
                sprintf("New document: \"%s\"\nLink: $SITE_URL/edit.php?id=%d",
                    $this->get_property(doc_title),
                    $this->get_property(doc_id)),
                "From: $SITE_NAME <$SITE_EMAIL>");
        }
    }

    // update() - update the document
    function update() {
        global $DB;
        $this->validate();
        // add doc to folder
        $folder_id = $this->get_property('doc_folder_id')+0;
        // if the newly-requested folder_id is different than
        // the previous folder ID, we need to first remove the
        // document from the old folder
        if ($folder_id!=($this->get_property('old_folder_id')+0)) {
            if ($this->get_property(old_folder_id)) {
                $class = foldertype($this->get_property('old_folder_id'));
                $f = new $class($this->get_property('old_folder_id'));
                // remove doc from old folder
                $f->del_doc($this);
                // now, set the folder ID to the new value
                if (!$this->errcount())
                    $this->set_property('doc_folder_id',$folder_id);
            }
        }
        // ok, so now we need to add the document to the new folder
        if ($folder_id) {
            $class = foldertype($folder_id);
            $f = new $class($this->get_property('doc_folder_id'));
            // $this->set_property(doc_folder_id,$folder_id);
            // add $this document to the folder $f
            $f->add_doc($this);
            $this->trigger_event('folder','doc_add');
        }
        // exit on any errors
        if ($this->errcount()) return;
        // perform triggers
        $this->trigger_event('document','update');
        // define query
        $q = "UPDATE docs SET doc_modified=NOW(),".
             "  doc_folder_id=%d,".
             "  doc_hidden=%d, doc_title='%s', doc_body='%s',".
             "  doc_tag='%s',doc_props='%s' ".
             "WHERE doc_id=%d";
        $DB->write(sprintf($q,$folder_id,
                              $this->get_property(doc_hidden),
                              addslashes($this->get_property(doc_title)),
                              addslashes($this->get_property(doc_body)),
                              $this->get_property(doc_tag),
                              addslashes($this->get_xml_properties()),
                              $this->get_property(doc_id)));
        $this->add_error($DB->error());
        if ($this->errcount()) {
            logmsg("Failed to update document id=%d, \"%s\", error=\"%s\"",
                $this->get_property(doc_id),
                $this->get_property(doc_title),
                $this->get_errors());
        }
        else {
            logmsg("Updated document id=%d, \"%s\"",
                $this->get_property(doc_id),
                $this->get_property(doc_title));
            // generate RSS XML
            $this->genrss();
            // update categories
            $this->add_categories();
        }
    }

    // delete() - delete the document
    function delete($reason='') {
        global $DB;
        $this->trigger_event('document','delete');
        $DB->write(sprintf("DELETE FROM docs WHERE doc_id=%d",
                    $this->get_property(doc_id)));
        $this->add_error($DB->error());
        $DB->write(sprintf("DELETE FROM ratings WHERE doc_id=%d",
                    $this->get_property(doc_id)));
        $this->add_error($DB->error());
        $DB->write(sprintf("DELETE FROM comments WHERE comment_doc_id=%d",
                    $this->get_property(doc_id)));
        $this->add_error($DB->error());
        $DB->write(sprintf("DELETE FROM schedule WHERE doc_id=%d",
                    $this->get_property(doc_id)));
        $this->add_error($DB->error());
        $DB->write(sprintf("DELETE FROM doc_categories WHERE doc_id=%d",
                    $this->get_property(doc_id)));
        $this->add_error($DB->error());
        logmsg("Deleted document id=%d, \"%s,\" reason=%s",
                    $this->get_property(doc_id),
                    $this->get_property(doc_title),
                    $reason);
        // generate RSS XML
        $this->genrss();
    }

    // set_property(name,value) - set property w/error checking
    function set_property($name,$value) {
        global $DB;
        switch($name) {
        case 'doc_folder_id':
            parent::set_property('old_folder_id',$this->get_property(doc_folder_id));
            parent::set_property($name,$value);
            break;
        case 'doc_tag':
            $value = strtolower(str_replace(' ','',clean($value)));
            parent::set_property($name,$value);
            break;
        case 'doc_title':
            if (clean($value) == '')
                $this->add_error(_ERR_NOTITLE);
            else
                parent::set_property($name,clean($value));
            break;
        case 'ad_url_link':
        case 'doc_summary':
        case 'doc_body':
        case 'folder_body':
            parent::set_property($name,clean_html($value));
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // get_xml_properties() - set properties from doc_props
    function get_xml_properties($ignore=DOC_FIELDS) {
        return parent::get_xml_properties($ignore);
    }

    // get_property
    function get_property($name) {
        switch($name) {
        case 'doc_body':
            $b = parent::get_property('doc_body');
            $s = parent::get_property('doc_summary');
            if (($s!='') and ($b==''))
              return $s;
            else
              return $b;
            break;
        case 'doc_title_display':
            if ($this->get_property(doc_hidden))
                return $DOC_HIDDEN_PREFIX . parent::get_property('doc_title') . $DOC_HIDDEN_SUFFIX;
            else
                return parent::get_property('doc_title');
            break;
        default:
            return parent::get_property($name);
        }
    }

    // get_properties() - add some new properties
    function get_properties() {
        global $DB,$CLASSES,$DOC_HIDDEN_PREFIX,$DOC_HIDDEN_SUFFIX,
               $RATING,$CURUSER,$SITE_DATE_FORMAT,$SYMBOLS;
        $a = parent::get_properties();
        $a['doc_body'] = str_replace("\r\n","\n",$this->get_property('doc_body'));
        $u = new User($this->get_property(doc_owner_id));
        foreach ($u->get_properties() as $name => $value) {
            $a["doc_$name"] = $value;
        }
        // get rating information
        $q = sprintf("SELECT COUNT(*),AVG(rating),SUM(rating) ".
                     "FROM ratings WHERE doc_id=%d",
                            $this->get_property(doc_id));
        $r = $DB->read($q);
        if ($r) {
            list($num,$rating,$total) = $DB->fetch_array($r);
            $a['doc_rating_count'] = sprintf("%d",$num);
            $a['doc_rating'] = $rating;
        }
        // get overall count
        $q = sprintf("SELECT COUNT(*),AVG(rating) FROM ratings");
        $r = $DB->read($q);
        if ($r) {
            list($cnum,$cavg) = $DB->fetch_array($r);
            $a['site_rating_count'] = $cnum;
            $a['site_rating_average'] = $cavg;
$cavg = 5.0;
            $a['doc_weighted_rating'] = 
                ($num/($num+5.0))*$rating+(5.0/($num+5.0))*$cavg;
        }
        // get count of comments
        $q = sprintf("SELECT COUNT(*) FROM comments WHERE comment_doc_id=%d",
                            $this->get_property(doc_id));
        $r = $DB->read($q);
        if ($r) {
            list($num) = $DB->fetch_array($r);
            $a[doc_comment_count] = sprintf("%d",$num);
        }
        // set displayed document type
        $a[doc_type_display] = $CLASSES[$this->get_property(doc_type)];
        // get document's folder information
        if ($this->get_property(doc_folder_id)) {
            $class = foldertype($this->get_property(doc_folder_id));
            $folder = new $class($this->get_property(doc_folder_id));
            foreach ($folder->get_properties() as $name => $value) {
                $a["doc_$name"] = $value;
            }
        }
        else {
            $a['doc_folder_name'] = '';
        }
        if ($this->get_property(doc_folder_id)) {
            $q = sprintf('SELECT created,begin_date,end_date FROM schedule '.
                         'WHERE folder_id=%d AND doc_id=%d',
                         $this->get_property(doc_folder_id),
                         $this->get_property(doc_id));
            $r = $DB->read($q);
            list($da,$bd,$ed) = $DB->fetch_array($r);
            $a[folder_date_added] = $da;
            $a[folder_begin_date] = $bd;
            $a[folder_end_date] = $ed;
        }
        $a['doc_title_display'] = $this->get_property('doc_title_display');
        // handle competition entries
        if ($a['doc_folder_competition_active']) {
          $a['doc_user_firstname'] = '';
          $a['doc_user_lastname'] = '';
          $a['doc_user_name'] = '';
          $a['doc_user_id'] = 0;
          $a['doc_owner_id'] = 0;
          $a['doc_rating_count'] = 0;
          $a['doc_rating'] = 0;
          foreach($SYMBOLS as $syma => $symb) {
            $a[$syma] = 0;
          }
        }
        if ($a['competition_entry']&&$a['doc_folder_voting']) {
          // construct rating box
          if ($CURUSER) {
            $q = sprintf("SELECT rating FROM ratings ".
                         "WHERE doc_id=%d AND user_id=%d",
                         $this->get_property('doc_id'),
                         $CURUSER->get_property('user_id'));
            $r = $DB->read($q);
            list($user_val) = @$DB->fetch_array($r);
            if ($a['doc_folder_competition_type'] == "vote") {
              $a['doc_competition_value'] =
                sprintf(' <input type="radio" value="%d" name="vote"%s>Select</input>',
                  $this->get_property('doc_id'),
                  ($user_val==VOTE_VAL) ? ' checked="checked"' : '');
            }
            else {
              $optlist = ' <option value="0">Not rated</option>'."\n";
              foreach($RATING as $val => $prompt) {
                $optlist .= sprintf(' <option value="%s"%s>%s</option>%s',
                              $val,
                              ($val==$user_val) ? ' selected="selected"' : '',
                              $prompt,
                              "\n");
              }
              $a['doc_competition_value'] =
                sprintf('<select name="doc_rating_%d">%s</select>',
                  $this->get_property('doc_id'),
                  $optlist);
            }
          }
          else {
            $a['doc_competition_value'] = '[not logged in]';
          }
        }
        else if (($a['doc_folder_begin_voting']!='') && (strtotime($a['doc_folder_begin_voting']) > strtotime("now"))) {
          $a['doc_competition_value'] = '[not voting yet]';
        }
        else if ($a['competition_entry']) {
          switch($a['doc_folder_competition_type']) {
          case 'max':
            $cvalue = sprintf('%d/%d',$total,$a['doc_rating_count']);
            break;
          case 'maxavg':
            $cvalue = sprintf('%.2f/%d',$rating,$a['doc_rating_count']);
            break;
          case 'vote':
            $cvalue = sprintf('%d',$a['doc_rating_count']);
            break;
          default:
            $cvalue = '[unanticipated error]';
          }
          $a['doc_competition_value'] = $cvalue;
        }
        return $a;
    }

    // input_form_values() - return input form array
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
                      value => $this->get_property('doc_id')),
                array(name => doc_title,
                      type => text,
                      size => 250,
                      value => $this->get_property('doc_title'),
                      prompt => _PROMPT_DOC_TITLE),
                array(name => doc_folder_id,
                      type => select,
                      options => folderlist($CURUSER->get_property(user_id),
                                    $this->get_property(doc_type)),
                      value => $this->get_property('doc_folder_id'),
                      prompt => _PROMPT_DOC_FOLDER),
                array(name => doc_body,
                      type => textarea,
                      value => $this->get_property('doc_body'),
                      rows => 15,
                      prompt => _PROMPT_DOC_BODY),
                array(name => doc_hidden,
                      type => checkbox,
                      rval => 1,
                      disabled => $hidden_disabled,
                      value => $this->get_property(doc_hidden),
                      prompt => _PROMPT_DOC_HIDDEN),
                array(name => allow_ratings,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_ratings),
                      prompt => _PROMPT_DOC_RATING),
                array(name => allow_comments,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_comments),
                      prompt => _PROMPT_DOC_COMMENTS),
                array(name => copying_allowed,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(copying_allowed),
                      prompt => _PROMPT_DOC_COPYING)
             );
        $cats = doc_categories($this->get_property('doc_type'));
        for($i=1; $i<=$MAX_DOC_CATEGORIES; $i++) {
          $a[] = array(name => "doc_category_$i",
                       type => select,
                       options => $cats,
                       value => $this->get_property("doc_category_$i"),
                       prompt => sprintf(_PROMPT_DOC_CATEGORY,$i)
                      );
        }
        if (isadmin()) {
            $a[] = array(name => doc_tag,
                         type => text,
                         size => 32,
                         value => $this->get_property(doc_tag),
                         prompt => _PROMPT_DOC_TAG);
        }
        else {
            $a[] = array(name => doc_tag,
                         type => hidden,
                         value => $this->get_property(doc_tag));
        }
        return $a;
    }

    // display(template) - returns formatted document based on template
    function display($template) {
        global $DB,$PAGE,$PHP_SELF,$TEMPLATES;
        $tplname = 'document'.md5(rand());
        $PAGE->load_template($tplname,$template);
        $a = $this->get_properties();
        foreach($a as $name => $value) {
            $PAGE->set_property($name,$value);
        }
        return $PAGE->parse($tplname);
    }

    // genrss - create rss.xml file
    function genrss() {
        global $GEN_RSS,$PAGE,$CACHED_VALUE_UPDATE_TIME,
            $RSS_GEN_TIME,$TEMPLATES;
        if (!$GEN_RSS) return;
        $rss = $TEMPLATES[XMLrss];
        $timediff = time() - $RSS_GEN_TIME;
        if ((!file_exists("rss.xml"))||($timediff>($CACHED_VALUE_UPDATE_TIME*60))) {
            $PAGE->load_template(xml,$rss);
            $fc = fopen("rss.xml","w");
            fwrite($fc,$PAGE->parse(xml));
            fclose($fc);
            @chmod('rss.xml',0777);
            logmsg("Generated RSS (rss.xml)");
            set_global('RSS_GEN_TIME',time());
        }
    }

    // add categories to doc
    function add_categories() {
      global $DB;
      // delete any old ones
      $DB->write(sprintf("DELETE FROM doc_categories WHERE doc_id=%d",
                    $this->get_property('doc_id')));
      // add document categories
      for($i=1; $i<10; $i++) {
        $cid = $this->get_property("doc_category_$i")+0;
        if ($cid) {
          $q = sprintf("INSERT INTO doc_categories (doc_id,doc_cat_id) ".
                       "VALUES (%d,%d)",
                       $this->get_property('doc_id'),
                       $cid);
          $DB->write($q);
        }
      }
    }

    // validate() - check things out
    function validate() {
      global $DB,$DOC_REQUIRE_FOLDER,$DOC_REQUIRE_CATEGORY;

      parent::validate();

      // check doc_owner_id
      if (!$this->get_property('doc_owner_id')) {
        $this->add_error('Invalid document owner ID');
      }

      // check for duplicate doc_tag
      $r = $DB->read(sprintf("SELECT COUNT(*) FROM docs WHERE doc_tag='$value' ".
                              " AND doc_id!=%d",$this->get_property(doc_id)));
      list($num) = $DB->fetch_array($r);
      if (($value!='') && $num)
          $this->add_error(_ERR_DUPETAG,$value);

      // check for folder
      if ($DOC_REQUIRE_FOLDER && (!$this->get_property('doc_folder_id')))
        $this->add_error('This website requires that documents be in a folder');

      // check for categories
      if ($DOC_REQUIRE_CATEGORY) {
        for($i=1; $i<10; $i++) {
          if ($this->get_property("doc_category_$i"))
            ++$num_categories;
        }
        if ($num_categories==0)
          $this->add_error('This website requires that documents have at least one category');
      }
    }

    // title() - return a descriptive string
    function title() {
      return $this->get_property('doc_title');
    }

}

?>
