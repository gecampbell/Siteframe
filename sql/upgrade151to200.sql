/* upgrades siteframe 1.x to 2.0
** $Id: upgrade151to200.sql,v 1.6 2002/09/26 05:10:05 glen Exp $
*/
alter table folders
add column folder_parent_id bigint not null
after folder_owner_id;

alter table folders
add column folder_children tinyint not null
after folder_public;

/* now, get rid of all "PFolders" and replace them with public=1 */
update folders
    set folder_public=1,
        folder_type='Folder'
where folder_type='PFolder';

/* create some indexes */
create index doc_folder_id_ndx on docs(doc_folder_id);
create index folder_parent_id_ndx on folders(folder_parent_id);
create index doc_type_ndx on docs(doc_type);
