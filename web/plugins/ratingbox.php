<?php
// ratingbox.php
// $Id: ratingbox.php,v 1.4 2003/06/24 02:30:19 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// creates a new macro {!ratingbox [N]!} that either displays
// an input form or the user's rating (if already rated)

$ratingbox = new Plugin('ratingbox');
$ratingbox->set_macro('ratingbox','FCNratingbox');

// this function implements the ratingbox macro
function FCNratingbox($arg) {
  global $CURUSER,$DB;
  if (!$CURUSER)
    return '';
  $class = doctype($arg[0]);
  if ($class=='')
    return '';
  $doc = new $class($arg[0]);
  if (!$doc->get_property('allow_ratings'))
    return '';
  $boxtext = <<<ENDBOX
  <form method="post"
      action="{site_path}/rate.php"
      enctype="multipart/form-data"
      name="ratingbox"
      style="border:none; display:inline; padding:0; margin:0;">
  <table>
  <tr>
  <td align="center">1</td>
  <td align="center" colspan="8">(worst) &larr; select rating &rarr; (best)</td>
  <td align="center">10</td>
  </tr>
  <tr>
  <td><input type="radio" name="rating" value="1" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="2" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="3" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="4" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="5" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="6" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="7" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="8" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="9" onClick="ratingbox.submit();"/></td>
  <td><input type="radio" name="rating" value="10" onClick="ratingbox.submit();"/></td>
  </tr>
  </table>
  <input type="hidden" name="submitted" value="1"/>
  <input type="hidden" name="return_location" value="$SITE_PATH/document.php?id={doc_id}"/>
  <input type="hidden" name="id" value="{doc_id}"/>
  </form>
ENDBOX;
  $q = sprintf(
        'SELECT rating FROM ratings WHERE doc_id=%d AND user_id=%d',
        $arg[0],
        $CURUSER->get_property('user_id'));
  $r = @$DB->read($q);
  list($rating) = $DB->fetch_array($r);
  if ($rating) {
    return sprintf('You rated this document %d',$rating);
  }
  else {
    return $boxtext;
  }
}

?>
