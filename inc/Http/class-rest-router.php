<?php
/**
 * Health Dashboard Data REST Route
 *
 * @package ChoctawNation
 * @subpackage HealthAnalyticsData
 */

namespace ChoctawNation\HealthDashboard\Http;

use ChoctawNation\HealthDashboard\WP\Notifier;
use ChoctawNation\HealthDashboard\WP\Plugin_Settings;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Health Data API Route Handler
 */
class Rest_Router extends WP_REST_Controller {
	/**
	 * The plugin settings instance.
	 *
	 * @var Plugin_Settings $plugin_settings
	 */
	private Plugin_Settings $plugin_settings;

	/**
	 * Whether the Rest Request is allowed
	 *
	 * @var bool $request_is_permitted
	 */
	private bool $request_is_permitted;

	/**
	 *  Whether the Endpoints are ready
	 *
	 * @var bool $endpoints_are_ready
	 */
	private bool $endpoints_are_ready;

	/**
	 * Notifier class instance
	 *
	 * @var Notifier $notifier
	 */
	private Notifier $notifier;

	/**
	 * Page ID to store the data on
	 *
	 * @var ?int $page_id
	 */
	private ?int $page_id;

	/**
	 * Meta Key to store the data under
	 *
	 * @var string $meta_key
	 */
	private string $meta_key;

