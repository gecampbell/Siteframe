/* $Id: upgrade210to220.sql,v 1.6 2003/06/27 02:26:41 glen Exp $
** Copyright (c)2002, Broadpool, LLC
** upgrades database to 2.2 format
*/

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
(9, 1, 'Bug',     'bug.php'),
(10,1, 'User',    'user.php');

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
    primary key (obj_prop_id)
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
