<?php
/**
 * Health Analytics Data Handler
 * Controller for the Health Analytics Data
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

namespace ChoctawNation\HealthDashboard;

use SimpleXMLElement;
use WP_Error;
use WP_REST_Response;

/**
 * Health Analytics Data Handler
 */
class Health_Analytics_Handler {
	/**
	 * The length of time to cache the data
	 *
	 * @var int
	 */
	private int $cache_length = 60 * 60 * 24; // 24 hours

	/**
	 * The filesystem object
	 *
	 * @var \WP_Filesystem_Base $fs
	 */
	private \WP_Filesystem_Base $fs;

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		$this->fs = $wp_filesystem;
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'cno/v1',
					'/health-data',
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_latest_health_data' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);
	}

	/**
	 * Get the latest health data from special folder
	 *
	 * @return WP_REST_Response the data as a JSON Object or an error
	 */
	public function get_latest_health_data(): WP_REST_Response {
		$health_data = $this->get_cache();
		if ( $health_data ) {
			return rest_ensure_response( $health_data );
		}
		$health_data = $this->get_latest_health_data_file();
		if ( false === $health_data ) {
			return rest_ensure_response( new WP_Error( 'data_error', 'Couldn\'t parse data!', array( 'status' => 500 ) ) );
		}
		if ( is_wp_error( $health_data ) ) {
			return rest_ensure_response( $health_data );
		}
		$health_data = $this->xml_to_json( $health_data );
		if ( is_wp_error( $health_data ) ) {
			return rest_ensure_response( $health_data );
		}
		return rest_ensure_response( $health_data );
	}

	/**
	 * Get the latest health data as an array
	 *
	 * @return array|null the data as an array or `null` if there was an issue
	 */
	public function get_latest_health_data_array(): ?array {
		$health_data_file = $this->get_latest_health_data_file();
		if ( ! $health_data_file || is_wp_error( $health_data_file ) ) {
			return null;
		}
		$health_data = $this->xml_to_json( $health_data_file );
		if ( is_wp_error( $health_data ) ) {
			return null;
		}
		return json_decode( $health_data, true );
	}

	/**
	 * Get the disease statuses as list elements
	 *
	 * @return string the disease statuses
	 */
	public function get_disease_statuses(): string {
		$data               = $this->get_latest_health_data_array();
		$diseases           = end( $data )['diseases'];
		$markup             = '';
		$disease_rename_map = array(
			'FLU'   => 'Flu',
			'COVID' => 'COVID-19',
		);
		foreach ( $diseases as $disease => $data ) {
			if ( array_key_exists( $disease, $disease_rename_map ) ) {
				$disease = $disease_rename_map[ $disease ];
			}
			$markup .= "<li>{$disease} Status: " . ( $data['Comparison'] ?? 'No data' ) . '</li>';
		}
		return $markup;
	}

	/**
	 * Echoes the disease statuses as list elements
	 */
	public function the_disease_statuses(): void {
		echo $this->get_disease_statuses();
	}

	/**
	 * Get the latest health data file
	 *
	 * @return SimpleXMLElement|false|WP_Error the latest health data file, `false` if it doesn't exist, or a WP_Error if there was an issue
	 */
	private function get_latest_health_data_file(): SimpleXMLElement|false|WP_Error {
		$health_data_file = get_template_directory() . '/health/PublicHealth_CalcData.xml';
		if ( $this->fs->exists( $health_data_file ) ) {
			$health_data = simplexml_load_file( $health_data_file );
			if ( ! $health_data ) {
				return new WP_Error( 'data_error', 'Couldn\'t parse data!', array( 'status' => 500 ) );
			}
			return $health_data;
		}
		return new WP_Error( 'no_data', 'Couldn\'t find data!', array( 'status' => 404 ) );
	}

	/**
	 * Convert XML to JSON
	 *
	 * @param SimpleXMLElement $xml the XML Element to convert
	 * @return string|WP_Error the JSON or a WP_Error if there was an issue
	 */
	private function xml_to_json( SimpleXMLElement $xml ): string|WP_Error {
		$data = array();
		foreach ( $xml->children() as $data_point ) {
			foreach ( $data_point->children() as $property ) {
				$name = $property['Name']->__toString();
				if ( 'WeekOfYr' === $name ) {
					$week_of_year = $this->get_key_value_pairs( $property );
				} else {
					$disease_data = $this->get_key_value_pairs( $property );
				}
			}
			$data[] = array(
				'WeekOfYr' => $week_of_year,
				'diseases' => $disease_data,
			);
		}
		sort( $data );
		$json = wp_json_encode( $data );
		if ( false === $json ) {
			return new WP_Error( 'json_error', 'Couldn\'t encode JSON!', array( 'status' => 500 ) );
		} else {
			$this->cache_response( $json );
			return $json;
		}
	}

	/**
	 * Recursively destructures the XML data into key value pairs
	 *
	 * @param SimpleXMLElement $xml the XML Element to destructure
	 * @return array|string the key value pairs, or an error
	 */
	private function get_key_value_pairs( SimpleXMLElement $xml ) {
		$details  = array();
		$children = $xml->count();
		if ( 0 === $children ) {
			return $xml[0]->__toString();
		}
		foreach ( $xml->children() as $property ) {
			$name = $property['Name']->__toString();
			if ( 'Key' === $name ) {
				$key = $property[0]->__toString();
			} else {
				$value           = $this->get_key_value_pairs( $property );
				$details[ $key ] = 'NOVALUE' === $value ? null : $value;
			}
		}
		return $details;
	}

	/**
	 * Cache the response
	 *
	 * @param string $json the JSON to cache
	 */
	private function cache_response( string $json ): void {
		set_transient( 'health_data', $json, $this->cache_length );
	}

	/**
	 * Get the cached data
	 *
	 * @return string|false the cached data
	 */
	private function get_cache(): string|false {
		$cache = get_transient( 'health_data' );
		if ( $cache ) {
			return $cache;
		}
		return false;
	}
}
