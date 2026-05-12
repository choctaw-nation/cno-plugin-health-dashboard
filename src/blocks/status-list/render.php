<?php
/**
 * Status List Block Render Callback
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

use ChoctawNation\HealthDashboard\File_Reader;

$file_reader = new File_Reader( 'cno_health_dashboard_get_filesystem' );
$data        = $file_reader->get_latest_disease_statuses();
if ( empty( $data ) ) {
	echo 'No data available';
	return;
}
?>
<ul>
	<?php foreach ( $data as $disease => $comparison ) : ?>
	<li><?php echo esc_html( $disease ); ?>: <?php echo esc_html( $comparison ); ?></li>
	<?php endforeach; ?>
</ul>