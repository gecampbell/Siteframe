#!/bin/sh
# $Id: unload_templates.sh,v 1.3 2003/07/06 12:50:43 glen Exp $
# this script dumps the themes and templates from a database
# you should redirect the output to a file
mysqldump --skip-opt -t $1 themes templates | \
  grep -v '^--' | \
  perl -p -e 's/templates VALUES \([0-9]+,/templates VALUES (NULL,/;'
