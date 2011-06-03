/* siteframe.sql
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
** Siteframe database schema
** $Id: siteframe.sql,v 1.52 2005/05/06 18:40:28 glen Exp $
**
** PLEASE NOTE THAT THIS FILE HAS CHANGED!
** Referential constraints have been added that require the use
** of MySQL 4.0 or tables in previous versions. If you do
** not have access to the correct version of MySQL, then you can
** edit this file to remove the "foreign key" clauses.
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
    user_created    datetime        not null,
    user_modified   datetime        not null,
    user_status     tinyint         not null,
    user_firstname  varchar(250),
    user_lastname   varchar(250)    not null,
    user_nickname   varchar(250),
    user_email      varchar(250)    not null,
    user_passwd     varchar(250)    not null,
    user_cookie     varchar(250)    not null,
    user_props      text,
    primary key (user_id)
);
alter table users
  add unique users_email_ndx (user_email),
  add fulltext users_full_ndx
      (user_firstname,user_lastname,user_nickname,user_props),
  add index users_name_ndx (user_lastname,user_firstname),
  add unique user_cookie_ndx (user_cookie),
  add index users_nickname_ndx (user_nickname);

/* docs - core document information
**   all other documents extend the basic document class
** doc_id:          unique ID
** doc_version:     user for version control (future feature)
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
    doc_version     bigint          not null,
    doc_created     datetime        not null,
    doc_modified    datetime        not null,
    doc_hidden      tinyint         not null,
    doc_folder_id   bigint,
    doc_owner_id    bigint          not null,
    doc_tag         varchar(32),
    doc_type        varchar(20)     not null,
    doc_title       varchar(250)    not null,
    doc_body        text            not null,
    doc_props       text,
    primary key (doc_id),
    foreign key (doc_owner_id) references users (user_id) on delete cascade
);
alter table docs
    add unique docs_owner_title_ndx
        (doc_type,doc_owner_id,doc_title),
    add fulltext docs_full_ndx (doc_title,doc_body,doc_props),
    add index doc_owner_id_ndx (doc_owner_id),
    add index doc_folder_id_ndx (doc_folder_id),
    add index doc_type_ndx (doc_type),
    add index doc_tag_ndx (doc_tag),
    add index doc_hidden_ndx (doc_hidden),
    add index doc_title_ndx (doc_title),
    add index doc_created_ndx (doc_created),
    add index doc_modified_ndx (doc_modified);

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
    primary key (folder_id),
    foreign key (folder_owner_id) references users (user_id) on delete cascade
);
alter table folders
    add unique folder_name_ndx (folder_name),
    add fulltext folder_ndx (folder_name,folder_body,folder_props),
    add index folder_owner_id_ndx (folder_owner_id),
    add index folder_parent_id_ndx (folder_parent_id);

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
    primary key (doc_id,user_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade,
    foreign key (user_id) references users (user_id) on delete cascade
);
alter table poll_votes
    add index poll_votes_doc_id_ndx (doc_id);

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
    primary key (folder_id,doc_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade
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
    primary key (doc_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade
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
    comment_doc_id  bigint          not null,
    comment_reply_to  bigint,
    comment_owner_id  bigint        not null,
    comment_created datetime        not null,
    comment_body    text            not null,
    comment_props   text,
    primary key (comment_id),
    foreign key (comment_doc_id) references docs (doc_id) on delete cascade,
    foreign key (comment_owner_id) references users (user_id) on delete cascade
);
alter table comments
    add fulltext comments_full_ndx (comment_body,comment_props),
    add index comment_doc_id_ndx (comment_doc_id),
    add index comment_owner_id_ndx (comment_owner_id);

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
    primary key (doc_id,user_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade,
    foreign key (user_id) references users (user_id) on delete cascade
);
alter table ratings
    add index ratings_doc_ndx (doc_id),
    add index ratings_user_id_ndx (user_id);

/* properties - global property values
** name:            unique ID
** value:           a value
*/
drop table if exists properties;
create table properties (
    name            varchar(250)    not null,
    value           text,
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
** referer
** agent            user agent
** remote IP
**
*/
drop table if exists sessions;
create table sessions (
    session_id      bigint          not null auto_increment,
    session_date    datetime        not null,
    session_uid     bigint          not null,
    remote_ip       varchar(50),
    referer         varchar(250),
    agent           varchar(250),
    authuser        varchar(250),
    primary key (session_id)
);

/* categories - document categories
*/
drop table if exists categories;
create table categories (
    cat_id          bigint          not null auto_increment,
    cat_name        varchar(250)    not null,
    cat_doc_type    varchar(20),
    cat_description text,
    primary key (cat_id)
);
alter table categories
    add unique category_category_ndx (cat_name),
    add index categories_cat_doc_type_ndx (cat_doc_type);

/* doc_categories - categories per document
*/
drop table if exists doc_categories;
create table doc_categories (
    doc_id          bigint          not null,
    doc_cat_id      bigint          not null,
    primary key (doc_id,doc_cat_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade,
    foreign key (doc_cat_id) references categories (cat_id) on delete cascade
);
alter table doc_categories
    add index doc_categories_doc_id_ndx (doc_id),
    add index doc_categories_doc_cat_id_ndx (doc_cat_id);

/* rss - holds RSS feeds
*/
drop table if exists rss;
create table rss (
    rss_url         varchar(250)    not null,
    rss_loaded      datetime        not null,
    rss_text        text            not null,
    primary key (rss_url)
);

/* new tables added in release 2.2 */

/* objs - document classes
*/
drop table if exists objs;
create table objs (
    obj_id          bigint          not null auto_increment,
    obj_active      tinyint         not null,
    obj_class       varchar(30)     not null,
    obj_class_file  varchar(50)     not null,
    primary key (obj_id)
);
alter table objs
    add unique objs_class_ndx (obj_class);
insert into objs (obj_id,obj_active,obj_class,obj_class_file) values
(1, 1, 'Article', 'article.php'),
(2, 0, 'Notice',  'notice.php'),
(3, 0, 'Ad',      'ad.php'),
(4, 0, 'Event',   'event.php'),
(5, 1, 'DocFile', 'file.php'),
(6, 1, 'Image',   'image.php'),
(7, 0, 'Link',    'link.php'),
(8, 0, 'Poll',    'poll.php'),
(9, 1, 'User',    'user.php');

/* obj_props - information on local object properties
*/
drop table if exists obj_props;
create table obj_props (
    obj_prop_id     bigint          not null auto_increment,
    obj_id          bigint          not null,
    obj_prop_name   varchar(250)    not null,
    obj_prop_seq    tinyint         not null,
    obj_prop_type   varchar(20)     not null,
    obj_prop_size   integer,
    obj_prop_prompt varchar(250)    not null,
    obj_prop_doc    text,
    obj_prop_admin  tinyint         not null,
    obj_prop_options text           not null,
    primary key (obj_prop_id),
    foreign key (obj_id) references objs (obj_id) on delete cascade
);
alter table obj_props
    add unique obj_props_name_ndx (obj_id,obj_prop_name);

/* tables added for release 2.3.0 */

/* groups
** used for editing and viewing
*/
drop table if exists groups;
create table groups (
    group_id            bigint          not null auto_increment,
    group_type          tinyint         not null,
    group_owner_id      bigint          not null,
    group_created       datetime        not null,
    group_modified      datetime        not null,
    group_name          varchar(250)    not null,
    group_body          text,
    group_props         text,
    primary key (group_id),
    foreign key (group_owner_id) references users (user_id) on delete cascade
);
alter table groups
    add unique group_name_ndx (group_name),
    add fulltext group_text_ndx (group_name,group_body,group_props);

/* group_members
** defines the users who are members of a group
*/
drop table if exists group_members;
create table group_members (
    group_id            bigint          not null,
    group_user_id       bigint          not null,
    date_added      datetime    not null,
    primary key (group_id,group_user_id),
    foreign key (group_id) references groups (group_id) on delete cascade,
    foreign key (group_user_id) references users (user_id) on delete cascade
);
alter table group_members
    add index group_id_ndx (group_id),
    add index group_user_id_ndx (group_user_id);

/* trackback
** collects information on trackback pings
*/
drop table if exists trackback;
create table trackback (
    tb_id               bigint          not null auto_increment,
    tb_doc_id           bigint          not null,
    created             datetime        not null,
    tb_url              varchar(250),
    tb_title            varchar(250),
    tb_ip               varchar(50),
    tb_site             varchar(250),
    tb_excerpt          text,
    primary key (tb_id)
);
alter table trackback
    add index tb_doc_id_ndx (tb_doc_id);

/* messages
** private messages for a specific member
*/
drop table if exists messages;
create table messages (
    msg_id              bigint          not null auto_increment,
    msg_to_user_id      bigint          not null,
    msg_from_user_id    bigint          not null,
    created             datetime        not null,
    modified            datetime        not null,
    expires             datetime        not null,
    msg_read            tinyint         not null,
    msg_title           varchar(250)    not null,
    msg_body            text            not null,
    primary key (msg_id),
    foreign key (msg_to_user_id) references users(user_id),
    foreign key (msg_from_user_id) references users(user_id)
);
alter table messages
    add index msg_to_user_ndx (msg_to_user_id),
    add index msg_from_user_ndx (msg_from_user_id),
    add index msg_read_ndx (msg_read);

/* triggers
** system events that require activity
*/
drop table if exists triggers;
create table triggers (
    tr_id               bigint          not null auto_increment,
    tr_user_id          bigint          not null,
    tr_obj_id           bigint          not null,
    tr_created          datetime        not null,
    tr_event_class      varchar(50)     not null,
    tr_event_type       varchar(50)     not null,
    primary key (tr_id),
    foreign key (tr_user_id) references users(user_id)
);

/* permissions
** maintains lists of editors for objects
** obj_type is "document", "folder", or "group"
** editor_type is "user" or "group"
*/
drop table if exists permissions;
create table permissions (
    obj_type            varchar(10)     not null,
    obj_id              bigint          not null,
    editor_type         char(1)         not null,
    editor_id           bigint          not null,
    can_edit            tinyint         not null,
    can_submit          tinyint         not null,
    primary key (obj_type,obj_id,editor_type,editor_id)
);
alter table permissions
    add index perms_obj_ndx (obj_type,obj_id),
    add index perms_editor_ndx (editor_type,editor_id);

/* themes
** tracks themes
*/
drop table if exists themes;
create table themes (
    theme_id            bigint          not null auto_increment,
    theme_created       datetime        not null,
    theme_modified      datetime        not null,
    theme_name          varchar(50)     not null,
    primary key (theme_id),
    unique index (theme_name)
);

/* templates
** stores templates for use by the system
*/
drop table if exists templates;
create table templates (
    tpl_id              bigint          not null auto_increment,
    tpl_created         datetime        not null,
    tpl_modified        datetime        not null,
    tpl_theme_id        bigint          not null,
    tpl_type_id         integer         not null,
    tpl_name            varchar(50)     not null,
    tpl_filename        varchar(250)    not null,
    tpl_body            text            not null,
    primary key (tpl_id),
    unique index (tpl_theme_id,tpl_name)
);

/* added release 3.1 */

/* subscriptions
** stores records of user subscriptions to document/folder/group
*/
drop table if exists subscriptions;
create table subscriptions (
subscr_id               bigint          not null auto_increment,
subscr_owner_id         bigint          not null,
subscr_obj_type         enum('D','F','G','U') not null,
subscr_obj_id           bigint          not null,
subscr_notify_mod       enum('N','Y')   not null,
subscr_notify_add       enum('N','Y')   not null,
subscr_notify_frequency enum('D','H','W','I') not null,
subscr_notify_type      enum('E','O')   not null,
subscr_created          datetime        not null,
subscr_modified         datetime        not null,
subscr_props            text,
primary key (subscr_id),
unique index (subscr_owner_id,subscr_obj_type,subscr_obj_id)
);

/* notifications
** when an event occurs, the notification resides here
*/
drop table if exists notifications;
create table notifications(
note_id                 bigint          not null auto_increment,
note_subscr_id          bigint          not null,
note_user_id            bigint          not null,
note_created            datetime        not null,
note_sent               datetime,
note_message            varchar(250)    not null,
note_url                varchar(250)    not null,
note_body               text,
note_props              text,
primary key (note_id),
foreign key (note_subscr_id) references subscriptions (subscr_id),
foreign key (note_user_id) references users(user_id),
index(note_subscr_id),
index(note_user_id),
index(note_sent)
);
