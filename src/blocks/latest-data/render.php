<?php
/**
 * Last Updated Block Render Callback
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

use ChoctawNation\HealthDashboard\Health_Analytics_Handler;

$handler = new Health_Analytics_Handler();
$data    = $handler->get_latest_health_data_array();
if ( empty( $data ) ) {
	echo 'No data available';
	return;
}
$latest_date = \DateTime::createFromFormat( 'Y-m-d', end( $data )['WeekOfYr']['end'] );
printf(
	"<time datetime='%s'>%s</time>",
	$latest_date->format( 'Y-m-d' ),
	$latest_date->format( $attributes['format'] )
);