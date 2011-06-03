/* upgrade a 2.3 installation to 2.4 */

alter table users
add column user_nickname varchar(250)
after user_lastname;
drop index users_full_ndx on users;
create fulltext index users_full_ndx
    on users(user_firstname,user_lastname,user_nickname,user_props);

alter table docs
add column doc_version bigint not null
after doc_id;

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
create unique index group_name_ndx on groups (group_name);
create fulltext index group_text_ndx on groups (group_name,group_body,group_props);

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
create index group_id_ndx on group_members (group_id);
create index group_user_id_ndx on group_members (group_user_id);

/* permissions
** maintains lists of editors for objects
** obj_type is "document", "folder", or "group"
** editor_type is "user" or "group"
*/
drop table if exists editors; /* old name of table */
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
create index perms_obj_ndx on permissions (obj_type,obj_id);
create index perms_editor_ndx on permissions (editor_type,editor_id);