<?php
/**
 * Pixel Event entity
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ServerSide;

use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\UserData;

/**
 * Class Pixel_Event
 *
 * @package PixelCaffeine\ServerSide
 */
class Pixel_Event {

	const TYPE_BROWSER = 'browser';
	const TYPE_SERVER  = 'server';

	/**
	 * The event ID
	 *
	 * @var null|string
	 */
	protected $event_id = null;

	/**
	 * The event name
	 *
	 * @var string
	 */
	protected $event_name;

	/**
	 * The key=>value list of parameters
	 *
	 * @var array
	 */
	protected $event_data = array();

	/**
	 * The user data attached to this event
	 *
	 * @var UserData
	 */
	protected $user_data;

	/**
	 * If the event must be fired with a delay (only applicable for browser side events)
	 *
	 * @var int
	 */
	protected $delay;

	/**
	 * If the event must be unique (so it saves a transient for that event ID)
	 *
	 * @var bool
	 */
	protected $unique = false;

	/**
	 * Pixel_Event constructor.
	 *
	 * @param string $event_name The event name.
	 */
	public function __construct( $event_name ) {
		$this->event_name = $event_name;
	}

	/**
	 * Get the event id
	 *
	 * @return string|null
	 */
	public function get_event_id() {
		return $this->event_id;
	}

	/**
	 * Set the event ID
	 *
	 * @param string $event_id The event ID to set.
	 *
	 * @return Pixel_Event
	 */
	public function set_event_id( $event_id ) {
		$this->event_id = $event_id;

		return $this;
	}

	/**
	 * Get the event name
	 *
	 * @return string
	 */
	public function get_event_name() {
		return $this->event_name;
	}

	/**
	 * Get the event data
	 *
	 * @return array
	 */
	public function get_event_data() {
		return $this->event_data;
	}

	/**
	 * Set the event data
	 *
	 * @param array $event_data The key=>value list of parameters.
	 *
	 * @return Pixel_Event
	 */
	public function set_event_data( $event_data ) {
		$this->event_data = $event_data;

		return $this;
	}

	/**
	 * Get the UserData attached to this event
	 *
	 * @return UserData
	 */
	public function get_user_data() {
		return $this->user_data;
	}

	/**
	 * Attach user data to this event
	 *
	 * @param UserData $user_data The UserData instance.
	 *
	 * @return Pixel_Event
	 */
	public function set_user_data( $user_data ) {
		$this->user_data = $user_data;

		return $this;
	}

	/**
	 * Get the event delay
	 *
	 * @return int
	 */
	public function get_delay() {
		return $this->delay;
	}

	/**
	 * Set the event delay
	 *
	 * @param int $delay  Event delay (only applicable for browser side events).
	 *
	 * @return Pixel_Event
	 */
	public function set_delay( $delay ) {
		$this->delay = $delay;

		return $this;
	}

	/**
	 * Set if the pixel must be tracked only once
	 *
	 * @param bool $unique True = must be unique.
	 *
	 * @return $this
	 */
	public function must_be_unique( $unique ) {
		$this->unique = $unique;

		return $this;
	}

	/**
	 * Get if the pixel must be tracked only once
	 *
	 * @return bool
	 */
	public function is_unique() {
		return $this->unique;
	}

	/**
	 * Check if the pixel can be fired (eg. if it's unique, it checks if it's already tracked)
	 *
	 * @param string $type 'browser' or 'server'.
	 *
	 * @return bool
	 */
	public function can_be_tracked( $type ) {
		return ! $this->unique || ! get_transient( sprintf( 'aepc_event_%s_%s', strtolower( $type ), $this->event_id ) );
	}

	/**
	 * Set this pixel as tracked (in order to check if it must be tracked again then)
	 *
	 * @param string $type 'browser' or 'server'.
	 *
	 * @return void
	 */
	public function set_as_tracked( $type ) {
		$this->unique && set_transient( sprintf( 'aepc_event_%s_%s', strtolower( $type ), $this->event_id ), true, apply_filters( 'aepc_unique_event_expire', 2 * HOUR_IN_SECONDS ) );
	}

	/**
	 * Get track code formatted
	 *
	 * @return string
	 */
	public function get_track_code() {
		$args       = sprintf( 'aepc_extend_args(%s)', wp_json_encode( (object) $this->get_event_data(), JSON_PRETTY_PRINT ) ?: '{}' );
		$event_id   = $this->get_event_id();
		$track_name = $this->get_event_name();
		$track_type = \AEPC_Track::get_track_type( $this->get_event_name() );

		return 'fbq(' . implode(
			', ',
			array_filter(
				array(
					"'{$track_type}'",
					"'{$track_name}'",
					$args,
					$event_id ? wp_json_encode( array( 'eventID' => $event_id ), JSON_PRETTY_PRINT | JSON_FORCE_OBJECT ) : false,
				)
			)
		) . ');';
	}

	/**
	 * Get track URL
	 *
	 * @return string
	 */
	public function get_track_url() {
		$args       = $this->get_event_data();
		$track_name = $this->get_event_name();
		$cd         = '';

		// Structure the arguments list.
		if ( ! empty( $args ) ) {
			foreach ( $args as $key => &$value ) {
				if ( is_array( $value ) ) {
					$value = wp_json_encode( $value ) ?: '{}';
				}

				$value = "cd[{$key}]={$value}";
			}

			$cd = '&' . implode( '&', $args );
		}

		return sprintf( 'https://www.facebook.com/tr?id=%1$s&ev=%2$s%3$s&noscript=1', PixelCaffeine()->get_pixel_id(), $track_name, $cd );
	}

	/**
	 * Allow to get an array format for client side
	 *
	 * @return array
	 */
	public function to_array() {
		return apply_filters(
			'aepc_track_event_data',
			array_filter(
				array(
					'params'   => $this->get_event_data() ?: array(),
					'delay'    => $this->get_delay(),
					'event_id' => $this->get_event_id(),
				)
			),
			$this->get_event_name()
		);
	}

}
