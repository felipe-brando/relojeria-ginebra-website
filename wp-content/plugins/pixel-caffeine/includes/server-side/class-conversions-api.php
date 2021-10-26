<?php
/**
 * Main instance of conversions API
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ServerSide;

use PixelCaffeine\Dependencies\FacebookAds\Api;
use PixelCaffeine\Dependencies\FacebookAds\Exception\Exception as FacebookException;
use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\Event;
use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\EventRequest;

/**
 * Class Conversions_API
 *
 * @package PixelCaffeine\ServerSide
 */
class Conversions_API {

	/**
	 * The SDK instance
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Event factory instance
	 *
	 * @var Event_Factory
	 */
	protected $event_factory;

	/**
	 * The facebook Pixel ID
	 *
	 * @var string
	 */
	protected $pixel_id;

	/**
	 * True if we need to log every events sent (and not only the failed one).
	 *
	 * @var bool
	 */
	protected $log_events;

	/**
	 * Conversions_API constructor.
	 *
	 * @param string        $pixel_id The Pixel ID.
	 * @param Api           $api The SDK instance.
	 * @param Event_Factory $event_factory The event factory to help for event instance creation.
	 * @param bool          $log_events True if we need to log every events sent (and not only the failed one).
	 */
	public function __construct( $pixel_id, Api $api, Event_Factory $event_factory, $log_events = false ) {
		$this->api           = $api;
		$this->event_factory = $event_factory;
		$this->pixel_id      = $pixel_id;
		$this->log_events    = $log_events;
	}

	/**
	 * Send all events enqueued in one request
	 *
	 * @param Pixel_Event[] $events List of events must be sent.
	 *
	 * @return void
	 */
	public function send_events( $events ) {
		/**
		 * Facebook SDK instances of the events to sent
		 *
		 * @var Event[] $events
		 */
		$events = array_map( array( $this->event_factory, 'create_event' ), $events );

		$request = ( new EventRequest( $this->pixel_id ) )
			->setEvents( $events ); // @phpstan-ignore-line

		if ( $this->log_events ) {
			foreach ( $events as $event ) {
				$logger = new \AEPC_Admin_Logger();
				$logger->log(
					sprintf( 'Server Side Event success: %s', $event->getEventName() ),
					array(
						'exception' => 'ServerSideEventSent',
						'event'     => $event->normalize(),
					)
				);
			}
		}

		try {
			$response = $request->execute();
		} catch ( FacebookException $e ) {
			$logger = new \AEPC_Admin_Logger();
			$logger->log(
				sprintf( 'Server Side Event error: %s', $e->getMessage() ),
				array(
					'code'      => $e->getCode(),
					'exception' => get_class( $e ),
					'events'    => array_map(
						function( Event $event ) {
							return wp_json_encode( $event->normalize() );
						},
						$events
					),
				)
			);
		}
	}

}
