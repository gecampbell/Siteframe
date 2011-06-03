<?php
// comment.php
// $Id: comment.php,v 1.13 2003/06/24 02:30:19 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// This file handles the comment function

require "siteframe.php";

$PAGE->set_property('page_title','Comment');
$PAGE->set_property('form_action',$PHP_SELF);
$PAGE->set_property('form_instructions',_MSG_COMMENT_INSTR);

$JAVASCRIPT_RETURN = <<<ENDRETURN
<html>
<body>
<script language="JavaScript">
opener.location.href='${SITE_PATH}/document.php?id=%d';
self.close();
</script>
</body>
</html>
ENDRETURN;

// verify that the user is logged in
if (!$CURUSER && (!$ANONYMOUS_COMMENTS)) {
  $PAGE->set_property('error',_ERR_COMMENT_NOTLOGGEDIN);
}
// handle a submitted comment form
else if ($_POST['submitted']) {
  $comment = new Comment(0,$_POST['comment_doc_id']);
  $comment->set_property('rating',$_POST['rating']);
  $comment->set_input_form_values($comment->input_form_values());
  $comment->add();
  $PAGE->set_property('error',$comment->get_errors());
  if ($comment->errcount())
    $PAGE->input_form('body',$comment->input_form_values());
  else
    exit(sprintf($JAVASCRIPT_RETURN,$comment->get_property(comment_doc_id)));
}
// otherwise, an error if id=N not specified
else if (!$_GET['id']) {
  $PAGE->set_property('error',_ERR_COMMENT_NO_ID);
}
// the default case
else {
  $comment = new Comment();
  $comment->set_property('comment_doc_id',$_GET['id']);
  $comment->set_property('comment_owner_id',
    $CURUSER ? $CURUSER->get_property('user_id') : 0);
  $comment->set_property('comment_reply_to',$_GET['reply']);
  $PAGE->input_form('body',$comment->input_form_values());
}

if ($COMMENT_INLINE)
  $PAGE->pparse('page');
else
  $PAGE->pparse('popup');

?>
