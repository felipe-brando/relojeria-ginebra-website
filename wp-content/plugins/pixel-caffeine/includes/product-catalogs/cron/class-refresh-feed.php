<?php
/**
 * Refresh feed job
 *
 * @package Pixel caffeine
 */

namespace PixelCaffeine\ProductCatalog\Cron;

use PixelCaffeine\Model\Job;
use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\Dictionary\FeedSaver;
use PixelCaffeine\ProductCatalog\Exception\FeedException;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

/**
 * Class RefreshFeed
 *
 * @package PixelCaffeine\ProductCatalog\Cron
 */
class RefreshFeed extends Job {

	const HOOK_NAME = 'aepc_refresh_product_feed';

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
		$product_catalogs = \AEPC_Admin::$product_catalogs_service->get_product_catalogs();

		foreach ( $product_catalogs as $product_catalog ) {
			$recurrence_id = $this->get_recurrence_id( $product_catalog );
			if ( isset( $recurrences[ $recurrence_id ] ) ) {
				continue;
			}

			$cycle                         = $product_catalog->configuration()->get( Configuration::OPTION_REFRESH_CYCLE );
			$cycle_type                    = $product_catalog->configuration()->get( Configuration::OPTION_REFRESH_CYCLE_TYPE );
			$recurrences[ $recurrence_id ] = array(
				'interval' => $cycle * constant( strtoupper( $cycle_type ) . '_IN_SECONDS' ),
				/* translators: 1: the cycle (number), 2: the type of cycle (month, year, etc.) */
				'display'  => sprintf( __( 'Feed Refresh Every %1$s %2$s', 'pixel-caffeine' ), $cycle, $cycle_type ),
			);
		}

		return $recurrences;
	}

	/**
	 * The task of the job
	 *
	 * @return array|mixed
	 */
	public function tasks() {
		$tasks            = array();
		$product_catalogs = \AEPC_Admin::$product_catalogs_service->get_product_catalogs();

		foreach ( $product_catalogs as $product_catalog ) {
			$tasks[ $this->get_recurrence_id( $product_catalog ) ] = array(
				'hook'          => self::HOOK_NAME,
				'callback'      => array( $this, 'task' ),
				'callback_args' => array( $product_catalog->get_entity()->get_id() ),
			);
		}

		return $tasks;
	}

	/**
	 * The product catalog refresh task
	 *
	 * @param string $product_catalog_id The product catalog ID.
	 *
	 * @return null
	 */
	public function task( $product_catalog_id ) {
		\AEPC_Admin::init();
		\AEPC_Admin::$api->connect();

		$service         = \AEPC_Admin::$product_catalogs_service;
		$product_catalog = $service->get_product_catalog( $product_catalog_id );

		// Fix plugin compatibilities.
		add_filter( 'option_siteground_optimizer_lazyload_images', '__return_false' );
		add_filter( 'site_option_siteground_optimizer_lazyload_images', '__return_false' );

		// Firstly delete.
		try {
			$service->generate_feed( $product_catalog, FeedSaver::REFRESH_CONTEXT );
			return null;
		} catch ( FeedException $e ) {
			return null;
		}
	}

	/**
	 * Get the recurrence ID for the product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 *
	 * @return string
	 */
	protected function get_recurrence_id( ProductCatalogManager $product_catalog ) {
		$cycle      = $product_catalog->configuration()->get( Configuration::OPTION_REFRESH_CYCLE );
		$cycle_type = $product_catalog->configuration()->get( Configuration::OPTION_REFRESH_CYCLE_TYPE );
		return 'aepc-feed-' . $product_catalog->get_entity()->get_id() . '-' . $cycle . '-' . $cycle_type;
	}

	/**
	 * Unschedule a job for a specific product catalog
	 *
	 * @param string $product_catalog_id The product catalog ID.
	 *
	 * @return void
	 */
	public function unschedule( $product_catalog_id = '' ) {
		if ( ! empty( $product_catalog_id ) ) {
			$timestamp = wp_next_scheduled( self::HOOK_NAME, array( $product_catalog_id ) );
			wp_unschedule_event( $timestamp ?: 0, self::HOOK_NAME, array( $product_catalog_id ) );
		} else {
			parent::unschedule();
		}
	}

}
