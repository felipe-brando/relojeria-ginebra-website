<?php
/**
 * Support class for the foreground feed saver
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

/**
 * Class ForegroundFeedSaver
 *
 * @package PixelCaffeine\ProductCatalog
 */
class ForegroundFeedSaver implements FeedSaverInterface {

	/**
	 * The product catalog manager instance
	 *
	 * @var ProductCatalogManager
	 */
	protected $product_catalog;

	/**
	 * BackgroundFeedSaver constructor.
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 */
	public function __construct( ProductCatalogManager $product_catalog ) {
		$this->product_catalog = $product_catalog;
	}

	/**
	 * Run the save process of the feed
	 *
	 * @param string $context The context.
	 *
	 * @return mixed
	 * @throws \Exception Then saving fails.
	 */
	public function save( $context ) {
		$entity       = $this->product_catalog->get_entity();
		$prev_counter = $entity->get_products_count();

		try {
			$this->product_catalog->get_feed_writer()->upload_start( $context );

			// Save.
			do {
				$this->product_catalog->get_feed_writer()->save_chunk( $context );
			} while ( $this->product_catalog->there_are_items_to_save() );

			// Success.
			$this->product_catalog->get_feed_writer()->upload_success( $context );
			$this->product_catalog->get_entity()->clear_last_error_message();
			$this->product_catalog->update();

			if ( ! $this->product_catalog->get_feed_directory_helper()->is_feed_existing() ) {
				\AEPC_Admin::$logger->log(
					'Saving process complete successfully but the file is not created in the filesystem.',
					array(
						'exception' => 'FeedCreationException',
					)
				);
			}

			return true;
		} catch ( \Exception $e ) {
			$this->product_catalog->get_feed_writer()->upload_failure( $context );
			$this->product_catalog->get_entity()->set_last_error_message( $e->getMessage() );
			$this->product_catalog->get_entity()->set_products_count( $prev_counter );
			$this->product_catalog->update();

			throw $e;
		}
	}

}
