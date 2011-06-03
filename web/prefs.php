<?php
/* prefs.php
** Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. See LICENSE.txt for details.
** $Id: prefs.php,v 1.22 2006/05/24 13:48:03 glen Exp $
**
** Allows the user to set preferences
*/
include "siteframe.php";
restricted();

$PAGE->set_property(page_title,_TITLE_PREFERENCES);

// prepare list of themes
$themelist[-1] = " Use Site Default";
$r = $DB->read("SELECT theme_name FROM themes ORDER BY theme_name");
while(list($nm) = $DB->fetch_array($r))
  $themelist[$nm] = str_replace('_',' ',$nm);

// validate submitted data
if ($_POST['submitted'] && $CURUSER) {
    if ($_POST[user_THEME]==$SITE_THEME)
        $CURUSER->set_property(theme,-1);
    else if ($USER_THEME)
        $CURUSER->set_property(theme,$_POST[user_THEME]);
    $CURUSER->set_property(lines_per_page,$_POST[user_LINES_PER_PAGE]);
    $CURUSER->set_property(language,$_POST[user_LANGUAGE]);
    $CURUSER->set_property(site_date_format,$_POST[user_DATE_FORMAT]);
    $CURUSER->set_property(site_time_format,$_POST[user_TIME_FORMAT]);
    $CURUSER->set_property(default_image_size,$_POST[user_DEFAULT_IMAGE_SIZE]);
    $CURUSER->set_property(use_big_thumbnails,$_POST[user_BIGTHUMB]+0);
    $CURUSER->set_property(user_subscribe,$_POST[user_SUBSCRIBE]+0);
    $CURUSER->set_property(no_html_email,$_POST[user_NOHTML]+0);
    $CURUSER->set_property(comment_inline,$_POST[user_COMMENT_INLINE]+0);
    $CURUSER->set_property(user_notify_comments,$_POST[user_NOTIFY_COMMENTS]+0);
    /*
    $CURUSER->set_property(user_notify_document,$_POST[user_NOTIFY_DOC]+0);
    $CURUSER->set_property(user_notify_folder,$_POST[user_NOTIFY_FOLDER]+0);
    $CURUSER->set_property(user_notify_user,$_POST[user_NOTIFY_USER]+0);
    */
    $CURUSER->set_property(no_snow,$_POST[user_NO_SNOW]+0);
    if (trim($_POST['user_password'])=='') {
        $PAGE->set_property(error,_ERR_NOPASSWORD);
    }
    else {
        $CURUSER->update($_POST[user_password]);
        if ($CURUSER->errcount()) {
            $PAGE->set_property(error,$CURUSER->get_errors());
        }
        else {
            logmsg("Updated preferences for id=%d, %s",
                    $CURUSER->get_property(user_id),
                    $CURUSER->get_property(user_name));
            header("Location: $PHP_SELF");
            exit;
        }
    }
}

