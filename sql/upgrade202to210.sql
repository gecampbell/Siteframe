/* $Id: upgrade202to210.sql,v 1.3 2003/05/06 22:16:49 glen Exp $
** Copyright (c)2002, Broadpool, LLC. All Rights Reserved.
** This file upgrades a 2.0.2 database to 2.1.0 standard
*/

/* rss - holds RSS feeds
*/
drop table if exists rss;
create table rss (
    rss_url         varchar(250)    not null,
    rss_loaded      datetime        not null,
    rss_text        text            not null,
    primary key (rss_url)
);
