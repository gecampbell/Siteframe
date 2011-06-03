<?php
// chart_ratings.php
// $Id: chart_ratings.php,v 1.4 2003/06/07 01:27:23 glen Exp $
// Copyright (c)2001-2003, Broadpool, LLC. All rights reserved. 
// see LICENSE.txt for details.
//
// This file generates a bar chart showing how many people cast
// each possible rating.

include "siteframe.php";
include "/usr/local/lib/php/phplot/phplot.php";

$q = "SELECT rating,COUNT(*) ".
     "FROM ratings ".
     "GROUP BY 1 ORDER BY 1";
$r = $DB->read($q);
while(list($rate,$num) = $DB->fetch_array($r)) {
    $data[] = array( $rate, $num );
    $nrows++;
}

$graph = new PHPlot;

$graph->SetDataType( "text-data" );
$graph->SetDataValues($data);
// $graph->SetImageArea(500,300);
$graph->SetPlotType( "bar" );
$graph->SetTitleFontSize( "2" );
$graph->SetTitle( "$SITE_NAME\nRating Distribution" );
// $graph->SetPlotAreaWorld(2000,0,2035,2000);
$graph->SetPlotBgColor( "white" );
$graph->SetPlotBorderType( "left" );
$graph->SetBackgroundColor( "white" );
$graph->SetXGridLabelType( "time" );
// $graph->SetNumHorizTicks( 4 );
// $graph->SetYScaleType( "log" );
$graph->SetDataColors( array( "navy" ), array( "gray" ));
$graph->SetFileFormat( "png" );

header( "Content-type: image/png" );
$graph->DrawGraph();

?>