// only display input form if user is logged in
if ($CURUSER) {
    // define the input form structure
    $prefs = array(
                array(name => LINES_PER_PAGE,
                      prompt => _PROMPT_LINES_PER_PAGE,
                      type => "select",
                      options => array(
                                     0 => "Select Option",
                                    -1 => "Site Default",
                                    10 => "10",
                                    15 => "15",
                                    20 => "20",
                                    25 => "25",
                                    30 => "30",
                                    35 => "35",
                                    40 => "40",
                                    45 => "45",
                                    50 => "50"
                                 ),
                      value => $LINES_PER_PAGE),
                array(name => DEFAULT_IMAGE_SIZE,
                      prompt => _PROMPT_DEFAULT_IMAGE_SIZE,
                      type => "select",
                      options => array(
                                      0 => "Select Resolution",
                                     -1 => "Site Default",
                                    400 => "400",
                                    600 => "600",
                                    900 => "900",
                                    999999 => "Full-Sized"
                                    ),
                      value => $DEFAULT_IMAGE_SIZE),
                array(name => THEME,
                      prompt => _PROMPT_THEME,
                      type => "select",
                      options => $themelist,
                      disabled => $USER_THEME ? 0 : 1,
                      value => $THEME),
                array(name => LANGUAGE,
                      prompt => _PROMPT_LANGUAGE,
                      type => select,
                      options => $LANGUAGES,
                      value => $LANGUAGE),
                array(name => DATE_FORMAT,
                      prompt => "Date format",
                      type => "select",
                      options => array(
                                    'Y/m/d'     => date('Y/m/d'),
                                    'Y/M/d'     => date('Y/M/d'),
                                    'd/M/Y'     => date('d/M/Y'),
                                    'm/d/y'     => date('m/d/y'),
                                    'Y-m-d'     => date('Y-m-d'),
                                    'Y-M-d'     => date('Y-M-d'),
                                    'd-M-Y'     => date('d-M-Y'),
                                    'm-d-y'     => date('m-d-y'),
                                    'M d'       => date('M d'),
                                    'F j, Y'    => date('F j, Y'),
                                    'j F Y'     => date('j F Y'),
                                    'jS F Y'    => date('jS F Y'),
                                    'F jS, Y'   => date('F jS, Y')
                                    ),
                      value => $SITE_DATE_FORMAT),
                array(name => TIME_FORMAT,
                      prompt => "Time format",
                      type => "select",
                      options => array(
                                    'H:i'       => date('H:i'),
                                    'h:i a'     => date('h:i a'),
                                    'h:i A'     => date('h:i A'),
                                    'H:i T'     => date('H:i T'),
                                    'H:i:s T'   => date('H:i:s T'),
                                    'h:i a T'   => date('h:i a T'),
                                    'h:i:s a T' => date('h:i:s a T'),
                                    'h:i A T'   => date('h:i A T'),
                                    'h:i:s A T' => date('h:i:s A T'),
                                    'B'         => date('B')." (Swatch Internet Time)"
                                    ),
                      value => $SITE_TIME_FORMAT),
                array(name => BIGTHUMB,
                      prompt => _PROMPT_BIGTHUMB,
                      doc => _DOC_BIGTHUMB,
                      type => "checkbox",
                      rval => 1,
                      value => $CURUSER->get_property(use_big_thumbnails)),
                array(name => SUBSCRIBE,
                      prompt => _PROMPT_USER_SUBSCRIBE,
                      type => "checkbox",
                      rval => 1,
                      value => $CURUSER->get_property(user_subscribe)),
                array(name => NOHTML,
                      prompt => "No HTML email",
                      type => checkbox,
                      rval => 1,
                      value => $CURUSER->get_property('no_html_email')),
                array(name => COMMENT_INLINE,
                      prompt => "Inline comments (no popups)",
                      type => "checkbox",
                      rval => 1,
                      value => $COMMENT_INLINE),
                array(name => NOTIFY_COMMENTS,
                      prompt => "Email comments on my documents to me",
                      type => checkbox,
                      rval => 1,
                      value => $CURUSER->get_property(user_notify_comments)),
                /*
                array(name => NOTIFY_DOC,
                      prompt => "Notify on new documents",
                      type => "checkbox",
                      rval => 1,
                      value => $CURUSER->get_property(user_notify_document)),
                array(name => NOTIFY_FOLDER,
                      prompt => "Notify on new folders",
                      type => "checkbox",
                      rval => 1,
                      value => $CURUSER->get_property(user_notify_folder)),
                array(name => NOTIFY_USER,
                      prompt => "Notify on new users",
                      type => "checkbox",
                      rval => 1,
                      value => $CURUSER->get_property(user_notify_user)),
                */
                array(name => NO_SNOW,
                      prompt => "Check to stop snowfall",
                      type => checkbox,
                      rval => 1,
                      value => $CURUSER->get_property(no_snow)),
                array(name => password,
                      prompt => _PROMPT_OLDPASSWORD,
                      type => "password",
                      size => 250)
             );
    $PAGE->set_property(form_name,'preferences');
    $PAGE->set_property(form_action,$PHP_SELF);
    $PAGE->set_property(form_instructions,_PREF_INSTR);
    $PAGE->set_property(input_form_hidden,'');
    $PAGE->input_form(body,$prefs,'user_');
}
else {
    //$PAGE->set_property(error,_ERR_NOTLOGGEDIN);
    //$PAGE->set_property(body,'');
    header("Location: login.php?redirect=".htmlentities(urlencode($PHP_SELF)));
}

// display the page
$PAGE->pparse(page);

?>
