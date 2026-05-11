<?php
/**
 * Admin screen and settings registration for Health Dashboard.
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

namespace ChoctawNation\HealthDashboard\WP\AdminScreen;

use ChoctawNation\HealthDashboard\WP\Plugin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Screen
 *
 * Handles the admin menu and settings for the Health Dashboard plugin.
 */
class Admin_Screen {
	/**
	 * Plugin Settings instance.
	 *
	 * @var Plugin_Settings $plugin_settings
	 */
	private Plugin_Settings $plugin_settings;

	/**
	 * Constructor
	 *
	 * @param Plugin_Settings $plugin_settings The plugin settings instance to use for accessing configuration values.
	 */
	public function __construct( Plugin_Settings $plugin_settings ) {
		$this->plugin_settings = $plugin_settings;
	}
	/**
	 * Register admin menu and submenu pages.
	 */
	public function register_menus() {
		$cap = 'manage_options';
		add_menu_page(
			'Health Dashboard Settings',
			'Health Dashboard Settings',
			$cap,
			'cno-health-dashboard',
			array( $this, 'render_overview' ),
			'dashicons-chart-line',
			75
		);
	}

	/**
	 * Enqueue admin screen assets, but only on our plugin's settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function load_required_assets( string $hook_suffix ) {
		if ( 'toplevel_page_cno-health-dashboard' !== $hook_suffix ) {
			return;
		}

		$settings             = $this->plugin_settings->get_settings();
		$selected_page_id     = isset( $settings['page_id'] ) ? absint( $settings['page_id'] ) : 0;
		$selected_page_option = null;

		if ( $selected_page_id > 0 ) {
			$selected_page = get_post( $selected_page_id );
			if ( $selected_page && 'page' === $selected_page->post_type ) {
				$selected_page_title = get_the_title( $selected_page_id );
				if ( '' === $selected_page_title ) {
					$selected_page_title = sprintf( '(No title) #%d', $selected_page_id );
				}

				$selected_page_option = array(
					'value' => (string) $selected_page_id,
					'label' => $selected_page_title,
				);
			}
		}

		$asset_file         = require_once dirname( __DIR__, 3 ) . '/build/admin/admin-app.asset.php';
		$plugin_assets_path = dirname( __DIR__, 2 );
		$asset_name         = 'cno-health-dashboard-admin';
		wp_enqueue_script(
			$asset_name,
			plugin_dir_url( $plugin_assets_path ) . 'build/admin/admin-app.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			array( 'strategy' => 'defer' )
		);
		wp_add_inline_script(
			$asset_name,
			'const cnoHealthDashboardApiSettings = ' . wp_json_encode(
				array(
					'restBase'           => $this->plugin_settings::ADMIN_REST_URL,
					'nonce'              => wp_create_nonce( 'wp_rest' ),
					'selectedPageOption' => $selected_page_option,
				)
			),
			'before'
		);
	}

	/**
	 * Render the overview page content.
	 */
	public function render_overview() {
		echo '<div class="wrap"><h1>Health Dashboard API</h1><div id="cno-health-dashboard-api-settings"></div><noscript>This plugin relies on JavaScript to function properly. Please enable JavaScript in your browser settings and refresh the page.</noscript></div>';
	}
}
