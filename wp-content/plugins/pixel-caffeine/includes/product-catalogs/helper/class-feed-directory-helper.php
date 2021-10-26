<?php
/**
 * Class to manage the file positions of the feeds
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog\Helper;

use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

/**
 * Class FeedDirectoryHelper
 *
 * Manage the file positions of the feeds
 *
 * @package PixelCaffeine\ProductCatalog\Helper
 */
class FeedDirectoryHelper {

	/**
	 * The product catalog manager instance
	 *
	 * @var ProductCatalogManager
	 */
	private $product_catalog;

	/**
	 * The configuration manager instance
	 *
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * The directory where the feed lives
	 *
	 * @var string
	 */
	private $directory_path;

	/**
	 * The directory where the feed lives
	 *
	 * @var string
	 */
	private $directory_url;

	/**
	 * The file name of the feed
	 *
	 * @var string
	 */
	private $file_name;

	/**
	 * FeedDirectoryHelper constructor.
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 */
	public function __construct( ProductCatalogManager $product_catalog ) {
		$this->product_catalog = $product_catalog;
		$this->configuration   = $this->product_catalog->configuration();

		// Set the default directory path.
		$wp_upload_dir = wp_upload_dir();
		$this->set_directory_path( $this->configuration->get( Configuration::OPTION_DIRECTORY_PATH, $wp_upload_dir['basedir'] . '/product-catalogs' ) );
		$this->set_directory_url( $this->configuration->get( Configuration::OPTION_DIRECTORY_URL, $wp_upload_dir['baseurl'] . '/product-catalogs' ) );
		$this->set_file_name( $this->configuration->get( Configuration::OPTION_FILE_NAME, sprintf( '%s.xml', $this->product_catalog->get_entity()->get_id() ) ) );
	}

	/**
	 * Get the absolute path of the feed of the product catalog
	 *
	 * @return string
	 */
	public function get_feed_path() {
		return untrailingslashit( $this->get_directory_path() ) . '/' . $this->get_file_name();
	}

	/**
	 * Get the absolute path of the feed of the product catalog
	 *
	 * @return string
	 */
	public function get_feed_path_tmp() {
		return untrailingslashit( $this->get_directory_path() ) . '/' . $this->get_file_name_tmp();
	}

	/**
	 * Get the URL of the feed
	 *
	 * @return string
	 */
	public function get_feed_url() {
		return untrailingslashit( $this->get_directory_url() ) . '/' . $this->get_file_name();
	}

	/**
	 * Get the directory where the feeds live
	 *
	 * @return string
	 */
	protected function get_directory_path() {
		return $this->directory_path;
	}

	/**
	 * Set the directory where the feeds will leave
	 *
	 * @param string $directory_path The directory where the feed lives.
	 *
	 * @return void
	 */
	public function set_directory_path( $directory_path ) {
		$this->directory_path = $directory_path;
	}

	/**
	 * Get the directory where the feeds will live
	 *
	 * @return string
	 */
	public function get_directory_url() {
		return $this->directory_url;
	}

	/**
	 * Set the directory where the feeds will live
	 *
	 * @param string $directory_url The directory where the feed lives.
	 *
	 * @return void
	 */
	public function set_directory_url( $directory_url ) {
		$this->directory_url = $directory_url;
	}

	/**
	 * Get the file name of the feed
	 *
	 * @return string
	 */
	protected function get_file_name() {
		return $this->file_name;
	}

	/**
	 * Get the name of the temporary feed file created during save/update
	 *
	 * @return string
	 */
	protected function get_file_name_tmp() {
		return $this->file_name . '.tmp';
	}

	/**
	 * Set the file name
	 *
	 * @param string $file_name The file name.
	 *
	 * @return void
	 */
	public function set_file_name( $file_name ) {
		$this->file_name = $file_name;
	}

	/**
	 * Get if the feed is existing
	 *
	 * @return bool
	 */
	public function is_feed_existing() {
		return file_exists( $this->get_feed_path() );
	}

}
