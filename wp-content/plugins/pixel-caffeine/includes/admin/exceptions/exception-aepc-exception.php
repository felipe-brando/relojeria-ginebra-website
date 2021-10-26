<?php
/**
 * General exception of Pixel Caffeine
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Admin\Exception;

/**
 * Class AEPCException
 *
 * @package PixelCaffeine\Admin\Exception
 */
class AEPCException extends \Exception {

	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link http://php.net/manual/en/exception.construct.php
	 *
	 * @param string     $message [optional] The Exception message to throw.
	 * @param int|string $code [optional] The Exception code.
	 * @param array      $context The context.
	 * @param \Exception $previous [optional] The previous throwable used for the exception chaining.
	 *
	 * @since 5.1.0
	 * @throws \Exception When error during register log.
	 */
	public function __construct( $message = '', $code = 0, $context = array(), $previous = null ) {
		\AEPC_Admin::$logger->log(
			$message,
			array_merge(
				array(
					'code'      => $code,
					'exception' => get_class( $this ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'$_REQUEST' => ! empty( $_REQUEST ) ? $_REQUEST : array(),
				),
				$context
			)
		);
		parent::__construct( $message, (int) $code, $previous );
	}

}
