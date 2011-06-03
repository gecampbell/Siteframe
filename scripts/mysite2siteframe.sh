#!/bin/bash
# Copyright (c)2001, Broadpool, LLC. All rights reserved.
# $Id: mysite2siteframe.sh,v 1.1.1.1 2002/09/05 16:43:14 glen Exp $
#------------------------------------------------------------------------
# mysite2siteframe.sh - this script will convert (as best as possible) a
# a "mysite" (v2) database to a "Siteframe" (v1) database.
# DISCLAIMER: It's possible that not every data type will be able to be
# converted. Also, some relationships in mysite may not be able to be 
# replicated in siteframe.
#------------------------------------------------------------------------

# change this path if necessary to reflect your system
TMP=/tmp
SITEFRAME=/www/siteframe

# source DB
DB1=fujiv2
DB1PATH=/www/hosts/fujirangefinder.com
# target DB
DB2=fujirangefinder
DB2PATH=/www/hosts/dev.fujirangefinder.com

# start processing
echo `date` converting $DB1 to $DB2
echo `date` creating files directory: do not worry if there is an error here
rm -rf ${DB2PATH}/files
mkdir ${DB2PATH}/files
chmod go+w ${DB2PATH}/files

# create database tables
echo `date` creating database tables
cd ${DB2PATH}
mysql ${DB2} < ${SITEFRAME}/sql/siteframe.sql
mysql ${DB2} <<X1
    DROP TABLE IF EXISTS TMPusers;
    DROP TABLE IF EXISTS TMPfolders;
    DROP TABLE IF EXISTS TMPcomments; 
    DROP TABLE IF EXISTS TMParticles;
    DROP TABLE IF EXISTS TMPimages;
    DROP TABLE IF EXISTS TMPimage_folders;
    DROP TABLE IF EXISTS TMPratings;
X1

# users
echo `date` users
mysqldump ${DB1} users > ${TMP}/users.dump
perl -i -p -e 's/TABLE users/TABLE TMPusers/g;' ${TMP}/users.dump
perl -i -p -e 's/INTO users/INTO TMPusers/g;' ${TMP}/users.dump
perl -i -p -e 's/\@\@LINK\(\s*\\"([^\\]+)\\"\s*,\s*\\"([^\\]+)\\"\s*\)/<a href=\\"$2\\">$1<\/a>/g;' ${TMP}/users.dump
mysql ${DB2} < ${TMP}/users.dump
mysql ${DB2} <<USER1
    DELETE FROM users;
    INSERT INTO users (user_id,created,modified,user_status,
        user_firstname,user_lastname,user_email,user_passwd,user_props)
    SELECT
        user_id,
        created,
        NOW(),
        1,
        firstname,
        lastname,
        email,
        password,
        CONCAT('<properties>',
            '<version>0.0.1</version>',
            '<user_description><![CDATA[',info,']]></user_description>',
            '<user_homepage></user_homepage>',
            '</properties>')
    FROM TMPusers;
    UPDATE users
        SET user_status=99
    WHERE user_id=1;
USER1

# folders
echo `date` folders
mysqldump ${DB1} folders > ${TMP}/folders.dump
perl -i -p -e 's/TABLE folders/TABLE TMPfolders/g;' ${TMP}/folders.dump
perl -i -p -e 's/INTO folders/INTO TMPfolders/g;' ${TMP}/folders.dump
perl -i -p -e 's/\@\@LINK\(\s*\\"([^\\]+)\\"\s*,\s*\\"([^\\]+)\\"\s*\)/<a href=\\"$2\\">$1<\/a>/g;' ${TMP}/folders.dump
mysql ${DB2} < ${TMP}/folders.dump
mysql ${DB2} <<FOLD1
    DELETE FROM folders;
    INSERT INTO folders (folder_id,created,modified,folder_owner_id,folder_public,folder_type,
        folder_name,folder_body,folder_props)
    SELECT folder_id,created,NOW(),owner_id,public,'Folder',folder_name,description,
        CONCAT('<properties><user_limit>',
               user_limit,
               '</user_limit><folder_limit_type>Article</folder_limit_type>',
               '<allow_ratings>1</allow_ratings>',
               '<allow_comments>1</allow_comments>',
               '</properties>')
    FROM TMPfolders
    WHERE object_id=1;
    INSERT INTO folders (folder_id,created,modified,folder_owner_id,folder_public,folder_type,
        folder_name,folder_body,folder_props)
    SELECT folder_id,created,NOW(),owner_id,public,'Folder',folder_name,description,
        CONCAT('<properties><user_limit>',
               user_limit,
               '</user_limit><folder_limit_type>Image</folder_limit_type>',
               '<allow_ratings>1</allow_ratings>',
               '<allow_comments>1</allow_comments>',
               '</properties>')
    FROM TMPfolders
    WHERE object_id=3;
    INSERT INTO folders (folder_id,created,modified,folder_owner_id,folder_public,folder_type,
        folder_name,folder_body,folder_props)
    SELECT folder_id,created,NOW(),owner_id,public,'Folder',folder_name,description,
        CONCAT('<properties><user_limit>',
               user_limit,
               '</user_limit><folder_limit_type>Event</folder_limit_type>',
               '<allow_ratings>1</allow_ratings>',
               '<allow_comments>1</allow_comments>',
               '</properties>')
    FROM TMPfolders
    WHERE object_id=2;
    UPDATE folders 
        SET folder_type = 'PFolder'
    WHERE folder_public!=0;
