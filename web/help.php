<?php
// help.php
// $Id: help.php,v 1.5 2003/06/27 01:35:08 glen Exp $
// Copyright (c)2003, Broadpool, LLC. All rights reserved.
// see LICENSE.txt for details.
//
// this is the online help file
require "siteframe.php";
$MACROS['entry'] = <<<ENDENTRY
<p><small>[<a href="#" onClick="javascript:self.close();">Close</a>]</small></p>

<!-- entry: $1 -->
<a name="$1"><h1>$2</h2></a>

ENDENTRY;

$HELPBODY = <<<ENDHELPBODY
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<title>Help</title>
<style type="text/css">
/* <![CDATA[ */
body {
font-family: arial,helvetica,sans-serif;
font-size: medium;
margin: 10px;
line-height: 140%;
}
p { margin: 0 0 10px 0; }
h1 { margin: 2em 0 .5em 0; font-size: 1.4em; }
/* ]]> */
</style>
</head>
<body>
<h1>Siteframe online help</h1>
<p>This is the Siteframe online help system.
It is not a complete reference to the Siteframe system;
instead, it provides a brief reference to the various
settings and controls on the site.</p>
<p>You can access the help system wherever you
see the [?] hyperlink.</p>

{!# keep the rest of these in alphabetical order if at all possible !}

{!entry 'autoformat' 'Automatic Text Formatting'!}
<p>Some text entry fields support automatic formatting. Automatic formatting
provides a simple way to format your text. Here are the basic rules:</p>
<p>Two or more newlines (carriage returns) to start a paragraph.</p>
<p>Hyperlinks are automatically linked; you can also use <code>[text|URL]</code>
to create a link with different text.</p>
<p>Text between double single quotes is ''<em>emphasized</em>'' (usually italic).
Text between __<b>double underscores</b>__ is in bold.</p>
<p>An asterix (*) at the beginning of the line indicates a bulleted list.
Lines that start with a (#) character are used for numbered lists.</p>
<p>Lines that start with a space are displayed in monospaced font.</p>
<p>"%%%" is a hard line break.</p>
<p><code>----</code> (four or more dashes) indicates a horizontal rule.</p>
<p>Exclamation points (!) at the beginning of a line indicate a heading.
More !!! points indicate more emphasis.</p>
<p><b>Note:</b> If you enter any HTML tags (indicated by a "&lt;" character),
then no automatic formatting is performed. In addition, certain HTML tags
are not allowed in documents created on the site, and will be automatically
removed if you attempt to use them.</p>

{!entry 'cookie' 'Cookies ("remember login")'!}
<p>If you check the "remember login" checkbox, then the website will
leave a cookie on your computer so that you do not have to login again
the next time you visit the site. A <i>cookie</i> is a small bit of
information that is stored on your computer, and is sent to the server
by your browser the next time you visit this site. In this case, the
cookie contains an encrypted string that is used to identify you to
the website; no personal information is stored in the cookie.</p>

{!entry 'doctag' 'Document Tag'!}
<p>The document tag is a text string that may be used to identify a
document. It can only be set by a site administrator. When used, it
allows simple URLs like <code>http://example.com/doc/treaty</code>
instead of <code>http://example.com/document.php?id=381</code>,
which is not as meaningful.

{!entry 'doctype' 'Document Type'!}
<p>This website supports a number of different documents (though the
site administrator(s) may have disabled some of them). These are some
of the more comment document types:</p>
<ul>
<li><b>Article</b> A simple web document containing primarily text.
Used for "normal" web pages and documentation.</li>
<li><b>File</b> This document has an uploaded file attached.
Once you have uploaded the file, users can download it
by clicking on it. Useful for uploading shared documents and
file archives.</li>
<li><b>Image</b> An image file is displayed as a picture. The file
itself should be in JPEG, PNG, or GIF format (not all file types
may be supported; it is dependent upon the server's software support).
When you upload the file, the website software will automatically create
thumbnail images in various sizes.</li>
<li><b>Poll</b> A multiple-choice questionnaire that tallies
its results automatically.</li>
</ul>

{!entry 'email' 'User e-mail address'!}
<p>An e-mail address is required to log in to a Siteframe website.
This e-mail address is used to uniquely identify you to the website.
By default, your e-mail address will not be visible on the
site (unless your site administrator has modified the software).</p>

{!entry 'docfolder' 'Document Folder'!}
<p>You can add your document to a folder for easy organization.
To be able to select a folder for your document, the folder must be
either (a)&nbsp;owned by you, (b)&nbsp;<i>public</i>, that is, open to anyone,
or (c)&nbsp;the folder permissions must have granted you <i>submittor</i>
rights on it.</p>

{!entry 'hidden' 'Hidden Documents'!}
<p>If the "hidden" checkbox on a document is checked, then the
document will only be visible through the user's "document" listing.
The document will not appear in any other listings on the site.
Hidden documents can still be viewed by site users; however, you
must provide a link to the document directly.</p>

{!entry 'nickname' 'User Nickname'!}
<p>You can optionally provide a nickname for your user account.
In some cases, the nickname will be used in place of your full name
(however, this has not been completely implemented throughout the website).</p>

{!entry 'password' 'Password'!}
<p>Your password authenticates you to the website. You should choose a
password that you can easily remember but which would be difficult for
someone else to guess.</p>

{!entry 'rating' 'Rating'!}
<p>A rating is a score (from 1 to 10, with 10 being the best) 
that you give to a document.
Usually, you are not permitted to rate your own documents (though this
policy can be modified by the site administrator). In addition, there 
may be a rule in place that requires ratings below a certain level
to be accompanied by a comment to justify the rating.</p>

{!entry 'register_model' 'Site Registration Model'!}
<p>The registration model determines how users can join and be authenticated
to your website. The <b>confirm</b> model is the default; users may register
and provide an e-mail address; however, that e-mail address must be verified
(by having the user respond to an encoded e-mail sent to it) before the user
can access the site. Unconfirmed users may be automatically deleted after a
period of time.</p>
<p>The <b>open</b> model allows users to register, but their
e-mail address is "trusted;" i.e., it does not have to be confirmed. This is
often convenient in an intranet implementation.</p>
<p>Finally, the <b>closed</b> model
does not allow any users to register; instead, they must be registered by a
site administrator. This means that only selected individuals can join the
site. In addition, there are other security controls in place with the
<b>closed</b> model; no one except logged-on users can view lists of users,
for example, thus ensuring privacy.</p>

{!entry 'summary' 'Document Summary'!}
<p>Many document types allow you to define a document summary in
addition to the main body of the document. The summary is often
displayed in listings; for example, when you view a folder containing
Articles, the summary will usually appear with the document's title.</p>

<div style="color:gray;">
<h1>Copyright Information</h1>
<p>The information contained in this web page is Copyright &copy;2003,
Broadpool, LLC. All rights reserved. This information is released in
accordance with the Creative Commons license contained in the file
LICENSE.txt which is distributed with this software.</p>
<p>You can learn more about this software at
<a href="http://siteframe.org">http://siteframe.org</a></p>
</div>
</body>
</html>
ENDHELPBODY;
print macro($HELPBODY);
exit;
?>