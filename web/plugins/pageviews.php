<?php
// $Id: pageviews.php,v 1.3 2004/10/24 05:33:51 glen Exp $
// Copyright (c)2004, Glen Campbell
// This plugin tracks the number of page views by site.
// It intercepts document.php and tracks the number of times that a
// specific document (id=N) has been displayed.
// It creates the template variable {document_page_views} that is only
// available on that page.

// the table definition
$DOC_PAGE_VIEWS_TABLE = <<<ENDSQL
CREATE TABLE doc_page_views
(
    doc_id          INTEGER NOT NULL,
    num_views       INTEGER,
    PRIMARY KEY (doc_id)
)
ENDSQL;

// create the plugin object
$DocumentPageViews = new Plugin('DocumentPageViews');

// DOCUMENT_PAGE_VIEWS_ENABLE
// This global property determines whether or not the page views are enabled
$DocumentPageViews->set_global(
    'Documents',
    'DOCUMENT_PAGE_VIEWS_ENABLE',
    array(
        'type' => 'checkbox',
        'rval' => 1,
        'prompt' => 'Enable page view tracking',
        'doc' => 'Check to enable tracking of document page views. '.
                 'Note that this can have a performance impact on the '.
                 'viewing of each document page (document.php).'
    )
);

if ($DOCUMENT_PAGE_VIEWS_ENABLE)
{
    // check to see if the page view tracking table exists; if not, create it
    if (!$DOCUMENT_PAGE_VIEWS_INSTALLED)
    {
        $DB->write($DOC_PAGE_VIEWS_TABLE);
        set_global('DOCUMENT_PAGE_VIEWS_INSTALLED', 1);
    }
    
    // establish an output property
    $DocumentPageViews->set_output_property(
        'Document',
        array(
            'name' => 'document_page_views',
            'callback' => 'GetDocumentPageViews'
        )
    );
    
    // if we're in document.php, then we need to count a new view
    if (basename($_SERVER['PHP_SELF']) == 'document.php')
    {
        $id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
        // only update valid ID's
        if ($id)
        {
            // first, we attempt updating an existing count (the most common case)
            $q = sprintf('UPDATE doc_page_views SET num_views=num_views+1 WHERE '.
                         'doc_id=%d', $id);
            $r = $DB->write($q);
            // if that fails, we insert a count of 1
            if ($DB->affected_rows()!=1)
            {
                $q = sprintf('INSERT INTO doc_page_views VALUES (%d, 1)', $id);
                $r = $DB->write($q);
                if (!$r)
                    die($DB->error());
            }
        }
    }
}

// register the plugin
$DocumentPageViews->register();

// the callback function
function GetDocumentPageViews(&$doc)
{
    global $DB;
    if (!$doc->get_property('doc_id'))
        return 0;
    $q = sprintf('SELECT num_views FROM doc_page_views WHERE doc_id=%d',
                    $doc->get_property('doc_id'));
    $r = $DB->read($q);
    list($count) = $DB->fetch_array($r);
    return $count;
}

?>