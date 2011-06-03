/* $Id: upgrade200to202.sql,v 1.6 2003/05/06 22:16:49 glen Exp $
** Copyright (c)2002, Broadpool, LLC. All Rights Reserved.
** Upgrades a 2.0.0 database to 2.0.2
*/

alter table properties
change value value text;


/* note that, for an upgrade, we can drop these tables and
** rebuild them, since they have never been used before even
** though their definitions have been in the SQL file.
*/


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

/* doc_categories - categories per document
*/
drop table if exists doc_categories;
create table doc_categories (
    doc_id          bigint          not null,
    doc_cat_id      bigint          not null,
    primary key (doc_id,doc_cat_id)
);