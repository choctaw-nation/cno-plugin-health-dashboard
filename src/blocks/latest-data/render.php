<?php
/**
 * Last Updated Block Render Callback
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

use ChoctawNation\HealthDashboard\File_Reader;

$file_reader = new File_Reader( 'cno_health_dashboard_get_filesystem' );
$data        = $file_reader->get_latest_data();
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