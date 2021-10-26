<?php
/**
 * Background process for the product catalog background saver
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

use AEPC_Admin_Notices;
use PixelCaffeine\ProductCatalog\Dictionary\FeedSaver;

/**
 * Class BackgroundFeedSaverProcess
 *
 * @package PixelCaffeine\ProductCatalog
 */
class BackgroundFeedSaverProcess extends \WP_Background_Process {

	/**
	 * Action
	 *
	 * (default value: 'background_process')
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'product_catalog_saving';

	/**
	 * The current item info
	 *
	 * @var array
	 */
	protected $current_item;

	/**
	 * The product catalog manager
	 *
	 * @var ProductCatalogManager
	 */
	protected $product_catalog;

	/**
	 * Save the exception in cache for passing through the processes
	 *
	 * @var \Exception|null
	 */
	protected $exception;

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param array $product_catalog {
	 *     The product catalog to generate.
	 *
	 *     @type string $id The name of product catalog
	 *     @type string $mode One of 'start' or 'continue'
	 * }
	 *
	 * @return mixed
	 *
	 * @throws Exception\EntityException When update fail.
	 */
	protected function task( $product_catalog ) {
		try {
			$this->current_item = wp_parse_args(
				$product_catalog,
				array(
					FeedSaver::MODE_FIELD         => FeedSaver::START_MODE,
					FeedSaver::CONTEXT_FIELD      => FeedSaver::NEW_CONTEXT,
					FeedSaver::PREV_COUNTER_FIELD => 0,
				)
			);

			$product_catalog_id = $this->current_item[ FeedSaver::ID_FIELD ];
			$context            = $this->current_item[ FeedSaver::CONTEXT_FIELD ];

			$service               = \AEPC_Admin::$product_catalogs_service;
			$this->product_catalog = $service->get_product_catalog( $product_catalog_id );

			// If we are in the first step, launch the starting method.
			if ( FeedSaver::START_MODE === $this->current_item[ FeedSaver::MODE_FIELD ] ) {
				$this->product_catalog->get_feed_writer()->upload_start( $context );
			}

			// Save.
			$this->product_catalog->get_feed_writer()->save_chunk( $context );

			// Restart again with new chunk if any.
			if ( $this->product_catalog->there_are_items_to_save() ) {
				$this->push_to_queue(
					array_merge(
						$this->current_item,
						array(
							FeedSaver::MODE_FIELD => FeedSaver::CONTINUE_MODE,
						)
					)
				);
				$this->save();
			}
		} catch ( \Exception $e ) {
			$this->exception = $e;
			$this->product_catalog->get_entity()->set_last_error_message( $this->exception->getMessage() );
			$this->product_catalog->get_entity()->set_products_count( $this->current_item[ FeedSaver::PREV_COUNTER_FIELD ] );
			$this->product_catalog->update();
			AEPC_Admin_Notices::add_notice( 'error', 'main', $this->exception->getMessage() );
		}

		return false;
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 *
	 * @return void
	 */
	protected function complete() {
		$context = $this->current_item[ FeedSaver::CONTEXT_FIELD ];

		if ( $this->exception ) {
			\AEPC_Admin::$logger->log(
				$this->exception->getMessage(),
				array(
					'code'         => $this->exception->getCode(),
					'exception'    => get_class( $this->exception ),
					'current_item' => $this->current_item,
				)
			);

			$this->product_catalog->get_feed_writer()->upload_failure( $context );
		} else {
			$this->product_catalog->get_feed_writer()->upload_success( $context );
			$this->product_catalog->get_entity()->clear_last_error_message();
			$this->product_catalog->update();
			AEPC_Admin_Notices::add_notice(
				'success',
				'main',
				/* translators: %s: the URL to the xml product feed. */
				make_clickable( sprintf( __( 'The Product Catalog Feed is saved. This is the URL: %s', 'pixel-caffeine' ), $this->product_catalog->get_url() ) )
			);

			if ( ! $this->product_catalog->get_feed_directory_helper()->is_feed_existing() ) {
				\AEPC_Admin::$logger->log(
					'Saving process complete successfully but the file is not created in the filesystem.',
					array(
						'exception' => 'FeedCreationException',
					)
				);
			}
		}

		parent::complete();
	}

	/**
	 * Is the updater running?
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager.
	 *
	 * @return bool
	 * @throws Exception\FeedException When process fails.
	 */
	public function is_updating( ProductCatalogManager $product_catalog ) {
		return $product_catalog->get_feed_writer()->is_saving();
	}

	/**
	 * Dispatch
	 *
	 * @return array|\WP_Error
	 * @phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
	 */
	public function dispatch() {
		return parent::dispatch(); // @phpstan-ignore-line
	}

}
