<?php
/**
 * Log Entity
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Logs\Entity;

/**
 * Class Log
 *
 * @package PixelCaffeine\Logs\Entity
 */
class Log {

	/**
	 * The log ID
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * The log description
	 *
	 * @var string
	 */
	protected $exception;

	/**
	 * The log message
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * The log date time
	 *
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * The log context data
	 *
	 * @var array
	 */
	protected $context = array();

	/**
	 * Pass the mandatory arguments
	 *
	 * @param string    $exception The exception name.
	 * @param string    $message The exception message.
	 * @param \DateTime $date The log date.
	 * @param array     $context The context data.
	 */
	public function __construct( $exception, $message, \DateTime $date = null, $context = array() ) {
		$this->exception = $exception;
		$this->message   = $message;
		$this->date      = $date ?: new \DateTime();
		$this->context   = $context;
	}

	/**
	 * Get the log ID
	 *
	 * @return int
	 */
	public function get_id() {
		return (int) $this->id;
	}

	/**
	 * Set the log ID
	 *
	 * @param int $id The log ID.
	 *
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = (int) $id;
	}

	/**
	 * Get the log exception name
	 *
	 * @return string
	 */
	public function get_exception() {
		return $this->exception;
	}

	/**
	 * Set the log exception name
	 *
	 * @param string $exception The exception name.
	 *
	 * @return void
	 */
	public function set_exception( $exception ) {
		$this->exception = $exception;
	}

	/**
	 * Get the log exception message
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Set the log exception message
	 *
	 * @param string $message The exception message.
	 *
	 * @return void
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Get the log date
	 *
	 * @return \DateTime
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * Set the log date
	 *
	 * @param \DateTime $date The log date.
	 *
	 * @return void
	 */
	public function set_date( $date ) {
		$this->date = $date;
	}

	/**
	 * Get the log context data
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Set the log context data
	 *
	 * @param array $context The context data.
	 *
	 * @return void
	 */
	public function set_context( $context ) {
		$this->context = $context;
	}

}
