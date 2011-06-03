<?php
// sfolder.php
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
// $Id: sfolder.php,v 1.23 2005/04/22 14:15:32 glen Exp $

class SFolder extends Folder {

    // SFolder() - constructor
    function SFolder($id=0, $dbrow=0) {
        global $DB;
        parent::Folder($id, $dbrow);
        $this->set_property(folder_type,'SFolder');
        $this->reschedule();
    }

    // update() - reschedule if interval changed
    function update() {
        global $DB;
        parent::update();
        $this->reschedule();
    }

    // set_property(name,val) - perform error-checking and cleanup
    function set_property($name,$value) {
        switch($name) {
        case 'folder_limit_type':
            if ($value == "none") {
                $this->add_error(_ERR_NEEDDOC);
            }
            else
                parent::set_property($name,$value);
            break;
        case 'folder_user_limit':
            if ($value < 0)
                $this->add_error(_ERR_BADVALUE,$name,$value);
            else
                parent::set_property($name,$value);
            break;
        default:
            parent::set_property($name,clean($value));
        }
    }

    // create an input form
    function input_form_values() {
        $a = parent::input_form_values();
        $a[] = array(
            name => folder_interval,
            type => text,
            size => 3,
            value => $this->get_property(folder_interval),
            doc => _DOC_FOLDER_INTERVAL,
            prompt => _PROMPT_FOLDER_INTERVAL
        );
        $a[] = array(
            name => folder_interval_type,
            type => select,
            options => array("HOUR" => "Hour(s)", "DAY" => "Day(s)", "WEEK" => "Week(s)", "MONTH" => "Month(s)"),
            value => $this->get_property(folder_interval_type),
            doc => _DOC_FOLDER_INTERVAL_TYPE,
            prompt => _PROMPT_FOLDER_INTERVAL_TYPE
        );
        return $a;
    }

    // add_doc(doc) - add a document, then reschedule
    function add_doc(&$doc) {
        global $DB;
        parent::add_doc($doc);
        if ((!$this->errcount()) && (!$doc->errcount()) && ($doc->get_property(doc_id)+0)) {
            // these lines are to prevent the "duplicate key" error
            /*
            $q = sprintf('SELECT folder_id FROM schedule WHERE doc_id=%d',
                         $doc->get_property(doc_id));
            $r = $DB->read($q);
            list($folderid) = $DB->fetch_array($r);
            if ($folderid) { return; }
            */
            $q = sprintf("DELETE FROM schedule WHERE doc_id=%d",
                  $doc->get_property('doc_id'));
            @$DB->write($q);
            // add to scheduled folder if not already there
            $q = sprintf('INSERT INTO schedule (folder_id,doc_id,created) '.
                         'VALUES (%d,%d,NOW())',
                         $this->get_property('folder_id'),
                         $doc->get_property('doc_id'));
            $DB->write($q);
            $doc->add_error($DB->error());
            logmsg("Added doc id=%d to scheduled folder %d",
              $doc->get_property('doc_id'),
              $this->get_property('folder_id'));
            $this->reschedule();
        }
        // $doc->set_property(doc_hidden,1);
        if ($this->get_property(folder_user_limit)) {
            $r = $DB->read(sprintf("SELECT COUNT(*) FROM docs WHERE doc_folder_id=%d ".
                                    "   AND doc_owner_id=%d",
                                    $this->get_property(folder_id),
                                    $doc->get_property(doc_owner_id)));
            list($count) = $DB->fetch_array($r);
            if ($count > $this->get_property(folder_user_limit)) {
                $doc->set_property(doc_folder_id,0);
                $doc->set_property(doc_hidden,1);
                $doc->update();
                $doc->add_error(_ERR_FOLDERLIMIT);
            }
        }
    }

    // del_doc(doc) - remove a document from a folder
    function del_doc(&$doc) {
        global $DB;
        if (!$doc->get_property(doc_id)) return;
        $q = sprintf('DELETE FROM schedule WHERE folder_id=%d AND doc_id=%d',
                $this->get_property(folder_id),
                $doc->get_property(doc_id));
        $DB->write($q);
        $doc->add_error($DB->error());
        $this->reschedule();
        logmsg("Deleted schedule entry for doc=%d",
            $doc->get_property(doc_id));
        $doc->set_property(doc_folder_id,0);
    }

    // reschedule() - reschedule this folder
    function reschedule() {
        global $DB,$SFOLDER_AUTO_REMOVE;
        $q = sprintf("SELECT COUNT(*) FROM schedule WHERE folder_id=%d AND begin_date < NOW() AND end_date > NOW()",
                $this->get_property(folder_id));
        $r = $DB->read($q);
        list($num) = $DB->fetch_array($r);
        if ($num == 0) {
            $q = sprintf("UPDATE schedule SET begin_date=NULL,end_date=NULL WHERE folder_id=%d AND begin_date>NOW()",
                $this->get_property(folder_id));
            $DB->write($q);
        }
        $q = sprintf("SELECT COUNT(*) FROM schedule WHERE folder_id=%d AND begin_date IS NULL",
                $this->get_property(folder_id));
        $r = $DB->read($q);
        list($some) = $DB->fetch_array($r);
        if (!$some) return;
        // reschedule upcoming events
        $q = sprintf('UPDATE schedule SET begin_date=NULL,end_date=NULL '.
                     'WHERE folder_id=%d AND begin_date>NOW() or begin_date IS NULL',
                     $this->get_property(folder_id));
        $DB->write($q);
        $q = sprintf('SELECT schedule.doc_id FROM schedule '.
                     'LEFT JOIN docs ON (schedule.doc_id=docs.doc_id) '.
                     'WHERE folder_id=%d AND end_date IS NULL '.
                     'ORDER BY docs.doc_created,schedule.doc_id',
                $this->get_property(folder_id));
        $r = $DB->read($q);
        while(list($did) = $DB->fetch_array($r)) {
            $q = sprintf('SELECT DATE_ADD(MAX(end_date),INTERVAL 1 SECOND),NOW() FROM schedule '.
                         'WHERE folder_id=%d',
                         $this->get_property(folder_id));
            $r1 = $DB->read($q);
            list ($begin,$now) = $DB->fetch_array($r1);
            if ($begin<$now)
                $beginstr = 'NOW()';
            else
                $beginstr = sprintf("'%s'",$begin);
            $q = sprintf('UPDATE schedule SET begin_date=%s,'.
                         ' end_date=DATE_ADD(%s,INTERVAL %d %s) '.
                         'WHERE folder_id=%d and doc_id=%d',
                         $beginstr,$beginstr,
                         $this->get_property(folder_interval),
                         $this->get_property(folder_interval_type),
                         $this->get_property(folder_id),
                         $did);
            $DB->write($q);
        }
        /* remove old documents */
        if ($SFOLDER_AUTO_REMOVE) {
            $q = sprintf("SELECT doc_id FROM schedule WHERE folder_id=%d ".
                         "AND end_date<NOW() AND begin_date IS NOT NULL",
                         $this->get_property('folder_id'));
            $r = $DB->read($q);
            while(list($did) = $DB->fetch_array($r)) {
                $q = sprintf("UPDATE docs SET doc_folder_id=0 WHERE doc_id=%d",$did);
                $DB->write($q);
                $q = sprintf("DELETE FROM schedule WHERE doc_id=%d",$did);
                $DB->write($q);
            }
        }
        logmsg('Rescheduled folder %d, "%s"',
            $this->get_property(folder_id),
                $this->get_property(folder_name));
    }

}

?>
