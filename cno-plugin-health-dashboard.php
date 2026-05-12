<?php
/**
 * Plugin Name: [Choctaw Nation of Oklahoma] Health Dashboard App
 * Description: Displays a virality chart of common diseases with recharts.
 * Plugin URI: https://github.com/choctaw-nation/cno-plugin-health-dashboard
 * Version: 1.0.0
 * Author: Choctaw Nation of Oklahoma
 * Author URI: https://www.choctawnation.com
 * Text Domain: cno
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.2
 * Requires at least: 6.0
 * Tested up to: 6.9.4
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

use ChoctawNation\HealthDashboard\Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$cno_autoload_path = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $cno_autoload_path ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>CNO Plugin Health Dashboard is missing required dependencies. Please run Composer install or deploy the plugin with its vendor directory included.</p></div>';
		}
	);
	return;
}

require_once $cno_autoload_path;
$cno_plugin = new Plugin_Loader( __DIR__ );

// Plugin Lifecycle Hooks
register_activation_hook( __FILE__, array( $cno_plugin, 'activate' ) );

// Static method for uninstall since the plugin can't rely on instance methods.
register_uninstall_hook( __FILE__, array( 'ChoctawNation\HealthDashboard\Plugin_Loader', 'uninstall' ) );

// Load the Plugin
add_action( 'plugins_loaded', array( $cno_plugin, 'load_plugin' ) );

/**
 * Helper function to get the WP Filesystem instance, which is needed for file operations in the plugin.
 *
 * @return ?WP_Filesystem_Base The WP Filesystem instance, or `null` if it couldn't be initialized.
 */
function cno_health_dashboard_get_filesystem(): ?WP_Filesystem_Base {
	static $fs = null;

	if ( null !== $fs ) {
		return $fs;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	WP_Filesystem();

	global $wp_filesystem;

	$fs = $wp_filesystem ?: null; // phpcs:ignore Universal.Operators.DisallowShortTernary.Found

	return $fs;
}