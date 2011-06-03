<?php
/* globals.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: globals.php,v 1.5 2003/06/07 01:27:23 glen Exp $
**
** functions that work with global properties
*/

// set_global(prop,value) - set a global property
function set_global($prop,$val) {
    global $DB;
    $q = sprintf("UPDATE properties SET value='%s' WHERE name='%s'",
            $val, strtoupper(clean($prop)));
    $DB->write($q);
    if ($DB->affected_rows()!=1) {
        $q = sprintf("INSERT INTO properties (name,value) VALUES ('%s','%s')",
                     strtoupper(clean($prop)), $val);
        $DB->write($q);
        if ($DB->affected_rows()!=1) {
            return 0;
        }
    }
    logmsg("Global property %s updated",strtoupper(clean($prop)));
    return 1;
}
?>