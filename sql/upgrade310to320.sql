/* siteframe.sql
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
** $Id: upgrade310to320.sql,v 1.2 2005/06/05 23:42:39 glen Exp $
**
** This script upgrades an existing database from 3.1.0 to 3.2.0
*/
alter table docs
    change created doc_created datetime,
    change modified doc_modified datetime;

alter table users
    change created user_created datetime,
    change modified user_modified datetime;

alter table comments
    change created comment_created datetime not null,
    change reply_to comment_reply_to integer,
    change owner_id comment_owner_id integer not null,
    change body comment_body text,
    change doc_id comment_doc_id integer not null;
