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
