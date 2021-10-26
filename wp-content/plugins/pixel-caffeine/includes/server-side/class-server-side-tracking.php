<?php
/**
 * Main class for the Conversions API (aka Server Side tracking)
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ServerSide;

use PixelCaffeine\Dependencies\FacebookAds\Api;

/**
 * Class Server_Side_Tracking
 */
final class Server_Side_Tracking {

	/**
	 * The conversions API service
	 *
	 * @var Conversions_API
	 */
	protected static $service;

	/**
	 * The list of events injected must be sent.
	 *
	 * @var Pixel_Event[]
	 */
	protected static $events;

	/**
	 * Init the module
	 *
	 * @param string $access_token The specific access token if the user define it instead of facebook connect.
	 *
	 * @return void
	 */
	public static function init( $access_token = null ) {
		add_action( 'template_redirect', array( __CLASS__, 'send_events' ) );
		add_action( 'shutdown', array( __CLASS__, 'send_events' ) ); // Try another time if any other events will be added.

		// Server-side tracking through AJAX.
		if ( self::must_track_in_ajax() ) {
			add_action(
				'rest_api_init',
				function () {
					register_rest_route(
						'aepc/v1',
						'/fbq',
						array(
							'methods'             => \WP_REST_Server::CREATABLE,
							'callback'            => array( __CLASS__, 'server_side_fbq' ),
							'permission_callback' => '__return_true',
						)
					);
				}
			);
		}
	}

	/**
	 * Setup the main instance
	 *
	 * @return void
	 */
	public static function setup() {
		$access_token = self::get_ss_access_token();

		if ( $access_token ) {
			self::$service = new Conversions_API(
				PixelCaffeine()->get_pixel_id(),
				Api::init( null, null, $access_token, false ), // @phpstan-ignore-line
				new Event_Factory(),
				self::must_log_all_events()
			);
		}
	}

	/**
	 * Register the events from the supported addons
	 *
	 * @param Pixel_Event $event The event data.
	 *
	 * @return void
	 */
	public static function inject_addon_event( Pixel_Event $event ) {
		self::$events[ $event->get_event_id() ] = $event;
	}

	/**
	 * Send the events when the current process is finishing
	 *
	 * @return void
	 */
	public static function send_events() {
		if ( empty( self::$events ) || ( self::must_track_in_ajax() && ! defined( 'REST_REQUEST' ) ) ) {
			return;
		}

		self::setup();

		if ( self::$service instanceof Conversions_API ) {
			self::$service->send_events( array_values( self::$events ) );
			self::$events = array(); // Allow to re-call send_events if any other events will be added.
		}
	}

	/**
	 * Handle the AJAX request to send server-side events
	 *
	 * @param \WP_REST_Request $request The AJAX request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function server_side_fbq( \WP_REST_Request $request ) {
		$query = $request->get_param( 'query' );

		if ( ! isset( $query[1], $query[3]['eventID'] ) ) {
			return new \WP_Error( 'fbq_unexpected_query', 'Invalid query for fbq server-side', array( 'status' => 422 ) );
		}

		\AEPC_Track::track(
			$query[1],
			array_merge(
				isset( $query[2] ) ? $query[2] : array(),
				array(
					'event_id' => $query[3]['eventID'],
				)
			)
		);

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Get the server side access token (if defined)
	 *
	 * @return string
	 */
	public static function get_ss_access_token() {
		return get_option( 'aepc_server_side_access_token' );
	}

	/**
	 * Check if the user wants to log all events
	 *
	 * @return bool
	 */
	public static function must_log_all_events() {
		return 'yes' === get_option( 'aepc_server_side_log_events' );
	}

	/**
	 * Check if the server-side events must be track through AJAX (fixes cache plugin conflicts)
	 *
	 * @return bool
	 */
	public static function must_track_in_ajax() {
		return 'yes' === get_option( 'aepc_server_side_track_in_ajax' );
	}
}
