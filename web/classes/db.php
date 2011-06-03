<?php
// db.php
// $Id: db.php,v 1.17 2006/11/12 15:41:48 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This file defines the DB object, which provides all database access
// functions. It is intended to isolate users from the actual implementation
// of the database, so that the system can be ported to other DBMS systems
// in the future.

class Db extends Siteframe {
var $readconnect,
    $writeconnect,
    $num_reads,
    $num_writes;

    /* Db - constructor function
    */
    function Db() {
        // since this function is called before the language files
        // are loaded, literal string messages are used instead of
        // the defined message constants
        $this->readconnect = mysql_connect(DBHOST,DBUSER,DBPASS);
        if (!$this->readconnect) {
            siteframe_abort('readconnect: failed to connect to %s database %s, %s',
              'MySQL',DBHOST,mysql_error());
        }
        if (!mysql_select_db(DBNAME,$this->readconnect)) {
            siteframe_abort('readconnect: failed to select database %s, %s',
              DBNAME, mysql_error());
        }
        $this->writeconnect = mysql_connect(DBWRITE,DBUSER,DBPASS);
        if (!$this->writeconnect) {
            siteframe_abort('writeconnect: failed to connect to %s database %s, %s',
              'MySQL',DBWRITE,mysql_error());
        }
        if (!mysql_select_db(DBNAME,$this->writeconnect)) {
            siteframe_abort('writeconnect: failed to select database %s, %s',
              DBNAME,mysql_error());
        }
        $this->num_reads = $this->num_writes = 0;
    }

    /* read(statement) - executes the specified query statement
    **   returns resource handle
    **   read is used for SELECT statements
    */
    function read($statement) {
        $verb = strtoupper(substr($statement,0,6));
        if ($verb!='SELECT') {
          die("Only SELECT statements allowed in DB->read, invalid [$statement]");
        }
        $this->num_reads++;
        /*$this->reads[] = $statement;*/
        return mysql_query($statement,$this->readconnect);
    }

    /* write(statement) - executes the specified query statement
    **   returns resource handle
    **   write should be used for INSERT, UPDATE, DELETE
    */
    function write($statement) {
        $verb = strtoupper(substr($statement,0,6));
        if ($verb=='SELECT') {
          die("SELECT statements not allowed in DB->write, invalid [$statement]");
        }
        $this->num_writes++;
        return mysql_query($statement,$this->writeconnect);
    }

    /* fetch_array(resource) - return row from resource
    */
    function fetch_array($resource) {
        $a = mysql_fetch_array($resource);
        return $a;
    }

    /* insert_id() - returns the auto_increment ID of the last INSERT statement
    */
    function insert_id() {
        return mysql_insert_id();
    }

    /* affected_rows() - returns the number of rows affected by an INSERT or UPDATE
    */
    function affected_rows() {
        return mysql_affected_rows();
    }

    /* error() - returns the database error string
    */
    function error() {
        if (mysql_errno()!=0)
            return sprintf("%d:%s",mysql_errno(),mysql_error());
        else
            return false;
    }

    /* errno() - returns the database error number
    */
    function errno() {
        return mysql_errno();
    }

    /* num_rows(result) - returns number of rows in result set
    */
    function num_rows($result) {
        return mysql_num_rows($result);
    }

    /* num_fields(result) - number of fields in result set
    */
    function num_fields($result) {
        return mysql_num_fields($result);
    }

    /* field_name(result,pos) - return the name of the field ins result at pos
    */
    function field_name($result,$pos) {
        return mysql_field_name($result,$pos);
    }
}

?>