FOLD1

# comments
echo `date` comments
mysqldump ${DB1} comments > ${TMP}/comments.dump
perl -i -p -e 's/TABLE comments/TABLE TMPcomments/g;' ${TMP}/comments.dump
perl -i -p -e 's/INTO comments/INTO TMPcomments/g;' ${TMP}/comments.dump
mysql ${DB2} < ${TMP}/comments.dump

# events
echo `date` events
mysqldump ${DB1} events > ${TMP}/events.dump
perl -i -p -e 's/TABLE events/TABLE TMPevents/g;' ${TMP}/events.dump
perl -i -p -e 's/INTO events/INTO TMPevents/g;' ${TMP}/events.dump
mysql ${DB2} < ${TMP}/events.dump
mysql ${DB2} <<EVENT1
    INSERT INTO docs (created,modified,doc_hidden,doc_folder_id,doc_owner_id,
        doc_type,doc_title,doc_body,doc_props)
    SELECT
        NOW(),
        NOW(),
        0,
        folder_id,
        owner_id,
        'Event',
        title,
        description,
        CONCAT('<properties>',
               '<allow_ratings>1</allow_ratings>',
               '<allow_comments>1</allow_comments>',
               '</properties>')
    FROM TMPevents;
    INSERT INTO events (doc_id,event_begin,event_end)
    SELECT doc_id,event_date,event_date
    FROM TMPevents LEFT JOIN docs ON
        (TMPevents.title = docs.doc_title AND
         TMPevents.folder_id = docs.doc_folder_id AND
         TMPevents.owner_id = docs.doc_owner_id);
    INSERT INTO comments (doc_id,reply_to,owner_id,created,body,comment_props)
    SELECT
        docs.doc_id,
        0,
        TMPcomments.author_id,
        TMPcomments.created,
        comment,
        CONCAT(
            '<properties>',
            '<comment_subject>',TMPcomments.title,'</comment_subject>',
            '</properties>')
    FROM
        TMPcomments LEFT JOIN TMPevents ON
            (SUBSTRING_INDEX(TMPcomments.page,'=',-1)
                = TMPevents.event_id)
        LEFT JOIN docs ON (docs.doc_title = TMPevents.title AND 
                           docs.doc_owner_id = TMPevents.owner_id)
        WHERE TMPcomments.page LIKE '%event%';
EVENT1

# articles
echo `date` articles
mysqldump ${DB1} articles > ${TMP}/articles.dump
perl -i -p -e 's/TABLE articles/TABLE TMParticles/g;' ${TMP}/articles.dump
perl -i -p -e 's/INTO articles/INTO TMParticles/g;' ${TMP}/articles.dump
# fix @@LINK
perl -i -p -e 's/\@\@LINK\(\s*\\"([^\\]+)\\"\s*,\s*\\"([^\\]+)\\"\s*\)/<a href=\\"$2\\">$1<\/a>/g;' ${TMP}/articles.dump
mysql ${DB2} < ${TMP}/articles.dump
mysql ${DB2} <<ART1
    INSERT INTO docs (created,modified,doc_hidden,doc_folder_id,doc_owner_id,
        doc_type,doc_title,doc_body,doc_props)
    SELECT
        created,
        NOW(),
        hidden,
        folder_id,
        author_id,
        'Article',
        title,
        body,
        '<properties><doc_summary></doc_summary></properties>'
    FROM TMParticles;
    INSERT INTO comments (doc_id,reply_to,owner_id,created,body,comment_props)
    SELECT
        docs.doc_id,
        0,
        TMPcomments.author_id,
        TMPcomments.created,
        comment,
        CONCAT(
            '<properties>',
            '<comment_subject>',TMPcomments.title,'</comment_subject>',
            '</properties>')
    FROM
        TMPcomments LEFT JOIN TMParticles ON
            (SUBSTRING_INDEX(TMPcomments.page,'=',-1)
                = TMParticles.article_id)
        LEFT JOIN docs ON (docs.doc_title = TMParticles.title AND 
                           docs.doc_owner_id = TMParticles.author_id)
        WHERE TMPcomments.page LIKE '%article%';
