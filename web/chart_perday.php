<?php
// chart_perday.php
// $Id: chart_perday.php,v 1.10 2003/06/07 01:27:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
//
// This file generates a GIF image of a bar chart showing the
// number of visits per day for all data in the activity table
// It requires installation of the PHPLOT library.

include "siteframe.php";
include "/usr/local/lib/php/phplot/phplot.php";

$days = $_GET['days'];

$q = "SELECT DATE_FORMAT(session_date,'%Y-%m-%d'),COUNT(*) ".
     "FROM sessions ".
     ($days ?
        "WHERE (session_date > DATE_SUB(NOW(),INTERVAL $days DAY)) " :
        "") .
     "GROUP BY 1 ORDER BY 1";
$r = $DB->read($q);
while(list($dt,$num) = $DB->fetch_array($r)) {
    $data[] = array( $nrows ? '' : $start = $dt, $num);
    $nrows++;
}

$graph = new PHPlot;

$graph->SetDataType( "text-data" );
$graph->SetDataValues($data);
// $graph->SetImageArea(500,300);
$graph->SetPlotType( "area" );
$graph->SetTitleFontSize( "2" );
$graph->SetTitle( "$SITE_NAME\nVisits per day since $start" );
// $graph->SetPlotAreaWorld(2000,0,2035,2000);
$graph->SetPlotBgColor( "white" );
$graph->SetPlotBorderType( "left" );
$graph->SetBackgroundColor( "white" );
$graph->SetXGridLabelType( "time" );
// $graph->SetNumHorizTicks( 4 );
// $graph->SetYScaleType( "log" );
$graph->SetDataColors( array( "black" ), array( "black" ));
$graph->SetFileFormat( "png" );

header( "Content-type: image/png" );
$graph->DrawGraph();

?>
