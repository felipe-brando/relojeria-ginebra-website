<?php
/**
 * Exception for Facebook API requests
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Admin\Exception;

/**
 * Class FBAPIException
 *
 * @package PixelCaffeine\Admin\Exception
 */
class FBAPIException extends AEPCException {

	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link http://php.net/manual/en/exception.construct.php
	 *
	 * @param string          $message [optional] The Exception message to throw.
	 * @param int|string      $code [optional] The Exception code.
	 * @param array           $request_payload The request info (method, endpoint, arguments).
	 * @param \WP_Error|array $response The error response from the request.
	 * @param \Exception      $previous [optional] The previous throwable used for the exception chaining.
	 *
	 * @since 5.1.0
	 */
	public function __construct( $message = '', $code = 0, $request_payload = array(), $response = null, $previous = null ) {
		// Obscure sensible data.
		if ( isset( $request_payload['endpoint'] ) ) {
			$request_payload['endpoint'] = preg_replace( '/\/([0-9]+)\//', '/<id>/', $request_payload['endpoint'] );
		}

		if ( isset( $request_payload['auth_token'] ) ) {
			$request_payload['auth_token'] = substr_replace(
				$request_payload['auth_token'],
				str_repeat( '*', strlen( $request_payload['auth_token'] ) - 5 ),
				0,
				strlen( $request_payload['auth_token'] ) - 5
			);
		}

		parent::__construct(
			$message,
			$code,
			array(
				'request'  => $request_payload,
				'response' => $response,
			),
			$previous
		);
	}

}
