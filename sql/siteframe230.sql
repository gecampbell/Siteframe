/* siteframe.sql
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
** Siteframe database schema
** $Id: siteframe230.sql,v 1.1 2003/05/09 04:20:46 glen Exp $
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
    created         datetime        not null,
    modified        datetime        not null,
    user_status     tinyint         not null,
    user_firstname  varchar(250),
    user_lastname   varchar(250)    not null,
    user_email      varchar(250)    not null,
    user_passwd     varchar(250)    not null,
    user_cookie     varchar(250)    not null,
    user_props      text,
    primary key (user_id)
);
create unique index users_email_ndx on users(user_email);
create fulltext index users_full_ndx
    on users(user_firstname,user_lastname,user_props);
create index users_name_ndx on users(user_lastname,user_firstname);
create unique index user_cookie_ndx on users(user_cookie);

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
    primary key (doc_id),
    foreign key (doc_owner_id) references users (user_id) on delete cascade
);
create unique index docs_owner_title_ndx
    on docs(doc_type,doc_owner_id,doc_title);
create fulltext index docs_full_ndx on docs(doc_title,doc_body,doc_props);
create index doc_owner_id_ndx on docs(doc_owner_id);
create index doc_folder_id_ndx on docs(doc_folder_id);
create index doc_type_ndx on docs(doc_type);
create index doc_tag_ndx on docs(doc_tag);
create index doc_hidden_ndx on docs(doc_hidden);
create index doc_title_ndx on docs(doc_title);
create index doc_created_ndx on docs(created);
create index doc_modified_ndx on docs(modified);

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
create unique index folder_name_ndx on folders(folder_name);
create fulltext index folder_ndx on folders(folder_name,folder_body,folder_props);
create index folder_owner_id_ndx on folders(folder_owner_id);
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
    primary key (doc_id,user_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade,
    foreign key (user_id) references users (user_id) on delete cascade
);
create index poll_votes_doc_id_ndx on poll_votes(doc_id);

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
    doc_id          bigint          not null,
    reply_to        bigint,
    owner_id        bigint          not null,
    created         datetime        not null,
    body            text            not null,
    comment_props   text,
    primary key (comment_id),
    foreign key (doc_id) references docs (doc_id) on delete cascade,
    foreign key (owner_id) references users (user_id) on delete cascade
);
create fulltext index comments_full_ndx on comments(body,comment_props);
create index comment_doc_id_ndx on comments(doc_id);
create index comment_owner_id_ndx on comments(owner_id);

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
create index ratings_doc_ndx on ratings (doc_id);
create index ratings_user_id_ndx on ratings (user_id);

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
create unique index category_category_ndx on categories (cat_name);
create index categories_cat_doc_type_ndx on categories (cat_doc_type);

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
create index doc_categories_doc_id_ndx on doc_categories (doc_id);
create index doc_categories_doc_cat_id_ndx on doc_categories (doc_cat_id);

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
create unique index objs_class_ndx on objs(obj_class);
insert into objs (obj_id,obj_active,obj_class,obj_class_file) values
(1, 1, 'Article', 'article.php'),
(2, 1, 'Notice',  'notice.php'),
(3, 1, 'Ad',      'ad.php'),
(4, 1, 'Event',   'event.php'),
(5, 1, 'DocFile', 'file.php'),
(6, 1, 'Image',   'image.php'),
(7, 1, 'Link',    'link.php'),
(8, 1, 'Poll',    'poll.php'),
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
create unique index obj_props_name_ndx on obj_props(obj_id,obj_prop_name);

/* Article properties */
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(1,'doc_keywords',1,'textarea','Keywords','Enter keywords for document searching');

/* Event properties */
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(4,'doc_keywords',1,'textarea','Keywords','Enter keywords for document searching');

/* DocFile properties */
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(5,'doc_keywords',1,'textarea','Keywords','Enter keywords for document searching');

/* Image properties */
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(6,'doc_keywords',1,'textarea','Keywords','Enter keywords for document searching');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(6,'copying_allowed',2,'checkbox','All users to print via Shutterfly','If checked, then members will be able to print copies of this image on the Shutterfly image service');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(6,'image_model_release',4,'checkbox','Model release','Check if you have written permission to use the image');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc)
values
(6,'image_copyright',5,'text',200,'Alternate copyright','If the image is owned by some other entity, enter their information here so that the copyright will be properly displayed.');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc)
values
(6,'image_camera',6,'text',50,'Camera','Type of camera used');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc)
values
(6,'image_lens',7,'text',50,'Lens','Type of lens used');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc)
values
(6,'image_film',8,'text',50,'Film','Type of film used');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc)
values
(6,'image_exposure',9,'text',50,'Exposure','The image exposure information (f/stop and time).');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(6,'image_flash',10,'checkbox','Flash','Check if the image was taken with the aid of an electronic flash');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc,obj_prop_options)
values
(6,'image_support',11,'select',50,'Support','Hand-held, tripod, monopod, fencepost, etc.','Hand-held;Tripod;Monopod;Kite;Other');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_size,obj_prop_prompt,obj_prop_doc)
values
(6,'image_filter',12,'text',50,'Filtration','Record any filters used when the image was made');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(6,'image_adjustment',13,'textarea','Adjustments','Describe any adjustments made to the image, either in the darkroom or via an image-editing program.');
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(6,'image_border',14,'checkbox','Border','Check to display with a border; note: this option is not supported by some sites.');

/* Link properties */
insert into obj_props
(obj_id,obj_prop_name,obj_prop_seq,obj_prop_type,obj_prop_prompt,obj_prop_doc)
values
(7,'doc_keywords',1,'textarea','Keywords','Enter keywords for document searching');

/* tables added for release 2.3.0 */

/* groups
** used for editing and viewing
*/
drop table if exists groups;
create table groups (
    group_id            bigint          not null auto_increment,
    group_name          varchar(250)    not null,
    primary key (group_id)
);
create unique index group_name_ndx on groups (group_name);

/* group_users
** defines the users who are members of a group
*/
drop table if exists group_members;
create table group_members (
    group_id            bigint          not null,
    group_user_id       bigint          not null,
    primary key (group_id,group_user_id),
    foreign key (group_id) references groups (group_id) on delete cascade,
    foreign key (group_user_id) references users (user_id) on delete cascade
);
create index group_id_ndx on group_members (group_id);
create index group_user_id_ndx on group_members (group_user_id);

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
create index tb_doc_id_ndx on trackback(tb_doc_id);

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
create index msg_to_user_ndx on messages(msg_to_user_id);
create index msg_from_user_ndx on messages(msg_from_user_id);
create index msg_read_ndx on messages(msg_read);

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

/* templates
** stores templates for use by the system
*/
drop table if exists templates;
create table templates (
    tpl_id              bigint          not null auto_increment,
    tpl_created         datetime        not null,
    tpl_modified        datetime        not null,
    tpl_theme           varchar(50)     not null,
    tpl_name            varchar(50)     not null,
    tpl_filename        varchar(250)    not null,
    tpl_body            text            not null,
    primary key (tpl_id)
);
create unique index templates_u_ndx on templates(tpl_theme,tpl_name);