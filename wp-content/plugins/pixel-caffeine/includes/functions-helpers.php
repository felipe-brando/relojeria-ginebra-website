<?php
/**
 * Collection of helper functions
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Print the track code for a specific event
 *
 * @param string|array|object $args List of arguments of the event.
 *
 * @return string|null
 */
function aepc_pixel_event_code( $args = array() ) {
	$defaults = array(
		'event_id'         => '',
		'event_name'       => '',
		'value'            => '',
		'currency'         => '',
		'content_name'     => '',
		'content_category' => '',
		'content_ids'      => array(),
		'content_type'     => '',
		'num_items'        => '',
		'search_string'    => '',
		'status'           => '',
		'return'           => false,
	);

	// Set arguments.
	$args            = wp_parse_args( $args, $defaults );
	$standard_params = $args;

	// Set standard parameters.
	$standard_params = array_intersect_key( $standard_params, $defaults );

	// Get custom parameters.
	$custom_params = array_diff_key( $args, $standard_params );

	// Set standard parameters.
	foreach ( array( 'event_name', 'return' ) as $key ) {
		unset( $standard_params[ $key ], $custom_params[ $key ] );
	}

	// Get the event ID.
	if ( ! empty( $args['event_id'] ) ) {
		$standard_params['event_id'] = $args['event_id'];
	}

	// Get track code.
	$code = AEPC_Track::track( $args['event_name'], array_filter( $standard_params ), array_filter( $custom_params ) );

	// If option is on footer, must be returned and not printed.
	if ( 'footer' === get_option( 'aepc_pixel_position' ) ) {
		return null;
	}

	// Otherwise return according to argument.
	if ( ! $args['return'] ) {
		printf( "<script>\n\t%s\n</script>", wp_kses_post( $code ) );
	}

	// Anyway, unregister track.
	AEPC_Track::remove_event( $args['event_name'], 'last' );

	return $code;
}
