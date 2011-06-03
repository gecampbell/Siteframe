#!/bin/sh
# $Id: clearlog.sh,v 1.3 2003/04/08 15:05:15 glen Exp $
# Copyright (c)2002-2003, Broadpool, LLC. All Rights Reserved.
# Clears the activity log
#

if [ $# != 1 ]; then
  echo Usage:
  echo $0 [database]
  exit
fi

echo `date` $0: $* STARTED

/usr/local/bin/mysql $1 <<ENDLOG
  DELETE FROM activity
  WHERE event_date < DATE_SUB(NOW(),INTERVAL 7 DAY);
ENDLOG

echo `date` $0: $* FINISHED