	/**
	 * Build the Route
	 *
	 * @param Plugin_Settings $plugin_settings The Plugin Settings to get the REST Namespace from
	 * @param Notifier        $notifier The Notifier class to send failure emails with
	 */
	public function __construct( Plugin_Settings $plugin_settings, Notifier $notifier ) {
		$this->plugin_settings = $plugin_settings;
		$this->notifier        = $notifier;
		$this->page_id         = $this->plugin_settings->get_settings()['page_id'];
		$this->meta_key        = $this->plugin_settings->get_settings()['meta_key'];
		// if ( ! defined( 'HEALTH_REST_USERNAME' ) || ! defined( 'HEALTH_REST_PASSWORD' ) || empty( HEALTH_REST_PASSWORD ) || empty( HEALTH_REST_USERNAME ) ) {
		// $this->endpoints_are_ready = false;
		// } else {
		// $this->endpoints_are_ready = true;
		// }
		$this->schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'description' => 'This is the schema of the data our endpoint expects',
			'title'       => 'data',
			'type'        => 'array',
			'required'    => true,
			'items'       => array(
				'type'       => 'object',
				'required'   => true,
				'properties' => array(
					'WeekOfYr' => array(
						'required'   => true,
						'type'       => 'object',
						'properties' => array(
							'start' => array(
								'description' => 'Date Time string as YYYY-MM-DD',
								'required'    => true,
								'type'        => 'date-time',
							),
							'end'   => array(
								'description' => 'Date Time string as YYYY-MM-DD',
								'required'    => true,
								'type'        => 'date-time',
							),
						),
					),
					'diseases' => array(
						'required'   => true,
						'type'       => 'object',
						'properties' => array(
							'COVID' => array(
								'type'       => 'object',
								'properties' => array(
									'PositivityRate' => array(
										'required'   => true,
										'type'       => 'number',
										'minimum'    => 0,
										'maximum'    => 100,
										'multipleOf' => 0.1,
									),
									'Comparison'     => array(
										'required'    => true,
										'type'        => 'string',
										'description' => 'The comparison of positivity rate to the previous week, e.g. "Increasing", "Decreasing", "Plateauing", or "NOVALUE"',
									),
								),
							),
							'FLU'   => array(
								'type'       => 'object',
								'properties' => array(
									'PositivityRate' => array(
										'required'   => true,
										'type'       => 'number',
										'minimum'    => 0,
										'maximum'    => 100,
										'multipleOf' => 0.1,
									),
									'Comparison'     => array(
										'required'    => true,
										'type'        => 'string',
										'description' => 'The comparison of positivity rate to the previous week, e.g. "Increasing", "Decreasing", "Plateauing", or "NOVALUE"',
									),
								),
							),
							'RSV'   => array(
								'type'       => 'object',
								'properties' => array(
									'PositivityRate' => array(
										'required'   => true,
										'type'       => 'number',
										'minimum'    => 0,
										'maximum'    => 100,
										'multipleOf' => 0.1,
									),
									'Comparison'     => array(
										'required'    => true,
										'type'        => 'string',
										'description' => 'The comparison of positivity rate to the previous week, e.g. "Increasing", "Decreasing", "Plateauing", or "NOVALUE"',
									),
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Register Routes
	 */
	public function register_routes() {
		$namespace = $this->plugin_settings::PUBLIC_REST_URL;
		register_rest_route(
			$namespace,
			'/health-data',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_data' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_data' ),
					'permission_callback' => array( $this, 'can_update_items' ),
					'args'                => array( 'data' => $this->schema ),
				),
			),
		);
	}

	/**
	 * Checks if a given request has access to update a specific item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function can_update_items( WP_REST_Request $request ): true|WP_Error {
		$this->request_is_permitted = false;
		$received_auth              = $request->get_header( 'authorization' );
		$content_type               = $request->get_header( 'Content-Type' );
		if ( $received_auth ) {
			$permission = $this->set_request_is_permitted( $received_auth );
			if ( is_wp_error( $permission ) ) {
				return $permission;
			}
		}
		if ( $this->request_is_permitted ) {
			if ( $request->get_method() !== WP_REST_Server::READABLE && 'application/json' !== $content_type ) {
				return new WP_Error(
					'invalid_content_type',
					'Invalid Content Type! Please use application/json.',
					array(
						'status' => 400,
						'data'   => $request,
					)
				);
			}
			return true;
		}

		return new WP_Error(
			'unauthorized',
			'Sorry, you are not allowed to do that.',
			array(
				'status'       => 403,
				'current_user' => wp_get_current_user(),
				'data'         => $request,
			)
		);
	}

	/**
	 * Handles the Authorization Header
	 *
	 * @param string $received_auth The Authorization Header
	 */
	private function set_request_is_permitted( string $received_auth ) {
		if ( false === $this->endpoints_are_ready ) {
			return new WP_Error( 'endpoints_not_ready', 'The endpoints are not ready yet!', array( 'status' => 501 ) );
		}
		$auth                       = str_replace( 'Basic ', '', $received_auth );
		$user_pass                  = base64_decode( $auth ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$tuple                      = explode( ':', $user_pass );
		$username_checks_out        = HEALTH_REST_USERNAME === $tuple[0];
		$password_checks_out        = HEALTH_REST_PASSWORD === str_replace( ' ', '', $tuple[1] );
		$this->request_is_permitted = $username_checks_out && $password_checks_out;
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_data( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		if ( ! $this->page_id ) {
			return new WP_Error(
				'page_not_found',
				'Page Not Found! Please create a page with the slug "respiratory-health-hub".',
				array(
					'status' => 500,
					'data'   => $request,
				)
			);
		}
		$response = new WP_REST_Response( null, 200, array( 'Content-Type' => 'application/json' ) );
		$body     = $request->get_json_params();
		$data     = $body['data'];
		if ( ! $data ) {
			$this->notifier->send_notification( 'Updating Health Dashboard Data on CNO Site failed! ' . $request );
			return new WP_Error(
				'invalid_data',
				'Invalid Data! Please provide a valid JSON object.',
				array(
					'status' => 400,
					'data'   => $request,
				)
			);
		}

		$status = update_post_meta( $this->page->ID, $this->meta_key, $data );
		if ( false === $status ) {
			$this->notifier->send_notification( 'Updating Health Dashboard Data on CNO Site failed! ' . $data );
			$message = 'No new data given!';
			if ( ! $this->page->ID ) {
				$message = ( 'Page to update could not be located!' );
			}
			$response = new WP_Error(
				'update_failed',
				$message,
				array(
					'status' => 500,
					'data'   => $data,
				)
			);
			if ( ! $this->request_is_permitted ) {
				$response = new WP_Error(
					'unauthorized',
					'Sorry, you are not allowed to do that.',
					array(
						'status'       => 403,
						'current_user' => wp_get_current_user(),
						'data'         => $request,
					)
				);
			}
		}
		if ( ! is_wp_error( $response ) ) {
			$response->set_data(
				array(
					'status'  => 'success',
					'message' => 'Yakoke!',
					'data'    => get_post_meta( $this->page->ID, $this->meta_key, true ),
				)
			);
		}
		return $response;
	}

	/**
	 * Get all items from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	public function get_data( WP_REST_Request $request ) {
		$response = new WP_REST_Response( null, 200, array( 'Content-Type' => 'application/json' ) );
		if ( ! $this->page_id ) {
			return new WP_Error(
				'page_not_found',
				'Page Not Found! Please select a page via the plugin settings.',
				array(
					'status' => 500,
					'data'   => $request,
				)
			);
		}
		$data = get_post_meta( $this->page_id, $this->meta_key, true );
		if ( false === $data ) {
			$message = 'No data found! Maybe send some along?';
			if ( ! $this->page_id ) {
				$message = ( 'The page to display the data hasn\'t been set up! Please check with the Marketing Web Team.' );
			}
			$response = new WP_Error(
				'no_data_found',
				$message,
				array(
					'status' => 500,
				)
			);
		}
		if ( ! is_wp_error( $response ) ) {
			$response->set_data(
				array(
					'status'  => 'success',
					'message' => 'Halito! Here is the data we have at the moment',
					'data'    => $data,
				)
			);
		}
		return $response;
	}
}
