<?php
// image.php
// $Id: image.php,v 1.31 2005/08/16 14:20:58 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// defines the image class


$SUPPORTED_RESOLUTIONS =
    array(50,85,100,150,200,400,600,900);

class Image extends DocFile {

    // Image() - constructor
    function Image($id=0, $dbrow=0) {
        parent::DocFile($id, $dbrow);
        $this->set_property(doc_type,'Image');
    }

    // delete - delete all thumbnails
    function delete() {
        $props = $this->get_properties();
        foreach($props as $name => $value) {
            if (substr($name,0,8) == 'doc_file')
                @unlink($value);
        }
        parent::delete();
    }

    // get_properties - override to add add'l properties
    function get_properties() {
        global $DEFAULT_IMAGE_SIZE;
        $a = parent::get_properties();
        if ($DEFAULT_IMAGE_SIZE < 0 || $DEFAULT_IMAGE_SIZE > 2000)
            $a[doc_image_default] = sprintf('src="%s"',$this->get_property('doc_file'));
        else
            $a[doc_image_default] = sprintf('src="%s"',
                $this->get_property("doc_file_$DEFAULT_IMAGE_SIZE"));
        return $a;
    }

    // set_property - create thumbnails, check mime-type
    function set_property($name,$value) {
        global $MAX_IMAGE_SIZE,$_FILES,$SUPPORTED_RESOLUTIONS,$DB;
        switch($name) {
        case 'allow_ratings':
            if ($this->get_property('doc_id') &&
                ($this->get_property('allow_ratings') == 1) &&
                ($value == 0)) {
                $DB->write(
                  sprintf('DELETE FROM ratings WHERE doc_id=%d',
                    $this->get_property('doc_id'))
                );
            }
            parent::set_property($name,$value);
            break;
        case 'doc_file':
            parent::set_property($name,$value);
            // set size properties
            $size = GetImageSize($value);
            parent::set_property(image_width,$size[0]);
            parent::set_property(image_height,$size[1]);
            if ($MAX_IMAGE_SIZE) {
                if ($size[0] > $MAX_IMAGE_SIZE)
                    $this->add_error("Image width [%d] is larger than allowable %d pixels; make your image smaller and try again",$size[0],$MAX_IMAGE_SIZE);
                if ($size[1] > $MAX_IMAGE_SIZE)
                    $this->add_error("Image height [%d] is larger than allowable %d pixels; make your image smaller and try again",$size[1],$MAX_IMAGE_SIZE);
            }
            switch ($this->get_property('doc_file_mime_type')) {
            case 'image/gif':
            case 'image/png':
            case 'image/x-png':
            case 'image/pjpeg':
            case 'image/jpeg':
            case 'image/jpg':
                foreach($SUPPORTED_RESOLUTIONS as $rez) {
                    $newprop = sprintf('doc_file_%d',$rez);
                    @unlink($this->get_property($newprop));
                    parent::set_property(
                        $newprop,
                        $this->resize_image($value,
                            $this->get_property('doc_file_mime_type'),
                            $rez));
                }
                $newprop = sprintf('doc_file_center_%d',$rez);
                @unlink($this->get_property($newprop));
                parent::set_property(
                    $newprop,
                    $this->resize_image($value,
                        $this->get_property('doc_file_mime_type'),
                        $rez,
                        true));
                break;
            default:
                $this->add_error(_ERR_BADMIME,$this->get_property(doc_file_mime_type));
                logmsg("Bad MIME type [%s]",$this->get_property(doc_file_mime_type));
                logmsg("Errors: %s",$this->get_errors());
            }
            if ($this->errcount())
                @unlink($this->get_property('doc_file'));
            break;
        default:
            parent::set_property($name,$value);
        }
    }

    // input_form_values() - return input form array
    function input_form_values() {
        global $PUBLISH_MODEL,$CURUSER,$MAX_DOC_CATEGORIES;
        if ($PUBLISH_MODEL!='open') {
            if (!$CURUSER)
                $hidden_disabled = 1;
            else if (!isadmin())
                $hidden_disabled = 1;
            else
                $hidden_disabled = 0;
        }
        $a = array(
                array(name => doc_id,
                      type => hidden,
                      value => $this->get_property(doc_id)),
                array(name => doc_type,
                      type => hidden,
                      value => 'Image'),
                array(name => doc_title,
                      type => text,
                      size => 250,
                      value => $this->get_property(doc_title),
                      prompt => _PROMPT_DOC_TITLE),
                array(name => doc_folder_id,
                      type => select,
                      options => folderlist($CURUSER->get_property(user_id),'Image'),
                      value => $this->get_property(doc_folder_id),
                      prompt => _PROMPT_DOC_FOLDER),
                array(name => doc_file,
                      type => file,
                      optional => $this->get_property(doc_id),
                      value => $this->get_property(doc_file),
                      prompt => _PROMPT_DOC_FILE),
                array(name => doc_body,
                      type => textarea,
                      value => $this->get_property(doc_body),
                      rows => 15,
                      doc => _DOC_DOC_BODY,
                      prompt => _PROMPT_DOC_FILE_BODY),
                array(name => doc_hidden,
                      type => checkbox,
                      rval => 1,
                      disabled => $hidden_disabled,
                      value => $this->get_property(doc_hidden),
                      prompt => _PROMPT_DOC_HIDDEN),
                array(name => allow_ratings,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_ratings),
                      prompt => _PROMPT_DOC_RATING),
                array(name => allow_comments,
                      type => checkbox,
                      rval => 1,
                      value => $this->get_property(allow_comments),
                      prompt => _PROMPT_DOC_COMMENTS)
             );
        $a =  array_merge($a,$this->custom_properties());
        $cats = doc_categories($this->get_property('doc_type'));
        for($i=1; $i<=$MAX_DOC_CATEGORIES; $i++) {
          $a[] = array(name => "doc_category_$i",
                       type => select,
                       options => $cats,
                       value => $this->get_property("doc_category_$i"),
                       prompt => sprintf(_PROMPT_DOC_CATEGORY,$i)
                      );
        }
        if (isadmin())
            $a[] = array(name => doc_tag,
                         type => text,
                         size => 32,
                         value => $this->get_property(doc_tag),
                         prompt => _PROMPT_DOC_TAG);
        else
            $a[] = array(name => doc_tag,
                         type => hidden,
                         value => $this->get_property(doc_tag));
        return $a;
    }

}

?>
