#!/bin/sh
# Copyright (c)2001, Broadpool, LLC. All rights reserved.
# $Id: daily.sh,v 1.3 2006/07/15 15:03:03 glen Exp $

echo `date` $0: $* STARTED

cd $1
/usr/local/bin/php -q -d max_execution_time=3600 ./daily.php

echo `date` $0: $* FINISHED
