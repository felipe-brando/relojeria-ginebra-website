<?php
/**
 * Logs repository
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Logs;

use PixelCaffeine\Logs\Entity\Log;
use PixelCaffeine\Logs\Exception\LogNotExistingException;

/**
 * Class LogRepository
 *
 * @package PixelCaffeine\Logs
 */
class LogRepository implements LogRepositoryInterface {

	const DB_TABLE_NAME = 'aepc_logs';

	/**
	 * Save the object into the DB, updating it if the $log has an ID set
	 *
	 * @param Log $log The log entity instance to save.
	 *
	 * @throws LogNotExistingException When the log entity not existing if trying to update.
	 *
	 * @return void
	 */
	public function save( Log &$log ) {
		if ( $log->get_id() ) {
			$this->update( $log );
		} else {
			$this->persist( $log );
		}
	}

	/**
	 * Persists new Log object into the DB
	 *
	 * @param Log $log The log entity instance to save.
	 *
	 * @return void
	 */
	protected function persist( Log &$log ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . self::DB_TABLE_NAME,
			array(
				'exception' => $log->get_exception(),
				'message'   => $log->get_message(),
				'date'      => $log->get_date()->format( \DateTime::ISO8601 ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
				'context'   => serialize( $log->get_context() ),
			)
		);
		$log->set_id( $wpdb->insert_id );
	}

	/**
	 * Update a Log object into the DB
	 *
	 * @param Log $log The log entity instance to save.
	 *
	 * @throws LogNotExistingException When the log entity not existing if trying to update.
	 *
	 * @return void
	 */
	protected function update( Log &$log ) {
		if ( ! $this->find_by_id( $log->get_id() ) ) {
			throw new LogNotExistingException();
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->prefix . self::DB_TABLE_NAME,
			array(
				'exception' => $log->get_exception(),
				'message'   => $log->get_message(),
				'date'      => $log->get_date()->format( \DateTime::ISO8601 ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
				'context'   => serialize( $log->get_context() ),
			),
			array( 'ID' => $log->get_id() )
		);
	}

	/**
	 * Remove an Log from the DB
	 *
	 * @param int $log_id The log entity ID to remove.
	 *
	 * @throws LogNotExistingException When the log entity not existing.
	 *
	 * @return void
	 */
	public function remove( $log_id ) {
		if ( ! $this->find_by_id( $log_id ) ) {
			throw new LogNotExistingException();
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( $wpdb->prefix . self::DB_TABLE_NAME, array( 'ID' => $log_id ) );
	}

	/**
	 * Remove all logs from the DB
	 *
	 * @return void
	 */
	public function removeAll() {
		$logs = $this->find_all();
		foreach ( array_filter( (array) $logs ) as $log ) {
			$this->remove( $log->get_id() );
		}
	}

	/**
	 * Get the criteria SQL clauses
	 *
	 * @param array $criteria The list of criteria.
	 *
	 * @return string
	 */
	protected function getCriteriaSql( array $criteria ) {
		$sql = '';

		if ( $criteria ) {
			global $wpdb;
			foreach ( $criteria as $field => &$value ) {
				if ( is_int( $value ) ) {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$value = $wpdb->prepare( "`{$field}` = %d", $value );
				} else {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$value = $wpdb->prepare( "`{$field}` = %s", $value );
				}
			}

			$sql .= sprintf( ' WHERE %s', implode( ' AND ', array_values( $criteria ) ) );
		}

		return $sql;
	}

	/**
	 * Find by a field defined in $criteria
	 *
	 * @param array    $criteria The list of criteria.
	 * @param array    $order_by The fields for which order by.
	 * @param int|null $limit The limit.
	 * @param int|null $offset The offset.
	 *
	 * @return bool|Log|Log[]
	 * @throws \Exception When query fails.
	 */
	protected function findBy( array $criteria, array $order_by = null, $limit = null, $offset = null ) {
		global $wpdb;

		$logs       = array();
		$table_name = $wpdb->prefix . self::DB_TABLE_NAME;
		$sql        = "SELECT * FROM {$table_name}" . $this->getCriteriaSql( $criteria );

		if ( $order_by ) {
			foreach ( $order_by as $field => &$order ) {
				$order = "{$field} {$order}";
			}

			$sql .= sprintf( ' ORDER BY %s', implode( ', ', array_values( $order_by ) ) );
		}

		if ( $limit ) {
			$limit_sql = " LIMIT {$limit}";
			if ( $offset ) {
				$limit_sql .= " OFFSET {$offset}";
			}
			$sql .= $limit_sql;
		}

		// phpcs:ignore WordPress.DB
		$raw_logs = $wpdb->get_results( $sql );

		if ( empty( $raw_logs ) ) {
			return false;
		}

		foreach ( $raw_logs as $raw_log ) {
			$log = new Log(
				$raw_log->exception,
				$raw_log->message,
				new \DateTime( $raw_log->date ),
				maybe_unserialize( $raw_log->context )
			);
			$log->set_id( $raw_log->ID );

			$logs[] = $log;
		}

		return 1 === $limit ? $logs[0] : $logs;
	}

	/**
	 * Find a Log by the ID
	 *
	 * @param string|int $id The log ID.
	 *
	 * @return Log|false
	 * @throws \Exception When query fails.
	 */
	public function find_by_id( $id ) {
		$result = $this->findBy( array( 'ID' => $id ), array(), 1 );
		return $result instanceof Log ? $result : false;
	}

	/**
	 * Find all Logs for the page specified
	 *
	 * @param array    $order_by The fields for which order by.
	 * @param int|null $limit The limit.
	 * @param int|null $offset The offset.
	 *
	 * @return Log[]
	 * @throws \Exception When query fails.
	 */
	public function find_all( array $order_by = null, $limit = null, $offset = null ) {
		$result = $this->findBy( array(), $order_by, $limit, $offset );
		return is_array( $result ) ? $result : array();
	}

	/**
	 * Find all Logs filtered by Exception for the page specified
	 *
	 * @param string   $exception The exception to find.
	 * @param array    $order_by The fields for which order by.
	 * @param int|null $limit The limit.
	 * @param int|null $offset The offset.
	 *
	 * @return Log[]
	 * @throws \Exception When query fails.
	 */
	public function find_by_exception( $exception, array $order_by = null, $limit = null, $offset = null ) {
		$result = $this->findBy( array( 'exception' => $exception ), $order_by, $limit, $offset );
		return is_array( $result ) ? $result : array();
	}

	/**
	 * Return the count of all logs saved
	 *
	 * @param array $criteria The query criteria.
	 *
	 * @return int
	 */
	protected function getCount( array $criteria ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::DB_TABLE_NAME;
		$sql        = "SELECT COUNT(*) FROM {$table_name}" . $this->getCriteriaSql( $criteria );

		// phpcs:ignore WordPress.DB
		return intval( $wpdb->get_var( esc_sql( $sql ) ) );
	}

	/**
	 * Return the count of all logs saved
	 *
	 * @return int
	 */
	public function get_count_all() {
		return $this->getCount( array() );
	}
}
