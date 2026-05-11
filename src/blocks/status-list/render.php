<?php
/**
 * Status List Block Render Callback
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
?>
<ul>
	<li>a list of data</li>
</ul>