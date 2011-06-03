#!/bin/sh
# rebuild_siteframe_db.sh
# given the name of a siteframe database as a parameter, it unloads all the
# data, rebuilds the database, and reloads the data
# Copyright (c)2001, Broadpool, LLC. All rights reserved.
# $Id: rebuild_siteframe_db.sh,v 1.5 2002/11/12 01:25:01 glen Exp $

TEMP=/var/tmp

# change this line if necessary to point to your Siteframe installation
if ! PREFIX=$(expr $0 : "\(/.*\)/scripts/$(basename $0)\$"); then
    echo "$0: Cannot determine the PREFIX" >&2
    exit 1
fi

echo `date` dumping database $1
mysqldump -elq -t $1 > ${TEMP}/$1.dump

echo `date` rebuilding database structures
mysql $1 < ${PREFIX}/sql/siteframe.sql
mysql $1 <<END
  DELETE FROM activity;
  DELETE FROM properties;
  DELETE FROM objs;
  DELETE FROM obj_props;
END

echo `date` reloading data
mysql -fn $1 < ${TEMP}/$1.dump

echo `date` dump file is in ${TEMP}/$1.dump
echo `date` done
