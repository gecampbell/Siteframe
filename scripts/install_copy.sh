#!/bin/sh
# Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
# $Id: install_copy.sh,v 1.14 2003/06/13 22:26:35 glen Exp $
# This script is used to install a linked copy of Siteframe
# in the specified directory. PREFIX should point to the
# live "pristine" copy of the software.
#
# Syntax: install_copy.sh <directory>
#
# N.B. You must still copy (somewhere) the config.php file
# for the site

# N.B. (2) You will probably need to make other modifications
# to this file to get it to work on your server

# set these for the group of your webserver
# common values are 'www', 'apache', or 'nobody'
WWWGROUP=www

if ! PREFIX=$(expr $0 : "\(/.*\)/scripts/$(basename $0)\$"); then
    echo "$0: Cannot determine the PREFIX" >&2
    exit 1
fi

echo `date` $0: $* STARTED
echo `date` installing from $PREFIX

# removing old links
find $1 -type l -exec rm {} \;

echo `date` making directories
if [ ! -e $1/files ]; then
    mkdir $1/files
fi
if [ ! -e $1/admin ]; then
    mkdir $1/admin
fi
if [ ! -e $1/themes ]; then
    mkdir $1/themes
    cp -R ${PREFIX}/web/themes/* $1/themes/
fi
if [ ! -e $1/macros ]; then
    mkdir $1/macros
fi
chgrp -R ${WWWGROUP} $1/files >/dev/null
chmod -R g+w $1/files $1/themes >/dev/null
cat <<ENDCAT1
  This script has created the directory:
    $1/files
  and changed its group ownership to:
    ${WWWGROUP}
  with write permissions enabled. You should make sure that this
  directory is writable by your web server software so that you
  can upload files and images.
ENDCAT1

echo `date` copying
if [ ! -e $1/config.php ]; then
    cp ${PREFIX}/web/config.php $1
fi
cp ${PREFIX}/web/macros/*.macro $1/macros/

find $1 -name CVS -exec rm -rf {} \;

echo `date` making links - ignore errors here
ln -s ${PREFIX}/web/* $1/
ln -s ${PREFIX}/web/.htaccess $1/
ln -s ${PREFIX}/web/admin/* $1/admin/

find $1 -name 'CVS' -exec rm {} \;

echo `date` $0: $* FINISHED
