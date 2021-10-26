<?php
/**
 * Register the logs for errors and warnings
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Logs\Entity\Log;
use PixelCaffeine\Logs\LogRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main logger class
 *
 * @class AEPC_Admin_Logger
 */
class AEPC_Admin_Logger {

	/**
	 * Setup the logger
	 *
	 * @return void
	 */
	public function setup() {

	}

	/**
	 * Log the message
	 *
	 * The message MAY contain placeholders in the form: {foo} where foo
	 * will be replaced by the context data in key "foo".
	 *
	 * The context array can contain arbitrary data. The only assumption that
	 * can be made by implementors is that if an Exception instance is given
	 * to produce a stack trace, it MUST be in a key named "exception".
	 *
	 * @param string $message The message.
	 * @param array  $context The context of the error.
	 *
	 * @throws Exception Throws a general error when there is a problem during the log saving.
	 *
	 * @return void
	 */
	public function log( $message, array $context = array() ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$exception = $context['exception'];
		unset( $context['exception'] );

		$log = new Log(
			$exception,
			$message,
			new \DateTime(),
			$context
		);

		$repository = new LogRepository();
		$repository->save( $log );
	}

}
