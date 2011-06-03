#!/bin/sh
# $Id: dbmaintenance.sh,v 1.5 2003/04/08 15:05:15 glen Exp $
# Copyright (c)2002, Broadpool, LLC. All Rights Reserved.
# Performs regular database maintenance
#

if [ $# != 1 ]; then
  echo Usage:
  echo $0 [database]
  exit
fi

echo `date` $0: $* STARTED

/usr/local/bin/mysql $1 <<ENDMYSQL
REPAIR TABLE users;
OPTIMIZE TABLE users;
REPAIR TABLE docs;
OPTIMIZE TABLE docs;
REPAIR TABLE comments;
OPTIMIZE TABLE comments;
REPAIR TABLE folders;
OPTIMIZE TABLE folders;
REPAIR TABLE ratings;
OPTIMIZE TABLE ratings;
REPAIR TABLE doc_categories;
OPTIMIZE TABLE doc_categories;
ENDMYSQL

echo `date` $0: $* FINISHED
