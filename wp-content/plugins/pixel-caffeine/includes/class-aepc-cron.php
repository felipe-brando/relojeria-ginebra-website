<?php
/**
 * Support class for the cron jobs
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Model\Job;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Support class for the cron jobs
 *
 * @class AEPC_Cron
 */
class AEPC_Cron {

	/**
	 * The jobs list
	 *
	 * @var Job[]
	 */
	public static $jobs = array();

	/**
	 * Register the job instances
	 *
	 * @return void
	 */
	protected static function bootstrap_jobs() {
		self::$jobs = array(
			new \PixelCaffeine\Job\RefreshAudiencesSize(),
			new \PixelCaffeine\ProductCatalog\Cron\RefreshFeed(),
		);
	}

	/**
	 * AEPC_Cron Constructor.
	 *
	 * @return void
	 */
	public static function init() {
		self::bootstrap_jobs();

		foreach ( self::$jobs as $job ) {
			$job->init();
		}
	}

}
