<?php
// Folder Image plugin for Siteframe
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
// $Id: folder_image.php,v 1.4 2003/06/26 12:49:00 glen Exp $
//
// this plug-in allows users to select an image to represent their folder.

$FolderImage = new Plugin('FolderImage');
if ($ENABLE_FOLDERIMAGE) {
  // this property holds the ID of the folder's image
  $FolderImage->set_input_property(
    'Folder',
    array(
      name => 'folder_image_id',
      type => 'select',
      fcn_opt => 'FolderImageList',
      prompt => "Folder Image",
      doc => "Select an image from the folder that will be used ".
             "to represent the folder instead of the standard ".
             "folder icon."
      )
  );
  // this output property holds the name of the folder image
  $FolderImage->set_output_property(
    'Folder',
    array(
      name => 'folder_image',
      callback => 'FolderImageString'
    )
  );
}
else { // if not enabled
  $FolderImage->set_output_property(
    'Folder',
    array(
      name => 'folder_image',
      type => 'hidden',
      value => $SITE_PATH . '/images/folder.gif'
    )
  );
}
$FolderImage->set_global(
  'Folder Image Plugin',
  'ENABLE_FOLDERIMAGE',
  array(
    type => 'checkbox',
    rval => 1,
    prompt => 'Enable Folder Images',
    doc => 'If checked, this plugin allows users to select an image from their '.
           'folder to be used as the thumbnail for the whole folder, rather than '.
           'the standard folder icon.'
  )
);
$FolderImage->set_global(
  'Folder Image Plugin',
  'FOLDER_IMAGE_SIZE',
  array(
    type => 'select',
    options => array(50 => '50 (pixels)', 85 => '85', 100 => '100', 150 => '150', 200 => '200'),
    prompt => 'Folder Image Size',
    doc => 'If ENABLE_FOLDERIMAGE is TRUE, then this field is used to select '.
           'the image size used as the folder image.'
  )
);
$FolderImage->set_global(
  'Folder Image Plugin',
  'FOLDER_IMAGE_DEFAULT',
  array(
    type => 'select',
    options => FolderImageListDefault(),
    prompt => 'Default Folder Image',
    doc => 'Select an image to be used for folders that do not have a '.
           'folder image otherwise defined.'
  )
);
$FolderImage->register();

// FolderImageList - builds a list of images
function FolderImageList(&$folder) {
  global $DB;
  $a[0] = 'Default icon';
  $q = sprintf('SELECT doc_id,doc_title FROM docs '.
               'WHERE doc_type=\'Image\' AND doc_folder_id=%d '.
               'ORDER BY doc_title',
               $folder->get_property('folder_id'));
  $r = $DB->read($q);
  $folder->add_error($DB->error());
  while(list($docid,$doctitle)=$DB->fetch_array($r))
    $a[$docid] = $doctitle;
  return $a;
}

// FolderImageListDefault - builds a list of default images
function FolderImageListDefault() {
  global $LOCAL_PATH;
  $a = get_sorted_dir($LOCAL_PATH.'images');
  $out[''] = 'Select a default folder image';
  foreach ($a as $file) {
    $out[$file] = sprintf('<img src="%s" alt="%s"/> %s',$file,$file,$file);
  }
  return $out;
}

// FolderImageString - returns a string formatting the folder image
function FolderImageString(&$folder) {
  global $FOLDER_IMAGE_SIZE,$FOLDER_IMAGE_DEFAULT;
  $id = $folder->get_property('folder_image_id');
  if ($id) {
    $class = doctype($id);
    if ($class == '')
      return $FOLDER_IMAGE_DEFAULT;
    $doc = new $class($id);
    $icon = $doc->get_property(sprintf('doc_file_%d',$FOLDER_IMAGE_SIZE));
    if ($icon == '')
      return $FOLDER_IMAGE_DEFAULT;
    else
      return $icon;
  }
  else {
    return $FOLDER_IMAGE_DEFAULT;
  }
}

?>