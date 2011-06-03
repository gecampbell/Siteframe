/* siteframe.sql
** Copyright (c)2001, Broadpool, LLC. All rights reserved.
** Siteframe database schema
** $Id: siteframe201.sql,v 1.3 2003/05/06 22:16:49 glen Exp $
*/

/* users - contains user information
** user_id:         unique ID
** created:         date/time record created
** modified:        date/time record modified
** user_status:     0 (invalid), 1 (valid), 127 (administrator)
** user_firstname,user_lastname: doh!
** user_email:      email address
** user_passwd:     encrypted password (using password() function)
** user_props:      other properties in XML format <prop>value</prop>
*/
drop table if exists users;
create table users (
    user_id         bigint          not null auto_increment,
    created         datetime        not null,
    modified        datetime        not null,
    user_status     tinyint         not null,
    user_firstname  varchar(250),
    user_lastname   varchar(250)    not null,
    user_email      varchar(250)    not null,
    user_passwd     varchar(250)    not null,
    user_props      text,
    primary key (user_id)
);
create unique index users_lastname_ndx on users(user_email);
create fulltext index users_full_ndx
    on users(user_firstname,user_lastname,user_props);

/* docs - core document information
**   all other documents extend the basic document class
** doc_id:          unique ID
** created:         date/time document created
** modified:        date/time document modified
** doc_type:        a document type code
** owner_id:        user ID of document owner
** folder_id:       (optional) ID of document folder
** hidden:          (0,1) a HIDDEN document is visible only to owner and admin
** title:           document title - every document has a title!
** body:            document body
*/
drop table if exists docs;
create table docs (
    doc_id          bigint          not null auto_increment,
    created         datetime        not null,
    modified        datetime        not null,
    doc_hidden      tinyint         not null,
    doc_folder_id   bigint,
    doc_owner_id    bigint          not null,
    doc_tag         varchar(32),
    doc_type        varchar(20)     not null,
    doc_title       varchar(250)    not null,
    doc_body        text            not null,
    doc_props       text,
    primary key (doc_id)
);
create unique index docs_owner_title_ndx
    on docs(doc_type,doc_owner_id,doc_title);
create fulltext index docs_full_ndx on docs(doc_title,doc_body,doc_props);
create index docs_created_ndx on docs(created);
create index doc_folder_id_ndx on docs(doc_folder_id);

/* folders - a container for documents
** folder_id:       unique ID
** created,modified: the usual
** folder_owner_id: user ID of folder owner
** folder_name:     unique string
** folder_body:     descriptive text
** folder_props:    additional properties
*/
drop table if exists folders;
create table folders (
    folder_id       bigint          not null auto_increment,
    created         datetime        not null,
    modified        datetime        not null,
    folder_owner_id bigint          not null,
    folder_parent_id bigint         not null,
    folder_public   tinyint         not null,
    folder_children tinyint         not null,
    folder_type     varchar(20)     not null,
    folder_name     varchar(250)    not null,
    folder_body     text,
    folder_props    text,
    primary key (folder_id)
);
create unique index folder_name_ndx on folders(folder_name);
create fulltext index folder_ndx on folders(folder_name,folder_body,folder_props);
create index folder_parent_id_ndx on folders(folder_parent_id);

/* polls - user-defined multiple-choice questions
*/
drop table if exists polls;
/* the poll-table is no longer used */

/* poll_votes - holds votes per user
**   since there is one vote per user allowed, the key is the doc_id
**   and the user_id. Tshe question ID is the vote itself, which can
**   then be tallied
** doc_id:          relates to docs.doc_id
** user_id:         ID of the user who is voting
** question_id:     which question was selected
*/
drop table if exists poll_votes;
create table poll_votes (
    doc_id          bigint          not null,
    user_id         bigint          not null,
    question_id     bigint          not null,
    primary key (doc_id,user_id)
);

/* schedule - used for scheduled folders
**  folder_id       unique identifier
**  doc_id          the document scheduled
**  created         time the item was put into the folder
**  begin_date      start time to schedule
**  end_date        end time to schedule
*/
drop table if exists schedule;
create table schedule (
    folder_id       bigint          not null,
    doc_id          bigint          not null,
    created         datetime        not null,
    begin_date      datetime,
    end_date        datetime,
    primary key (folder_id,doc_id)
);

/* events - used for event documents
**
*/
drop table if exists events;
create table events (
    doc_id          bigint          not null,
    event_begin     datetime        not null,
    event_end       datetime        not null,
    event_private   tinyint         not null,
    primary key (doc_id)
);

/* comments - can be added to documents, not folders
**   the data model allows comments to be anonymous, but this may
**   not be allowed by the web interface
** doc_id:          relates to docs.doc_id
** comment_id:      unique identifier
** reply_to:        in response to another comment
** owner_id:        user ID of comment author
** body:            text of comment
*/
drop table if exists comments;
create table comments (
    comment_id      bigint          not null auto_increment,
    doc_id          bigint          not null,
    reply_to        bigint,
    owner_id        bigint          not null,
    created         datetime        not null,
    body            text            not null,
    comment_props   text,
    primary key (comment_id)
);
create fulltext index comments_full_ndx on comments(body,comment_props);

/* ratings - can be placed on documents, not folders
**   only one rating per user - no anonymous ratings
** doc_id:          relates to docs.doc_id
** user_id:         relates to users.user_id
** rating:          an integer value
*/
drop table if exists ratings;
create table ratings (
    doc_id          bigint          not null,
    user_id         bigint          not null,
    rating          int             not null,
    primary key (doc_id,user_id)
);
create index ratings_doc_ndx on ratings (doc_id);

/* properties - global property values
** name:            unique ID
** value:           a value
*/
drop table if exists properties;
create table properties (
    name            varchar(250)    not null,
    value           varchar(250),
    primary key (name)
);
insert into properties values('INIT',NOW());

/* activity - stores an online activity log
** event_id:        unique auto_increment ID
** event_date:      date of event
** message:         log message
*/
drop table if exists activity;
create table activity (
    event_id        bigint          not null auto_increment,
    event_date      datetime        not null,
    message         varchar(250)    not null,
    primary key (event_id)
);
insert into activity (event_date,message) values
(NOW(),'Log initialized');

/* sessions - track visitor sessions
** session_id       unique ID
** session_date     datetime
** session_uid      user ID or (0)
*/
drop table if exists sessions;
create table sessions (
    session_id      bigint          not null auto_increment,
    session_date    datetime        not null,
    session_uid     bigint          not null,
    primary key (session_id)
);

/* categories - document categories
*/
drop table if exists categories;
create table categories (
    category_id     bigint          not null auto_increment,
    category        varchar(250)    not null,
    primary key (category_id)
);
create unique index category_category_ndx on categories (category);

/* doc_categories - categories per document
*/
drop table if exists doc_categories;
create table doc_categories (
    doc_id          bigint          not null,
    category_id     bigint          not null,
    primary key (doc_id,category_id)
);
