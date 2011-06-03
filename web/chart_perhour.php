<?php
// chart_perhour.php
// $Id: chart_perhour.php,v 1.7 2003/06/10 03:41:14 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
//
// This file generates a GIF image of a bar chart showing the
// number of visits per day for all data in the activity table
// It requires installation of the PHPLOT library.

include "siteframe.php";
include "/usr/local/lib/php/phplot/phplot.php";

$days = $_GET['days'];

$q = "SELECT DATE_FORMAT(session_date,'%Y-%m-%d'),DATE_FORMAT(session_date,'%H:00'),COUNT(*) ".
     "FROM sessions ".
     ($days ?
        "WHERE (session_date > DATE_SUB(NOW(),INTERVAL $days DAY)) " :
        "") .
     "GROUP BY 1,2 ORDER BY 1,2";
$r = $DB->read($q);
while(list($dt,$hr,$num) = $DB->fetch_array($r)) {
    $data[] = array( $nrows==0 ? $start = $dt :
                     ((($hr%3)==0) ? $hr : ''), $num);
    $nrows++;
}

$graph = new PHPlot;

$graph->SetDataType( "text-data" );
$graph->SetDataValues($data);
// $graph->SetImageArea(500,300);
$graph->SetPlotType( "bars" );
$graph->SetTitleFontSize( "2" );
$graph->SetTitle( "$SITE_NAME\nVisits per hour since $start" );
// $graph->SetPlotAreaWorld(2000,0,2035,2000);
$graph->SetPlotBgColor( "white" );
$graph->SetPlotBorderType( "left" );
$graph->SetBackgroundColor( "white" );
$graph->SetXGridLabelType( "time" );
// $graph->SetNumHorizTicks( 4 );
// $graph->SetYScaleType( "log" );
$graph->SetDataColors( array( "blue" ), array( "black" ));
$graph->SetFileFormat( "png" );

header( "Content-type: image/png" );
$graph->DrawGraph();

?>
