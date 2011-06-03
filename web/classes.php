<?php
// classes.php
// $Id: classes.php,v 1.38 2005/10/31 06:22:09 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// classes include file

// NOTE: the Bug class has been moved to the ./plugins directory

$PAGE_START = time(); // used for timing

// security fix
if ($LOCAL_PATH != "../")
    $LOCAL_PATH = "./";

// to add a new class to the system, add it to the templates list and
// add the include file below.
// if you don't want to use  a particular class, then comment out the
// corresponding lines below

// $TEMPLATES[Document] = 'document';
$TEMPLATES[Article] = 'article';
$TEMPLATES[Notice] = 'notice';
$TEMPLATES[NoticeList] = 'notices';
$TEMPLATES[Ad] = 'ad';
$TEMPLATES[Category][0] = 'category';
$TEMPLATES[Category][''] = 'category';
$TEMPLATES[Category][Article] = 'category';
$TEMPLATES[Category][DocFile] = 'category';
$TEMPLATES[Category][Event] = 'category';
$TEMPLATES[Category][Image] = 'category_image';
$TEMPLATES[Category][Link] = 'category';
$TEMPLATES[Category][Poll] = 'category';
$TEMPLATES[DocFile] = 'file';
$TEMPLATES[Event] = 'event';
$TEMPLATES[FolderList] = 'folders';
$TEMPLATES[VFolder][0]          = $TEMPLATES[Folder][0] = 'folder';
$TEMPLATES[VFolder][Article]    = $TEMPLATES[Folder][Article] = 'folder';
$TEMPLATES[VFolder][Ad]         = $TEMPLATES[Folder][Ad] = 'folder_ad';
$TEMPLATES[VFolder][DocFile]    = $TEMPLATES[Folder][DocFile] = 'folder';
$TEMPLATES[VFolder][Event]      = $TEMPLATES[Folder][Event] = 'folder_events';
$TEMPLATES[VFolder][Image]      = $TEMPLATES[Folder][Image] = 'folder_image';
$TEMPLATES[VFolder][Link]       = $TEMPLATES[Folder][Link] = 'folder';
$TEMPLATES[VFolder][Poll]       = $TEMPLATES[Folder][Poll] = 'pollfolder';
$TEMPLATES[VFolder][none]       = $TEMPLATES[Folder][none] = 'folder_none';
$TEMPLATES[CFolder][0] = 'cfolder';
$TEMPLATES[CFolder][Article] = 'cfolder';
$TEMPLATES[CFolder][DocFile] = 'cfolder';
$TEMPLATES[CFolder][Event] = 'cfolder';
$TEMPLATES[CFolder][Image] = 'cfolder_image';
$TEMPLATES[CFolder][Link] = 'cfolder';
$TEMPLATES[CFolder][Poll] = 'cfolder';
$TEMPLATES[CFolder][none] = 'cfolder';
$TEMPLATES[LFolder][0] = 'folder';
$TEMPLATES[LFolder][Article] = 'folder';
$TEMPLATES[LFolder][DocFile] = 'folder';
$TEMPLATES[LFolder][Event] = 'folder_events';
$TEMPLATES[LFolder][Image] = 'folder_image';
$TEMPLATES[LFolder][Link] = 'folder';
$TEMPLATES[LFolder][Poll] = 'pollfolder';
$TEMPLATES[LFolder][none] = 'folder_none';
$TEMPLATES[SFolder][0] = 'sfolder';
$TEMPLATES[SFolder][Article] = 'sfolder';
$TEMPLATES[SFolder][DocFile] = 'sfolder';
$TEMPLATES[SFolder][Event] = 'folder_events';
$TEMPLATES[SFolder][Image] = 'sfolder';
$TEMPLATES[SFolder][Link] = 'sfolder';
$TEMPLATES[SFolder][Poll] = 'sfolder';
$TEMPLATES[PicFolder][0] = 'folder_image';
$TEMPLATES[PicFolder][Image] = 'folder_image';
$TEMPLATES[Image] = 'image';
$TEMPLATES[Index] = 'index';
$TEMPLATES[Link] = 'link';
$TEMPLATES[PollResults] = 'results';
$TEMPLATES[Poll] = 'poll';
$TEMPLATES[Search] = 'search';
$TEMPLATES[UserList] = 'users';
$TEMPLATES[User] = 'user';
$TEMPLATES[UserRatings] = 'user_ratings';
$TEMPLATES[Month] = 'month';
$TEMPLATES[Ratings] = 'ratings';
$TEMPLATES[Slideshow] = 'slideshow';
$TEMPLATES[Docs] = 'docs';

