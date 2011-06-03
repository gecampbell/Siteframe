create index users_nickname_ndx on users(user_nickname);

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
