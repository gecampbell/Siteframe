<?php
// register.php
// $Id: register.php,v 1.6 2003/06/05 05:37:35 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// site/user registration for Siteframe
//

require "siteframe.php";

$PAGE->set_property('page_title','Siteframe Product Registration');

if ($_POST['submitted']) {

    foreach($_POST as $name => $value) {
        if ($name != 'submitted')
            $msg .= sprintf("[%s] %s\n",$name,$value);
    }
    mail('registration@siteframe.org',
        'Siteframe Product Registration',
        $msg,
        'cc: '.$_POST['user_email']);
    $PAGE->set_property('error','Thanks very much; your information has been sent.');
    $PAGE->set_property('body','<pre>'.$msg.'</pre>');
    set_global(IS_REGISTERED,1);
}
else {
    $form = array(
        array(
            name => 'user_name',
            type => 'text',
            size => 250,
            value => $CURUSER->get_property('user_name'),
            prompt => 'Your name',
            doc => 'Enter your name here, in your chosen format'
        ),
        array(
            name => 'user_email',
            type => 'text',
            size => 250,
            value => $CURUSER->get_property('user_email'),
            prompt => 'E-mail address',
            doc => 'Enter an e-mail address; this address will never be sold '.
                   'or given away, but only used to keep you informed '.
                   'of the latest product releases and bug fixes.'
        ),
        array(
            name => 'mailing_list',
            type => checkbox,
            rval => 1,
            value => 1,
            prompt => 'Keep me informed of new releases and product updates',
            doc => 'If this box is checked, you will be added to our mailing '.
                   'list for new product announcements and bug fixes.'
        ),
        array(
            name => 'visible',
            type => checkbox,
            rval => 1,
            value => 1,
            prompt => 'Show me in the directory',
            doc => 'If this box is checked, you are granting permission to '.
                   'list your site at <a href="http://siteframe.org">Siteframe</a>\'s '.
                   'directory of Siteframe websites. Your personal information '.
                   '(name, e-mail address) will <i>not</i> be made available.'
        ),
        array(
            name => 'website_name',
            type => 'text',
            size => 250,
            value => $SITE_NAME,
            prompt => 'Your website name',
            doc => 'Enter the name of your website here.'
        ),
        array (
            name => 'website_url',
            type => 'text',
            size => 250,
            value => $SITE_URL,
            prompt => 'Website URL',
            doc => 'Enter the URL of your website here. If it is not accessible '.
                   'from the Internet (for example, if it is on a corporate '.
                   'intranet, just put "Intranet" here.'
        ),
        array(
            name => 'address',
            type => 'textarea',
            prompt => 'Mailing address',
            doc => 'You can enter your mailing address in this box'
        ),
        array(
            name => 'comments',
            type => 'textarea',
            prompt => 'Comments',
            doc => 'Enter any comments about the product in this box.'
        ),
        array(
            name => 'siteframe_version',
            type => 'hidden',
            value => SITEFRAME_VERSION
        ),
        array(
            name => 'date',
            type => 'hidden',
            value => date('Y-m-d H:iT')
        )
    );
    $PAGE->set_property('form_instructions',
        'Enter the information below and press Submit. An e-mail containing '.
        'this information will be sent to registration@siteframe.org. A copy '.
        'of the e-mail will also be sent to the e-mail address you provide.');
    $PAGE->set_property('form_action',$PHP_SELF);
    $PAGE->input_form('body',$form,'','Register');
}

$PAGE->pparse('page');
?>
