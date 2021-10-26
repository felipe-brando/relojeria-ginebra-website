<?php
/**
 * Main instance for the product feed background saver
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

use PixelCaffeine\ProductCatalog\Dictionary\FeedSaver;
use PixelCaffeine\ProductCatalog\Exception\FeedException;

/**
 * Class BackgroundFeedSaver
 *
 * @package PixelCaffeine\ProductCatalog
 */
class BackgroundFeedSaver implements FeedSaverInterface {

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
	 * @return array
	 * @throws FeedException When the background saver fails.
	 */
	public function save( $context ) {
		$background_saver = \AEPC_Admin::$product_catalogs_service->get_background_saver_process();
		$entity           = $this->product_catalog->get_entity();

		$response = $background_saver
			->data(
				array(
					array(
						FeedSaver::CONTEXT_FIELD      => $context,
						FeedSaver::ID_FIELD           => $entity->get_id(),
						FeedSaver::PREV_COUNTER_FIELD => $entity->get_products_count(),
					),
				)
			)
			->save()
			->dispatch();

		if ( is_wp_error( $response ) ) {
			throw FeedException::feedCannotBeSaved( $response );
		}

		return $response;
	}

}
