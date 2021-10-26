<?php
/**
 * Connect to the DB
 *
 * @package Pixel caffeine
 */

namespace PixelCaffeine\ProductCatalog;

use PixelCaffeine\ProductCatalog\Entity\ProductCatalog as Entity;
use PixelCaffeine\ProductCatalog\Exception\EntityException;

/**
 * Class DbProvider
 *
 * @package PixelCaffeine\ProductCatalog
 */
class DbProvider {

	const OPTION_NAME = 'aepc_product_catalogs';

	const FEED_STATUS_SAVED  = 'SAVED';
	const FEED_STATUS_EDITED = 'EDITED';

	const ID_FIELD             = 'id';
	const FORMAT_FIELD         = 'format';
	const CONFIG_FIELD         = 'config';
	const PRODUCTS_COUNT_FIELD = 'products_count';
	const LAST_UPDATE_DATE     = 'last_update_date';
	const LAST_ERROR_MESSAGE   = 'last_error_message';

	/**
	 * Get all product catalogs from the DB
	 *
	 * @return array
	 */
	public function get_raw_data() {
		return get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Get all product catalogs from the DB
	 *
	 * @return Entity[]
	 */
	public function get_product_catalogs() {
		return array_map( array( $this, 'map_entity_data' ), $this->get_raw_data() );
	}

	/**
	 * Get a record from the DB by the specified ID
	 *
	 * @param string $id The product catalog ID.
	 *
	 * @return Entity
	 * @throws EntityException When entity does not exist.
	 */
	public function get_product_catalog( $id ) {
		$product_catalogs = $this->get_raw_data();

		if ( ! isset( $product_catalogs[ $id ] ) ) {
			throw EntityException::does_not_exist( $id );
		}

		return $this->map_entity_data( $product_catalogs[ $id ] );
	}

	/**
	 * Add a new product catalog into Database
	 *
	 * @param Entity $entity The product catalog entity instance.
	 *
	 * @throws EntityException When entity already exists.
	 *
	 * @return void
	 */
	public function create_product_catalog( Entity $entity ) {
		if ( $this->is_product_catalog_exists( $entity ) ) {
			throw EntityException::is_already_existing( $entity );
		}

		$id   = $entity->get_id();
		$date = new \DateTime();

		if ( empty( $id ) ) {
			throw EntityException::name_is_empty();
		}

		update_option(
			self::OPTION_NAME,
			array_merge(
				$this->get_raw_data(),
				array(
					$entity->get_id() => array(
						self::ID_FIELD             => $entity->get_id(),
						self::FORMAT_FIELD         => $entity->get_format(),
						self::CONFIG_FIELD         => $entity->get_config(),
						self::PRODUCTS_COUNT_FIELD => $entity->get_products_count(),
						self::LAST_UPDATE_DATE     => $date->format( \DateTime::ISO8601 ),
						self::LAST_ERROR_MESSAGE   => $entity->get_last_error_message(),
					),
				)
			)
		);
	}

	/**
	 * Update the product catalog
	 *
	 * @param Entity $entity The product catalog entity instance.
	 *
	 * @throws EntityException When the entity does not exist.
	 *
	 * @return void
	 */
	public function update_product_catalog( Entity $entity ) {
		if ( ! $this->is_product_catalog_exists( $entity ) ) {
			throw EntityException::does_not_exist( $entity->get_id() );
		}

		$product_catalogs                      = $this->get_raw_data();
		$product_catalogs[ $entity->get_id() ] = array(
			self::ID_FIELD             => $entity->get_id(),
			self::FORMAT_FIELD         => $entity->get_format(),
			self::CONFIG_FIELD         => $entity->get_config(),
			self::PRODUCTS_COUNT_FIELD => $entity->get_products_count(),
			self::LAST_UPDATE_DATE     => $entity->get_last_update_date()->format( \DateTime::ISO8601 ),
			self::LAST_ERROR_MESSAGE   => $entity->get_last_error_message(),
		);

		update_option( self::OPTION_NAME, $product_catalogs );
	}

	/**
	 * Delete an Entity from DB
	 *
	 * @param Entity $entity The product catalog entity instance.
	 *
	 * @throws EntityException When the entity does not exist.
	 *
	 * @return void
	 */
	public function delete_product_catalog( Entity $entity ) {
		if ( ! $this->is_product_catalog_exists( $entity ) ) {
			throw EntityException::does_not_exist( $entity->get_id() );
		}

		$product_catalogs = $this->get_raw_data();
		unset( $product_catalogs[ $entity->get_id() ] );

		update_option( self::OPTION_NAME, $product_catalogs );
	}

	/**
	 * Detect if the product catalog is already created with the same name
	 *
	 * @param Entity $entity The product catalog entity instance.
	 *
	 * @return bool
	 */
	public function is_product_catalog_exists( Entity $entity ) {
		return in_array( $entity->get_id(), array_keys( $this->get_raw_data() ), true );
	}

	/**
	 * Map the raw data from DB into Entity
	 *
	 * @param array $data The entity data.
	 *
	 * @return Entity
	 * @throws \Exception When the mapping fails.
	 */
	protected function map_entity_data( array $data ) {
		$entity = new Entity();

		$data = wp_parse_args(
			$data,
			array(
				self::PRODUCTS_COUNT_FIELD => 0,
				self::LAST_ERROR_MESSAGE   => '',
			)
		);

		// Set data.
		$entity->set_id( $data[ self::ID_FIELD ] );
		$entity->set_format( $data[ self::FORMAT_FIELD ] );
		$entity->set_config( $data[ self::CONFIG_FIELD ] );
		$entity->set_products_count( $data[ self::PRODUCTS_COUNT_FIELD ] );
		$entity->set_last_update_date( new \DateTime( $data[ self::LAST_UPDATE_DATE ] ) );
		$entity->set_last_error_message( $data[ self::LAST_ERROR_MESSAGE ] );

		return $entity;
	}

	/**
	 * Set as saved all products from the items of the current chunk
	 *
	 * This method will be called after the XML is saved
	 *
	 * @param FeedMapper[]          $items The list of FeedMapper instances to save in the feed.
	 * @param ProductCatalogManager $product_catalog The product catalog manager instnace.
	 *
	 * @return void
	 */
	public function set_items_saved_in_feed( $items, ProductCatalogManager $product_catalog ) {
		foreach ( $items as $entry ) {
			$addon = $entry->get_item()->get_addon();
			$addon->set_product_saved_in_feed( $product_catalog, $entry->get_item() );
		}
	}

	/**
	 * Returns the transient key of the feed status flag
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return string
	 */
	protected function feed_saving_status_transient_key( ProductCatalogManager $product_catalog ) {
		return $product_catalog->get_entity()->get_id() . '_saving';
	}

	/**
	 * Mark the product catalog feed in saving
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return void
	 */
	public function mark_feed_in_saving( ProductCatalogManager $product_catalog ) {
		set_transient( $this->feed_saving_status_transient_key( $product_catalog ), true );
	}

	/**
	 * Mark the product catalog feed saving as complete
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return void
	 */
	public function mark_feed_saving_complete( ProductCatalogManager $product_catalog ) {
		delete_transient( $this->feed_saving_status_transient_key( $product_catalog ) );
	}

	/**
	 * Detect if the product feed is saving in background
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return bool
	 */
	public function is_feed_saving( ProductCatalogManager $product_catalog ) {
		return get_transient( $this->feed_saving_status_transient_key( $product_catalog ) );
	}

}
