<?php
/**
 * Support class for the cron jobs
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Model;

/**
 * Class Job
 *
 * @package PixelCaffeine\Model
 */
abstract class Job {

	/**
	 * Setup the job tasks and eventually recurrences
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'cron_schedules', array( $this, 'recurrences' ) );
		add_action( 'init', array( $this, 'register_events' ) );
	}

	/**
	 * Register the recurrences in the cron_schedules hook
	 *
	 * @param array $recurrences The recurrences.
	 *
	 * @return array {
	 *     @type int $interval
	 *     @type string $display
	 * }
	 */
	public function recurrences( $recurrences ) {
		return $recurrences;
	}

	/**
	 * Register the events
	 *
	 * @return void
	 */
	public function register_events() {
		$recurrences = wp_get_schedules();

		foreach ( $this->tasks() as $interval => $job ) {
			if ( ! wp_next_scheduled( $job['hook'], $job['callback_args'] ) ) {
				wp_schedule_event( time() + $recurrences[ $interval ]['interval'], $interval, $job['hook'], $job['callback_args'] );
			}

			add_action( $job['hook'], $job['callback'] );
		}
	}

	/**
	 * Unschedule the job
	 *
	 * @param string $hook Parameter passed from the scheduled event.
	 *
	 * @return void
	 */
	public function unschedule( $hook = '' ) {
		foreach ( $this->tasks() as $interval => $job ) {
			$timestamp = wp_next_scheduled( $job['hook'], $job['callback_args'] );
			if ( false !== $timestamp ) {
				wp_unschedule_event( $timestamp, $job['hook'], $job['callback_args'] );
			}
		}
	}

	/**
	 * Register the tasks
	 *
	 * @return mixed
	 */
	abstract public function tasks();

}
