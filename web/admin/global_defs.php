<?php
// global_defs.php
// $Id: global_defs.php,v 1.11 2007/09/10 19:45:30 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this file defines all the "extended" global properties.

$CPGLOBAL['0Configuration']['LOCKDOWN'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'prevent inadvertent misconfiguration',
  doc => 'When this is checked, then certain system-specific '.
  'settings are disabled. This prevents the inadvertent '.
  'modification of those settings, or the deletion of '.
  'critical system objects. If you need to modify those '.
  'settings (and you know what you\'re doing), then '.
  'uncheck the box to release the lock.'
);
$CPGLOBAL['0Configuration']['SITE_META_KEYWORDS'] = array(
  type => 'textarea',
  prompt => "Site keyword settings",
  doc => 'These keywords are automatically added to a &lt;meta/&gt; tag on each page of the site. (May not be supported by all themes.)'
);
$CPGLOBAL['0Configuration']['ALLOW_UNCONFIRMED'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Allow unconfirmed users to login",
  doc => "If unchecked, then users who have registered but not yet ".
  "responded to their confirmation e-mail will not be ".
  "allowed to login to the site. If unchecked, then users ".
  "can begin using the site immediately while waiting for ".
  "their registrations to be confirmed. This is ignored if ".
  "REGISTER_MODEL=Open."
);
$CPGLOBAL['0Configuration']['PUBLISH_MODEL'] = array(
  type => 'select',
  options => array( edited => "Edited", open => "Open" ),
  prompt => "Publication Model",
  doc => 'The publication model determines how new content is handled. If <b>Open</b>, then new content is managed by site members, and the member controls when the content is visible. If <b>Edited</b>, then new content is marked "hidden" by default, an email message is sent to the <b>editor_email</b>, below, and the editor is the only one who can make content visible.'
);
$CPGLOBAL['0Configuration']['NOTIFY_EMAIL'] = array(
  type => 'text',
  size => 250,
  prompt => "Editor Notification Email",
  doc => 'Not used if the publication model is <b>open</b>. Otherwise, the email address to which notifications of new documents are sent.'
);
$CPGLOBAL['0Configuration']['REGISTER_CAPTCHA'] = array(
  'type' => 'text',
  'size' => 250,
  'prompt' => 'Registration CAPTCHA',
  'doc' => 'If defined, then the user is required to enter this word before registering',
);
$CPGLOBAL['Documents']['NOTICES_ADMIN_ONLY'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Only allow administrator(s) to create Notices",
  doc => 'If you check this box, then only site administratos can create Notices (recommended).'
);
$CPGLOBAL['0Configuration']['FILEPATH'] = array(
  type => 'text',
  size => 250,
  disabled => $LOCKDOWN,
  prompt => "Path to store files",
  doc => 'The path (relative to the website root) where uploaded files will reside. You should ensure that the directory is writable by your web server. If your web server does not have permission to creat this directory or write to it, then there will be much weeping and gnashing of teeth.'
);
$CPGLOBAL['Images']['DEFAULT_IMAGE_SIZE'] = array(
  type => 'select',
  options => array(
              "400" => "400",
              "600" => "600",
              "900" => "900",
              "-1" => "Full-sized" ),
  prompt => "Default image size (in pixels)",
  doc => 'Select the default size for images; users can override this in their preferences.'
);
$CPGLOBAL['Images']['IMAGE_PROOF'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Add "PROOF" to images larger than 150 pixels',
  doc => 'If selected, then thumbnails larger than 150 pixels will have the word "PROOF" interpolated across the image to discourage copying.'
);
$CPGLOBAL['Images']['MAX_IMAGE_SIZE'] = array(
  type => 'number',
  size => 5,
  prompt => "Maximum size (in pixels) of images",
  doc => "Leave as 0 to allow any size"
);
$CPGLOBAL['Images']['SELFPORTRAIT_SIZE'] = array(
  type => 'select',
  options => array(
    "150" => "150",
    "200" => "200",
    "400" => "400" ),
  prompt => "Self-portrait size",
  doc => 'Select the default size for user self-portraits.'
);
$CPGLOBAL['Images']['IMAGE_QUALITY'] = array(
  type => 'select',
  options => array(
              100 => "100%%",
              90 =>  "90%%",
              80 =>  "80%%",
              70 =>  "70%%",
              60 =>  "60%%",
              50 =>  "50%%",
              40 =>  "40%%" ),
  prompt => "Image quality for resized images",
  doc => 'Default JPEG compression for downsized images; a higher number means better quality but larger file sizes; a lower number means smaller files but poorer quality. It is usually difficult to see differences between 80% and 90%, so 80% is the recommended setting.'
);
$CPGLOBAL['Documents']['MULTI_FILE_UPLOAD'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Allow multiple file uploads",
  doc => "If checked, this will allow users to upload multiple ".
  "files at one time."
);
$CPGLOBAL['Documents']['MULTI_FILE_UPLOAD_MAX'] = array(
  type => 'number',
  size => 2,
  prompt => "Multi-file upload number",
  doc => "Enter the maximum number of files allowed to be uploaded at one time."
);
$CPGLOBAL['Documents']['MAX_FILE_SIZE'] = array(
  type => 'number',
  size => 10,
  prompt => 'Maximum uploaded file size (in kilobytes)',
  doc => 'The maximum size of uploaded files (in kilobytes). For example, a value of "256" will not allow files larger than 256K to be uploaded. A value of 0 means that there is no site limit (there may, however, be limits set by PHP or the web server software).'
);
$CPGLOBAL['Miscellaneous']['ALLOW_EMAIL_SUBMISSIONS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Allow comments and articles via email',
  doc => 'Check this box to allow submission of content via email (experimental feature).'
);
$CPGLOBAL['Comments']['ALLOW_COMMENTS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Allow comments',
  doc => 'Check this box to allow members to comment on the document (may be overridden by folder settings).'
);
$CPGLOBAL['Comments']['COMMENT_INLINE'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Inline comments (no popups)',
  doc => 'Check this box to use inline comments instead of popup pages.'
);
$CPGLOBAL['Comments']['ANONYMOUS_COMMENTS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Allow anonymous comments",
  doc => 'Check this box to allow website visitors to post comments without registering.'
);
$CPGLOBAL['Comments']['COMMENT_SUBJECTS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Comments have subject lines",
  doc => 'Check this box to require subject lines for comments.'
);
$CPGLOBAL['Comments']['COMMENT_EMAIL'] = array(
  type => 'text',
  size => 250,
  prompt => "Comment email address",
  doc => 'If a value is entered here, then all comments will be sent to this email address. Useful if you have a mailing list for monitoring site discussions.'
);
$CPGLOBAL['Ratings']['ALLOW_RATINGS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Use document ratings",
  doc => 'Check this box to allow members to rate documents. This is a default value for new documents, and can be overridden by the folder settings.'
);
$CPGLOBAL['Ratings']['RATE_COMMENT_LIMIT'] = array(
  type => 'number',
  size => 2,
  prompt => "Maximum rating requires comment",
  doc => 'If a user rates a document this value or lower, then a comment will be required to support the rating.'
);
$CPGLOBAL['Ratings']['MIN_RATING'] = array(
  type => 'number',
  size => 5,
  prompt => "Minimum rating for retention",
  doc => 'Documents with ratings less than this value may be automatically deleted from the site after 30 days. If 0, then no documents will ever be deleted.'
);
$CPGLOBAL['Ratings']['SELF_RATING_ALLOWED'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Self-rating allowed',
  doc => 'If this box is checked, them members are allowed to rate their own documents. If not, then membes are not permitted self-rating.'
);
$CPGLOBAL['Templates']['HOME_PAGE'] = array(
  type => 'select',
  options => templatelist(1),
  prompt => "Home Page Template",
  doc => 'Select the template to be used as your home page. You can create or modify this template by selecting Content Templates under the Control Panel menu.'
);
$CPGLOBAL['Templates']['NAVIGATION'] = array(
  type => 'select',
  options => templatelist(2),
  prompt => "Navigation Template",
  doc => 'Select the template to be used as your navigation menu. You can create or modify this template by selecting Content Templates under the Control Panel menu. This value may not be supported by all themes.',
);
$CPGLOBAL['Templates']['FOOTER_TEXT'] = array(
  type => 'select',
  options => templatelist(3),
  prompt => "Footer Template",
  doc => 'Select the template to be used as your page footer. You can create or modify this template by selecting Content Templates under the Control Panel menu.'
);
$CPGLOBAL['Templates']['MACRO_AUTOLOAD'] = array(
  type => 'select',
  options => templatelist(4),
  prompt => 'Autoload macro definitions',
  doc => 'Select a template that contains macro definitions. If this is defined, then these macro definitions will be automatically loaded and will be available for use on your website.'
);
$CPGLOBAL['Templates']['LOGO'] = array(
  type => file,
  prompt => "Upload a logo file",
  doc => "Current logo file: $LOGO<br/>Use this to upload a new ".
  "file for use as the website logo. Use &#123;site_path}/&#123;logo} ".
  "to refer to this file for accuracy."
);
$CPGLOBAL['Templates']['LOGO_DELETE'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Check to remove existing logo",
  doc => "If checked, the existing logo file will be deleted and ".
  "the LOGO property will be reset."
);
$CPGLOBAL['Templates']['CHARSET'] = array(
  type => 'text',
  size => 50,
  doc => "Use this to modify the default character set supported by ".
  "the HTML files generated by this site. This can be used, ".
  "for example, to specify an alternate (non-European) character ".
  "set. Be careful! Incorrect use could break things.",
  prompt => "Character set used for HTML files"
);
$CPGLOBAL['User']['LINES_PER_PAGE'] = array(
  type => 'select',
  options => array( 10=>'10', 15=>'15', 20=>'20', 25=>'25', 30=>'30'),
  prompt => "Default items per page",
  doc => 'The default number of items per page; this is used for lists and directories, and can be overridden by user preferences.'
);
$CPGLOBAL['User']['USER_THEME'] = array(
  type => 'checkbox',
  rval => 1,
  doc => 'Check this if users are allowed to change their THEME setting',
  prompt => "Are users allowed to change their theme setting?"
);
$CPGLOBAL['Templates']['SEP'] = array(
  type => 'text',
  size => 20,
  prompt => "default link separator",
  doc => "This value is optionally displayed as a separator between multiple links on the same line of text"
);
$CPGLOBAL['User']['MAX_DOC_PER_DAY'] = array(
  type => 'number',
  size => 4,
  prompt => "Maximum number of documents allowed per day",
  doc => "The maximum number of documents a user is allowed to create in any 24-hour period."
);
$CPGLOBAL['Templates']['OKTAGS'] = array(
  type => 'textarea',
  prompt => "Allowable HTML tags",
  doc => 'This defines the HTML tags that are allowed in user-supplied content. It is <b>highly recommended</b> that you do not allow &lt;script/&gt; tags or any other tag that can produce insecure behavior.'
);
$CPGLOBAL['Documents']['DOC_HIDDEN_PREFIX'] = array(
  type => 'text',
  size => 250,
  prompt => "Prefix for names of hidden documents",
  doc => 'A string added to the front of hidden documents. Not well supported at the moment.'
);
$CPGLOBAL['Documents']['DOC_HIDDEN_SUFFIX'] = array(
  type => 'text',
  size => 250,
  prompt => "Suffix for names of hidden documents",
  doc => 'A string added to the end of hidden documents. Not well supported at the moment.'
);
$CPGLOBAL['Documents']['MAX_DOC_CATEGORIES'] = array(
  type => 'text',
  size => 4,
  prompt => "Maximum number of categories per document",
  doc => 'The maximum number of categories (up to 10) that can be attached to documents. Set to 0 if you do not wish to use categories.'
);
$CPGLOBAL['Documents']['DOC_REQUIRE_FOLDER'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Documents must be in a folder',
  doc => 'If checked, all documents must be assigned to a folder; otherwise, documents can be "orphaned."'
);
$CPGLOBAL['Documents']['DOC_REQUIRE_CATEGORY'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Documents must have a category',
  doc => 'If checked, alll documents must have at least one category assigned'
);
$CPGLOBAL['Report']['SITE_NEWSLETTER_EMAIL'] = array(
  type => 'text',
  size => 250,
  prompt => "Newsletter email address",
  doc => 'Instead of sending the daily report to individual users, use this address to send to a mailing list.'
);
$CPGLOBAL['Report']['REPORT_DAYS'] = array(
  type => 'number',
  size => 3,
  prompt => "Number of days for report",
  doc => 'The site newsletter will contain this many days\' worth of recent entries. For example, if you want your site newsletter to be produced weekly, then this value should be 7.'
);
$CPGLOBAL['Report']['DEFAULT_USER_SUBSCRIBE'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Default for user_subscribe",
  doc => "If checked, then new site members will be automatically subscribed to the newsletter (they can unsubscribe by changing their Preferences). If unchecked, then users must manually change their Preferences to subscribe."
);
$CPGLOBAL['Miscellaneous']['PING_WEBLOGS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Notify weblogs.com when site is changed",
  doc => 'If checked, then the software will automatically notify <a href="http://www.weblogs.com">http://www.weblogs.com</a> via XML-RPC whenever a new document is added to the site.'
);
$CPGLOBAL['RSS']['GEN_RSS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Generate RSS for syndication",
  doc => 'If checked, then the software will automatically generate an RSS file for content syndication.'
);
$CPGLOBAL['RSS']['NUM_RSS'] = array(
  type => 'number',
  size => 3,
  prompt => "Number of RSS items to syndicate",
  doc => 'If "Generate RSS for syndication" is checked, then this defines the number of "recent items" to include in the rss.xml file.'
);
$CPGLOBAL['Templates']['TRUNCATE_SIZE'] = array(
  type=> 'number',
  size => 3,
  prompt => "Characters used for :truncate function",
  doc => 'Used by the &#123;<i>variable</i>:truncate} function; this determines how many characters appear in the truncated version of the page variable.'
);
$CPGLOBAL['Folders']['DEFAULT_FOLDER_TYPE'] = array(
  type => 'select',
  options => $FOLDERS,
  doc => 'What should the default type of folder be?',
  prompt => "Default folder type"
);
$CPGLOBAL['Folders']['DEFAULT_FOLDER_PARENT'] = array(
  type => 'select',
  options => $topfolders,
  doc => 'What should the default parent of new folders be?',
  prompt => "Default folder parent"
);
$CPGLOBAL['Folders']['FOLDER_PATH_SEP'] = array(
  type => 'text',
  size => 100,
  prompt => "Folder path separator",
  doc => 'When constructing a folder path (breadcrumb trail), the separator is used between each level of the folder hierarchy.'
);
$CPGLOBAL['Folders']['FOLDER_PATH_PREFIX'] = array(
  type => 'text',
  size => 100,
  prompt => "Folder path prefix",
  doc => 'When constructing a folder path (breadcrumb trail), this string is prefixed before the start of the folder hierarchy.'
);
$CPGLOBAL['Folders']['FOLDER_PATH_SUFFIX'] = array(
  type => 'text',
  size => 100,
  prompt => "Folder path suffix",
  doc => 'When constructing a folder path (breadcrumb trail), this string is added at the end of the folder hierarchy.'
);
$CPGLOBAL['Folders']['PUBLIC_FOLDER_PREFIX'] = array(
  type => 'text',
  size => 250,
  prompt => "Prefix for names of public folders",
  doc => 'A string added before the name of a public folder. For example, if you want public folders to appear in <i>italics</i>, the prefix string would be &lt;i>, and the suffix would be &lt;/i>'
);
$CPGLOBAL['Folders']['PUBLIC_FOLDER_SUFFIX'] = array(
  type => 'text',
  size => 250,
  prompt => "Suffix for names of public folders",
  doc => 'A string added after the name of a public folder.'
);
$CPGLOBAL['Folders']['TOP_FOLDERS_ADMIN_ONLY'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Restrict top-level folders to administrators",
  doc => 'If checked, then only site administrators can create top-level folders; in this case, all member-created folders must be a subfolder of some other folder. If unchecked, then top-level folders can be created by any site member.'
);
$CPGLOBAL['Folders']['SFOLDER_AUTO_REMOVE'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Automatically removed expired documents from scheduled folders",
  doc => 'If checked, then documents are automatically removed from scheduled folders once their time of reckoning is up. Otherwise, documents remain in the folder but are held as inactive.'
);
$CPGLOBAL['Folders']['DEFAULT_CFOLDER_HIDDEN'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Should documents in competition folders be hidden by default?',
  doc => 'If checked, then all documents in competition folders are hidden by default; otherwise, they are visible.'
);
$CPGLOBAL['Miscellaneous']['TREE_TITLE'] = array(
  type => 'text',
  size => 250,
  prompt => "Title used by tree.php",
  doc => 'This string is used for the title of the folder hierarchy page produced by tree.php.'
);
$CPGLOBAL['Advertisements']['MAX_AD_DAYS'] = array(
  type => 'number',
  size => 3,
  prompt => "Max number of days for Ads to run",
  doc => 'The number of days an advertisement runs',
);
$CPGLOBAL['Advertisements']['MAX_AD_SIZE'] = array(
  type => 'number',
  size => 5,
  prompt => "Max size of Ad",
  doc => 'The maximum size (in characters) for an advertisement document.',
);
$CPGLOBAL['Advertisements']['AD_FOLDER_REQUIRED'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Advertisements must be in a folder",
  doc => 'If checked, then advertisements must reside in a folder.'
);
$CPGLOBAL['Logging']['TRACK_SESSIONS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => "Track user sessions",
  doc => 'If checked, then user visits are tracked by the website software. This can have significant overhead and is not recommended for very large sites.'
);
$CPGLOBAL['Logging']['LOG_DAYS'] = array(
  type => 'number',
  size => 3,
  prompt => 'Days of data to log',
  doc => 'Determines how much session-tracking and activity log data is retained on the site. Very large values can degrade site performance.'
);
$CPGLOBAL['Performance']['CACHED_VALUE_UPDATE_TIME'] = array(
  type => 'number',
  size => 4,
  prompt => "Cached value update frequency (in minutes)",
  doc => 'This value (in minutes) determines how frequently some compute-intensive values are recalculated. For example, the calculation of the "top-rated document" is very database intensive; if you set this value to "30," then that value is automatically calculated only if the cached value is more than 30 minutes old.'
);
$CPGLOBAL['Miscellaneous']['COOKIE_DAYS'] = array(
  type => 'number',
  size => 4,
  prompt => "Duration of \"Remember me\" cookies (in days)",
  doc => 'When a user clicks "Remember me" on login, this value determines the age of the cookie placed on his/her computer. A value of 90, for example, means that users will need to re-login every 90 days. A value of 1 would mean that users need to login every single day. Adjust according to your security preferences.'
);
$CPGLOBAL['User']['USER_STATISTICS'] = array(
  type => 'checkbox',
  rval => 1,
  prompt => 'Extended user statistics',
  doc => 'If checked, then the daily batch job will calculate special user properties such as user_rating_count, user_comment_count, user_rating_count, user_document_count, and user_folder_count. It also calculates user_activity_count, which is the total number of comments and ratings the member has placed on the site (an indicator of overall site activity). Finally, the top 5% and 10% of users are identified with user_top5 or user_top10 (meaning percentile).'
);
$CPGLOBAL['Maintenance']['MAINT_MODE_MSG'] = array(
  type => 'textarea',
  prompt => 'Maintenance Mode Message',
  doc => 'Enter a message to be displayed when the site is in maintenance mode. If this is blank, then a default message is displayed.'
);
?>
