#!/bin/sh
# Copyright (c)2001, Broadpool, LLC. All rights reserved.
# $Id: hourly.sh,v 1.4 2003/06/21 16:46:42 glen Exp $

# change this to use the path to your PHP executable
PHP=/usr/local/bin/php

echo `date` $0: $* STARTED

# move to the specified directory
cd $1

${PHP} -q -d max_execution_time=0 ./hourly.php

echo `date` $0: $* FINISHED
