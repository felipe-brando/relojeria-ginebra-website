<?php
/**
 * Refresh audience size job
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Job;

use PixelCaffeine\Model\Job;

/**
 * Class RefreshAudiencesSize
 *
 * @package PixelCaffeine\Job
 */
class RefreshAudiencesSize extends Job {

	/**
	 * The list of tasks to run for this job
	 *
	 * @return array[]|mixed
	 */
	public function tasks() {
		$tasks = array(
			'daily' => array(
				'hook'          => 'aepc_refresh_audiences_size',
				'callback'      => array( $this, 'task' ),
				'callback_args' => array(),
			),
		);

		return $tasks;
	}

	/**
	 * The product catalog refresh task
	 *
	 * @return void
	 */
	public function task() {
		\AEPC_Admin::init();
		\AEPC_Admin::$api->connect();

		\AEPC_Admin_CA_Manager::refresh_approximate_counts();
	}

}
