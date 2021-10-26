<?php
/**
 * The product catalog entity class
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog\Entity;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The product catalog entity class
 *
 * @class ProductCatalog
 */
class ProductCatalog {

	/**
	 * The product catalog ID
	 *
	 * @var string
	 */
	private $id;

	/**
	 * The product catalog format
	 *
	 * @var string
	 */
	private $format;

	/**
	 * The product catalog configuration
	 *
	 * @var array
	 */
	private $config;

	/**
	 * How many products are in the product catalog
	 *
	 * @var int
	 */
	private $products_count;

	/**
	 * The last update date time
	 *
	 * @var \DateTime
	 */
	private $last_update_date;

	/**
	 * The last error message of the product catalog
	 *
	 * @var string
	 */
	private $last_error_message;

	/**
	 * Get the product catalog ID
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the product catalog ID
	 *
	 * @param string $id The product catalog ID.
	 *
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the product catalog format
	 *
	 * @return string
	 */
	public function get_format() {
		return $this->format;
	}

	/**
	 * Set the product catalog format
	 *
	 * @param string $format The product catalog format.
	 *
	 * @return void
	 */
	public function set_format( $format ) {
		$this->format = $format;
	}

	/**
	 * Get the product catalog configuration
	 *
	 * @return array
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Set the product catalog configuration
	 *
	 * @param array $config The product catalog configuration.
	 *
	 * @return void
	 */
	public function set_config( $config ) {
		$this->config = $config;
	}

	/**
	 * Get how many products are inside the product catalog
	 *
	 * @return int
	 */
	public function get_products_count() {
		return $this->products_count;
	}

	/**
	 * Set how many products are inside the product catalog
	 *
	 * @param int $products_count The products count inside the product catalog.
	 *
	 * @return void
	 */
	public function set_products_count( $products_count ) {
		$this->products_count = $products_count;
	}

	/**
	 * Increment the products counter
	 *
	 * @param int $count How many products count to add in the total counter.
	 *
	 * @return void
	 */
	public function increment_products_counter( $count ) {
		$this->products_count += $count;
	}

	/**
	 * Get the last update date time
	 *
	 * @return \DateTime
	 */
	public function get_last_update_date() {
		return $this->last_update_date;
	}

	/**
	 * Set the last update date time
	 *
	 * @param \DateTime $last_update_date Last update date time.
	 *
	 * @return void
	 */
	public function set_last_update_date( $last_update_date ) {
		$this->last_update_date = $last_update_date;
	}

	/**
	 * Get the last error message
	 *
	 * @return string
	 */
	public function get_last_error_message() {
		return $this->last_error_message;
	}

	/**
	 * Set the last error message
	 *
	 * @param string $last_error_message Last error message.
	 *
	 * @return void
	 */
	public function set_last_error_message( $last_error_message ) {
		$this->last_error_message = $last_error_message;
	}

	/**
	 * Clear the last error message
	 *
	 * @return void
	 */
	public function clear_last_error_message() {
		$this->last_error_message = '';
	}

}
