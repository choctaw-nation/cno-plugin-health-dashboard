<?php
/**
 * Notifier class for sending notifications to the admin when there is an error with the health data.
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

namespace ChoctawNation\HealthDashboard\WP;

/**
 * Class Notifier
 */
class Notifier {
	/**
	 * The email(s) to send notifications to
	 *
	 * @var string[] $emails
	 */
	private array $emails;

	/**
	 * Notifier constructor.
	 *
	 * @param string|string[] $emails The email(s) to send notifications to
	 */
	public function __construct( string|array $emails = array() ) {
		if ( is_string( $emails ) ) {
			$emails = array( $emails );
		}
		$this->emails = array_unique( array( ...$emails, get_option( 'admin_email' ) ) );
	}

	/**
	 * Sends a notification to the admin email(s)
	 *
	 * @param string $message The message to send
	 */
	public function send_notification( string $message ) {
		foreach ( $this->emails as $email ) {
			wp_mail( $email, 'Health Data Dashboard Error', $message );
		}
	}
}
