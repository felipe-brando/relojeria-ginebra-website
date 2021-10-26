<?php
/**
 * The response instance
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AEPC_Admin_Response
 *
 * @package PixelCaffeine\Admin
 */
class AEPC_Admin_Response {

	/**
	 * If response is successful
	 *
	 * @var bool
	 */
	protected $success;

	/**
	 * The response data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Response constructor.
	 *
	 * @param bool  $success If response is successful.
	 * @param array $data The response data.
	 */
	public function __construct( $success, $data = array() ) {
		$this->success = $success;
		$this->data    = $data;
	}

	/**
	 * If the response is successful
	 *
	 * @return bool
	 */
	public function is_success() {
		return $this->success;
	}

	/**
	 * Set if the response is successful
	 *
	 * @param bool $success If response is successful.
	 *
	 * @return void
	 */
	public function set_success( $success ) {
		$this->success = $success;
	}

	/**
	 * Get the response data
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Set the response data
	 *
	 * @param array $data The response data.
	 *
	 * @return void
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * Return a key value of data collection
	 *
	 * @param string $key The key of the value inside the response data.
	 * @param mixed  $default The default value if not key in the response data.
	 *
	 * @return mixed
	 */
	public function get( $key, $default = false ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
	}
}
