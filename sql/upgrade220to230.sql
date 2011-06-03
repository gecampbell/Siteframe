/* $Id: upgrade220to230.sql,v 1.7 2003/05/06 22:16:49 glen Exp $
** Copyright (c)2002, Broadpool, LLC.
** Upgrades a 2.2.0 installation to 2.3.0
*/

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
    primary key (group_id,group_user_id)
);
create index group_id_ndx on group_members (group_id);
create index group_user_id_ndx on group_members (group_user_id);

/* sessions */
alter table sessions
add remote_ip varchar(50);
alter table sessions
add referer varchar(250);
alter table sessions
add agent   varchar(250);
alter table sessions
add authuser varchar(250);

/* users */
alter table users
add user_cookie varchar(250) not null
after user_passwd;
create unique index users_user_cookie_ndx on users(user_cookie);
update users
set user_cookie=sha1(created),
    user_passwd=sha1(user_passwd);

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
create index trackback_doc_id_ndx on trackback(tb_doc_id);

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
    foreign key (msg_to_user_id) references users (user_id)
);

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