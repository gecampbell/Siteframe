/* upgrade 3.0 beta 1 to 3.0 beta 2
** $Id: upgrade300b1to300b2.sql,v 1.1 2003/06/04 17:25:50 glen Exp $
*/
alter table templates add
    tpl_type_id          integer         not null
after tpl_theme_id;
