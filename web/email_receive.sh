#!/bin/sh
# $Id: email_receive.sh,v 1.10 2002/10/09 23:46:31 glen Exp $
# Copyright (c)2002, Broadpool, LLC. All Rights Reserved.

if ! PREFIX=$(expr $0 : "\(/.*\)/email_receive\.sh\$"); then
    echo "$0: Cannot determine the PREFIX" >&2
    exit 1
fi

cd $PREFIX

/usr/local/bin/php -q ${PREFIX}/email_receive.php $@
