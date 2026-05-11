<?php
/**
 * Plugin Settings
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

namespace ChoctawNation\HealthDashboard\WP;

/**
 * Class Plugin_Settings
 */
class Plugin_Settings {
	/**
	 * Option key for storing plugin settings in the WordPress options table.
	 */
	private const OPTION_KEY = 'cno_health_dashboard_settings';

	/**
	 * Namespace for REST API endpoints related to plugin settings.
	 */
	public const ADMIN_REST_URL = 'cno-health-dashboard-settings/v1';

	/**
	 * Namespace for public REST API endpoints related to health data.
	 */
	public const PUBLIC_REST_URL = 'cno/v1';


	/**
	 * Initialize the default option on plugin activation if it does not already exist.
	 *
	 * @return void
	 */
	public function initialize_defaults(): void {
		if ( false === get_option( self::OPTION_KEY ) ) {
			add_option( self::OPTION_KEY, $this->get_defaults() );
		}
	}

	/**
	 * Get settings
	 */
	public function get_settings(): array {
		return get_option( self::OPTION_KEY, $this->get_defaults() );
	}

	/**
	 * Update settings
	 *
	 * @param array $new_settings The new settings to save.
	 * @return bool True if the settings were successfully updated, false otherwise.
	 */
	public function update_settings( array $new_settings ): bool {
		return update_option( self::OPTION_KEY, $new_settings );
	}

	/**
	 * Get default settings
	 */
	public function get_defaults(): array {
		return array(
			'page_id'  => null,
			'meta_key' => '_health_data',
		);
	}

	/**
	 * Register the setting with WordPress so it can be read and validated.
	 *
	 * @return void
	 */
	public function register(): void {
		register_setting(
			self::OPTION_KEY . '_group',
			self::OPTION_KEY,
			array(
				'type'    => 'array',
				'default' => $this->get_defaults(),
			)
		);
	}

	/**
	 * Delete the plugin settings from the database.
	 *
	 * @return void
	 */
	public function delete_settings(): void {
		if ( false !== get_option( self::OPTION_KEY ) ) {
			delete_option( self::OPTION_KEY );
		}
	}
}
