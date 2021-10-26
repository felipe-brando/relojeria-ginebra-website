<?php
/**
 * Contract for the errors log repository
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Logs;

use PixelCaffeine\Logs\Entity\Log;

interface LogRepositoryInterface {

	/**
	 * Save the object into the DB, updating it if the $log has an ID set
	 *
	 * @param Log $log The log entity instance.
	 *
	 * @return void
	 */
	public function save( Log &$log );

	/**
	 * Remove an Log from the DB
	 *
	 * @param int $log_id The log ID.
	 *
	 * @return void
	 */
	public function remove( $log_id );

	/**
	 * Find a Log by the ID
	 *
	 * @param int $id The log ID.
	 *
	 * @return Log
	 */
	public function find_by_id( $id );

	/**
	 * Find all Logs for the page specified
	 *
	 * @param array $order_by The fields for which order by.
	 * @param null  $limit The limit.
	 * @param null  $offset The offset.
	 *
	 * @return Log[]
	 */
	public function find_all( array $order_by = null, $limit = null, $offset = null );

	/**
	 * Find all Logs filtered by Exception for the page specified
	 *
	 * @param string $exception The exception to find.
	 * @param array  $order_by The fields for which order by.
	 * @param null   $limit The limit.
	 * @param null   $offset The offset.
	 *
	 * @return Log[]
	 */
	public function find_by_exception( $exception, array $order_by = null, $limit = null, $offset = null );

	/**
	 * Return the count of all logs saved
	 *
	 * @return int
	 */
	public function get_count_all();

}
