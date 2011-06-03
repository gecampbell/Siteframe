/* additional properties
** this file adds a number of additional properties to objects
** $Id: object_properties.sql,v 1.1 2003/06/27 05:00:10 glen Exp $
*/

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
