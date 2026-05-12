<?php
/**
 * Health Dashboard Data REST Route
 *
 * @package ChoctawNation
 * @subpackage HealthAnalyticsData
 */

namespace ChoctawNation\HealthDashboard;

use Error;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Health Data API Route Handler
 */
class Rest_Router extends WP_REST_Controller {
	/**
	 * File_Reader instance
	 *
	 * @var File_Reader $file_reader
	 */
	private File_Reader $file_reader;

	/**
	 * Constructor
	 *
	 * @param File_Reader $file_reader The File Reader object
	 */
	public function __construct( File_Reader $file_reader ) {
		$this->file_reader = $file_reader;
	}

	/**
	 * Register Routes
	 */
	public function register_routes() {
		$namespace  = 'cno/v1';
		$route_base = '/health-data';
		register_rest_route(
			$namespace,
			$route_base . '/data',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_latest_health_data' ),
				'permission_callback' => '__return_true',
				'schema'              => array( $this, 'get_health_data_schema' ),
			),
		);
		register_rest_route(
			$namespace,
			$route_base . '/last-updated',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_last_updated' ),
				'permission_callback' => '__return_true',
				'schema'              => array(
					'type'        => 'string',
					'pattern'     => '^\d{4}-\d{2}-\d{2}$',
					'description' => 'The last updated date in YYYY-MM-DD format',
				),
			),
		);
		register_rest_route(
			$namespace,
			$route_base . '/status-list',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status_list' ),
				'permission_callback' => '__return_true',
				'schema'              => array(
					'type'                 => 'object',
					'additionalProperties' => array(
						'type' => 'string',
						'enum' => array( 'Increasing', 'Declining', 'Plateauing', 'No data' ),
					),
				),
			),
		);
	}

	/**
	 * Get the latest health data from special folder
	 *
	 * @return WP_REST_Response the data as a JSON Object or an error
	 */
	public function get_latest_health_data(): WP_REST_Response|WP_Error {
		try {
			$health_data = $this->file_reader->get_latest_data();
			$json        = wp_json_encode( $health_data, JSON_THROW_ON_ERROR );
			return rest_ensure_response( $health_data );
		} catch ( Error $e ) {
			return new WP_Error( 'data_error', "Couldn't retrieve health data! " . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Get the schema for the health data
	 *
	 * @return array the schema for the health data
	 */
	public function get_health_data_schema() {
		return array(
			'type'       => 'object',
			'required'   => array( 'WeekOfYr', 'diseases' ),
			'properties' => array(
				'WeekOfYr' => array(
					'type'       => 'object',
					'required'   => array( 'start', 'end' ),
					'properties' => array(
						'start' => array(
							'type'        => 'string',
							'pattern'     => '^\d{4}-\d{2}-\d{2}$',
							'description' => 'The starting date of the week in YYYY-MM-DD format',
						),
						'end'   => array(
							'type'        => 'string',
							'pattern'     => '^\d{4}-\d{2}-\d{2}$',
							'description' => 'The ending date of the week in YYYY-MM-DD format',
						),
					),
				),
				'diseases' => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => array(
						'type'       => 'object',
						'required'   => array( 'Comparison', 'PositivityRate' ),
						'properties' => array(
							'Comparison'     => array(
								'type' => 'string',
								'enum' => array( 'Increasing', 'Declining', 'Plateauing', 'No data' ),
							),
							'PositivityRate' => array(
								'type' => 'string',
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Get the last updated date from the latest health data entry
	 */
	public function get_last_updated(): WP_REST_Response|WP_Error {
		try {
			$data         = $this->file_reader->get_latest_data();
			$last_updated = end( $data )['WeekOfYr']['end'];

			return rest_ensure_response( $last_updated );
		} catch ( Error $e ) {
			return new WP_Error( 'data_error', "Couldn't retrieve last updated time! " . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Get the latest disease statuses as list elements
	 */
	public function get_status_list(): WP_REST_Response|WP_Error {
		try {
			$status_list = $this->file_reader->get_latest_disease_statuses();
			return rest_ensure_response( $status_list );
		} catch ( Error $e ) {
			return new WP_Error( 'data_error', "Couldn't retrieve status list! " . $e->getMessage(), array( 'status' => 500 ) );
		}
	}
}