// $TEMPLATES[Email] = 'email';
// These are various email templates
$TEMPLATES[Email][ascii] = 'email_ascii';
$TEMPLATES[Email][html] = 'email_html';
$TEMPLATES[Share][ascii] = 'email_ascii_share';
$TEMPLATES[Share][html] = 'email_html_share';
$TEMPLATES[ShareFolder][ascii] = 'email_ascii_sharefolder';
$TEMPLATES[ShareFolder][html] = 'email_html_sharefolder';
$TEMPLATES[Daily][ascii] = 'email_ascii_daily';
$TEMPLATES[Daily][html] = 'email_html_daily';
$TEMPLATES[Mailto][ascii] = 'email_ascii_mailto';
$TEMPLATES[Mailto][html] = 'email_html_mailto';
$TEMPLATES[Comment][ascii] = 'email_ascii_comment';
$TEMPLATES[Comment][html] = 'email_html_comment';
$TEMPLATES[Confirm][ascii] = 'email_ascii_confirm';
$TEMPLATES[Confirm][html] = 'email_html_confirm';

// Notification Templates
$TEMPLATES[Notification][document] = 'notification';
$TEMPLATES[Notification][folder] = 'notification';
$TEMPLATES[Notification][user] = 'notification';

// subscription notification templates
$TEMPLATES[Notifications] = 'notifications';
$TEMPLATES[Notify][online] = 'notify_online';
$TEMPLATES[Notify][ascii] = 'notify_ascii';
$TEMPLATES[Notify][html] = 'notify_html';
$TEMPLATES[Notify][immediate] = 'notify_immediate';
$TEMPLATES[Subscriptions] = 'subscriptions';

// XML response template (used by Trackback)
$TEMPLATES[XMLresponse] = 'xmlresponse';
$TEMPLATES[XMLrss] = 'rss';

// used by error.php (HTTP error handler)
$TEMPLATES[Error] = 'error';

// GROUP templates
$TEMPLATES[Groups] = 'groups';
$TEMPLATES[Group] = 'group';

// various other weird things
$TEMPLATES[Comments] = 'comments';

// document classes
include "${LOCAL_PATH}classes/document.php";
include "${LOCAL_PATH}classes/article.php";
include "${LOCAL_PATH}classes/notice.php";
include "${LOCAL_PATH}classes/ad.php";
include "${LOCAL_PATH}classes/comment.php";
include "${LOCAL_PATH}classes/event.php";
include "${LOCAL_PATH}classes/file.php";
include "${LOCAL_PATH}classes/image.php";
include "${LOCAL_PATH}classes/link.php";
include "${LOCAL_PATH}classes/poll.php";

// groups
include "${LOCAL_PATH}classes/group.php";

// folder classes
include "${LOCAL_PATH}classes/folder.php";      // personal folder
// include "${LOCAL_PATH}classes/pfolder.php";    // public folder
include "${LOCAL_PATH}classes/lfolder.php";     // limited folder
include "${LOCAL_PATH}classes/cfolder.php";     // competition folder
include "${LOCAL_PATH}classes/sfolder.php";     // scheduled folder
include "${LOCAL_PATH}classes/vfolder.php";     // virtual folder

// weblogs.com support
include "${LOCAL_PATH}classes/pingWeblogs.php";

// email classes
include "${LOCAL_PATH}classes/email.php";
include "${LOCAL_PATH}classes/notification.php";

// subscriptions
include "${LOCAL_PATH}classes/subscription.php";

// establishes classes
function establish_classes() {
  global $CLASSES,$DB;
  $q = "SELECT obj_active,obj_class,obj_class_file FROM objs";
  $r = $DB->read($q);
  while(list($active,$class,$classfile) = $DB->fetch_array($r)) {
    if (!$active) {
      unset($CLASSES[$class]);
    }
  }
}

?>
