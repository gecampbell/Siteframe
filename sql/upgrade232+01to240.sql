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
