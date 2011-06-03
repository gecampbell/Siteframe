<?php
// multiload.php
// $Id: multiload.php,v 1.7 2003/06/22 00:15:57 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details
//
// allows multiple-image upload capability

require "siteframe.php";

$PAGE->set_property('page_title','Multiple File Upload');

$instructions = <<<ENDINSTRUCTIONS
<p>Use this page to upload multiple files or images to the same folder.
Select the target folder, choose the files, and optionally enter
their titles (if you do not provide a title, then the original
file name is used). When complete, press <b>Submit</b>.
Because of the time involved in processing uploaded files, this page
may take some time to load after you press <b>Submit</b>.</p>
<p><em>Note: this site may have restrictions on the size of images or
the number of documents you can create per day.</em></p>
ENDINSTRUCTIONS;

if (!$CURUSER) {
    $PAGE->set_property('error','You must be logged in to use this page');
}
else if (!$MULTI_FILE_UPLOAD) {
    $PAGE->set_property('error','This site does not currently permit multiple file uploads');
}
else if ($_POST['submitted']) {

    // because uploading and processing multiple files can take some time,
    // we'll increase the default execution timeout period
    set_time_limit(180);    // 3 minutes

    if ($_POST['folder_id']) {
        $PAGE->set_property('folder_id',$_POST['folder_id']);
        $fclass = foldertype($_POST['folder_id']);
        if ($fclass == '') {
            $PAGE->set_property('error','Invalid folder ID');
            $PAGE->pparse('page');
            exit;
        }
        $f = new $fclass($_POST['folder_id']);
        switch($f->get_property('folder_limit_type')) {
        case 'Image':
            $class = 'Image';
            break;
        case 'DocFile':
        case '':
            $class = 'DocFile';
            break;
        default:
            $PAGE->set_property('error',
                sprintf('Unsupported folder limit type [%s]',
                    $f->get_property('folder_limit_type')));
            $PAGE->pparse('page');
            exit;
        }
        $folderpath = sprintf('%s/folder.php?id=%d',
            $SITE_PATH,
            $f->get_property('folder_id'));
        $PAGE->set_property('body',
            sprintf('<p>Return to <a href="%s">%s</a></p>',
                $folderpath,
                $f->get_property('folder_name')));
    }

    // process each uploaded file
    foreach($_POST['title'] as $index => $value) {
        if ($_FILES['filename']['error'][$index] != 4) {
            $obj = new $class();
            $obj->set_property('doc_type',$class);
            if ($value == '')
                $title = $_FILES['filename']['name'][$index];
            else
                $title = $value;
            $obj->set_property('doc_folder_id',$_POST['folder_id']);
            $obj->set_property('doc_owner_id',$CURUSER->get_property('user_id'));
            $obj->set_property('doc_title',$title);
            $obj->set_property('doc_hidden',$_POST['hidden']);
            $obj->save_file(
                'doc_file',
                $_FILES['filename']['tmp_name'][$index],
                $_FILES['filename']['name'][$index],
                $_FILES['filename']['size'][$index],
                $_FILES['filename']['type'][$index]);
            $obj->add();
            if ($obj->errcount()) {
                $PAGE->set_property('error',$obj->get_errors(),TRUE);
                $num_errors++;
            }
            else
                $PAGE->set_property('error',
                    sprintf('Loaded file %s<br/>',$title),TRUE);
        }
    }

    // if there were no errors, go ahead and return to the folder page
    if (!$num_errors) {
        header('Location: '.$folderpath);
        exit;
    }

}
else if (!$_GET['folder']) {
    $PAGE->set_property('error',
        'You must specify a folder to receive the file upload<br/>');
}
else {

    // validate the folder
    $fclass = foldertype($_GET['folder']);
    $PAGE->set_property('folder_id',$_GET['folder']);
    if ($fclass == '') {
        $PAGE->set_property('error',
            'The requested folder does not seem to exist');
        $PAGE->pparse('page');
        exit;
    }
    $f = new $fclass($_GET['folder']);

    // check the limit type
    switch($f->get_property('folder_limit_type')) {
    case 'Image':
        $class = 'Image';
        break;
    case 'DocFile':
    case '':
        $class = 'DocFile';
        break;
    default:
        $PAGE->set_property('error',
            sprintf('Unsupported folder limit type [%s]',$class));
        $PAGE->pparse('page');
        exit;
    }

    // construct the input form
    $form[] = array(
        name => 'folder_id',
        type => 'select',
        options => folderlist($CURUSER->get_property('user_id'),$class),
        value => $_GET['folder'],
        prompt => 'Folder for '.$CLASSES[$class],
        doc => 'Select the target folder for these '.$CLASSES[$class]
    );
    $form[] = array(
        name => 'hidden',
        type => checkbox,
        rval => 1,
        prompt => 'Hidden',
        doc => 'If checked, these files will not be visible in the folder '.
               'or in other lists until you edit it and mark it '.
               'as un-hidden. This allows you the time to edit the document '.
               'information if necessary before making it publicly visible. '.
               'You can still see the documents listed under "My Page".'
    );
    if ($MAX_DOC_PER_DAY && $MULTI_FILE_UPLOAD_MAX > $MAX_DOC_PER_DAY)
      $MULTI_FILE_UPLOAD_MAX = $MAX_DOC_PER_DAY;
    $form[] = array(
        name => 'files',
        type => 'sidebyside',
        count => $MULTI_FILE_UPLOAD_MAX ?
                 $MULTI_FILE_UPLOAD_MAX : 10,
        fields => array(
            array(
                name => 'filename[]',
                type => 'file',
                prompt => 'File'
            ),
            array(
                name => 'title[]',
                type => 'text',
                size => 250,
                prompt => 'Title'
            ),
        ),
        prompt => $CLASSES[$class].' to upload',
        doc => 'Select your files using the <b>Browse...</b> button, and give '.
               'each file a title (if you don\'t provide a title, then '.
               'the file name is used). To add additional '.
               'information, edit the document once the files have '.
               'been uploaded.'
    );
    $PAGE->set_property('form_instructions',$instructions);
    $PAGE->set_property('form_action',$PHP_SELF);
    $PAGE->input_form('body',$form);
}
$PAGE->pparse('page');
?>