ART1

# image_folders
echo `date` image_folders
mysqldump ${DB1} image_folders > ${TMP}/image_folders.dump
perl -i -p -e 's/TABLE image_folders/TABLE TMPimage_folders/g;' ${TMP}/image_folders.dump
perl -i -p -e 's/INTO image_folders/INTO TMPimage_folders/g;' ${TMP}/image_folders.dump
# images
echo `date` images
mysqldump ${DB1} images > ${TMP}/images.dump
perl -i -p -e 's/TABLE images/TABLE TMPimages/g;' ${TMP}/images.dump
perl -i -p -e 's/INTO images/INTO TMPimages/g;' ${TMP}/images.dump
# ratings
echo `date` ratings
mysqldump ${DB1} ratings > ${TMP}/ratings.dump
perl -i -p -e 's/TABLE ratings/TABLE TMPratings/g;' ${TMP}/ratings.dump
perl -i -p -e 's/INTO ratings/INTO TMPratings/g;' ${TMP}/ratings.dump
# consolidate
mysql ${DB2} < ${TMP}/image_folders.dump
mysql ${DB2} < ${TMP}/images.dump
mysql ${DB2} < ${TMP}/ratings.dump
mysql ${DB2} <<IMG1
    INSERT INTO docs (created,modified,doc_hidden,doc_folder_id,doc_owner_id,
        doc_type,doc_title,doc_body,doc_props)
    SELECT
        created,
        NOW(),
        0,
        folder_id,
        owner_id,
        'Image',
        title,
        description,
        CONCAT('<properties><doc_file>',
            REPLACE(imagefile,'images/','./files/'),
            '</doc_file>',
            '<version>0.0.0</version>',
            '<doc_file_mime_type>image/jpeg</doc_file_mime_type>',
            '<allow_ratings>1</allow_ratings>',
            '<allow_comments>1</allow_comments>',
            '<copying_allowed></copying_allowed>',
            '<image_model_release></image_model_release>',
            '<image_camera></image_camera>',
            '<image_lens></image_lens>',
            '<image_film></image_film>',
            '<image_exposure></image_exposure>',
            '<image_copyright></image_copyright>',
            '<image_flash></image_flash>',
            '<image_support>N/R</image_support>',
            '</properties>')
    FROM TMPimages LEFT JOIN TMPimage_folders ON (TMPimages.image_id=TMPimage_folders.image_id);
    INSERT INTO comments (doc_id,reply_to,owner_id,created,body,comment_props)
    SELECT
        docs.doc_id,
        0,
        TMPcomments.author_id,
        TMPcomments.created,
        comment,
        CONCAT(
            '<properties>',
            '<comment_subject>',TMPcomments.title,'</comment_subject>',
            '</properties>')
    FROM
        TMPcomments LEFT JOIN TMPimages ON
            (SUBSTRING_INDEX(TMPcomments.page,'=',-1)
                = TMPimages.image_id)
        LEFT JOIN docs ON (docs.doc_title = TMPimages.title AND 
                           docs.doc_owner_id = TMPimages.owner_id)
    WHERE TMPcomments.page LIKE '%image%';
    INSERT INTO ratings (doc_id,user_id,rating)
    SELECT
        docs.doc_id,
        TMPratings.rated_by,
        TMPratings.rating
    FROM
        TMPratings LEFT JOIN TMPimages ON (TMPimages.image_id=TMPratings.image_id)
                   LEFT JOIN docs ON (docs.doc_title=TMPimages.title);
IMG1
echo `date` copying image files
cp -v -R ${DB1PATH}/images/* ${DB2PATH}/files
echo `date` removing extraneous files
find ${DB2PATH}/files -name '*/*-*.jpg' -exec rm -f {} \;
echo `date` updating permissions
chmod go+w ${DB2PATH}/files/*
echo `date` updating image database
CURRENT=`pwd`
cd ${DB2PATH}
php -q -d max_execution_time=0 ./resizeimages.php

# critique
# NOT CONVERTED

# event_types
# NOT CONVERTED


# faq

# guestbook

# links

# log
# NOT CONVERTED

# msgs
# NOT CONVERTED

# notices
# NOT CONVERTED

# objects

# rating_values

# ratings

# reports

# siteprefs

# final cleanup
echo `date` removing temporary tables
mysql ${DB2} <<END
    DROP TABLE TMPusers;
    DROP TABLE TMPfolders;
    DROP TABLE TMPcomments;
    DROP TABLE TMPevents;
    DROP TABLE TMPimages;
    DROP TABLE TMPimage_folders;
    DROP TABLE TMPratings;
END
chmod -R go+rw ${DB2PATH}/files/*
echo `date` done
