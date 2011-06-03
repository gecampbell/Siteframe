#!/bin/bash
# $Id: backup.sh,v 1.1.1.1 2002/09/05 16:43:14 glen Exp $
# requires three parameters
# 1 - database name
# 2 - directory
# 3 - email for report

if [[ $# = 3 ]]; then
   echo `date` beginning processing
   echo Database: $1
   echo Directory: $2
   echo Email: $3
else
   echo Syntax:
   echo '        ' $0 database directory
   exit
fi

# modify to point to your saved locations
BACKUPS=/u1/backups

echo `date` dumping database $1
mv ${BACKUPS}/$1.dump ${BACKUPS}/$1.dump.1
/usr/local/bin/mysqldump $1 >${BACKUPS}/$1.dump

echo `date` creating tar file of $2
mv ${BACKUPS}/$1.tar.gz ${BACKUPS}/$1.tar.gz.1
cd $2
tar cvf ${BACKUPS}/$1.tar . >/tmp/$1.list
echo backup file list in /tmp/$1.list
gzip -f ${BACKUPS}/$1.tar

echo `date` emailing nightly log report
/usr/local/bin/mysql -uglen $1 <<LOG | mail -s "nightly report for $1" $3
  SELECT event_date,message
  FROM activity;
  DELETE FROM activity;
  INSERT INTO activity (event_date,message) VALUES (NOW(),'log initialized');
LOG
