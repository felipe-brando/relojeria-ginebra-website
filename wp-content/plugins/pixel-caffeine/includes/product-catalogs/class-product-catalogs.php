<?php
/**
 * Manager of all product catalogs
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use AEPC_Facebook_Adapter as Facebook;
use PixelCaffeine\ProductCatalog\Admin\Metaboxes;
use PixelCaffeine\ProductCatalog\Dictionary\FeedSaver;
use PixelCaffeine\ProductCatalog\Entity\ProductCatalog as Entity;
use PixelCaffeine\ProductCatalog\Exception\FeedException;
use PixelCaffeine\ProductCatalog\Exception\GoogleTaxonomyException;
use PixelCaffeine\ProductCatalog\Feed\XMLWriter;
use PixelCaffeine\Dependencies\Symfony\Component\Filesystem\Filesystem;

/**
 * Manager class of Product Catalog feature
 *
 * @class ProductCatalog
 */
final class ProductCatalogs {

	/**
	 * The filesystem instance
	 *
	 * @var Filesystem|null
	 */
	protected $filesystem;

	/**
	 * The facebook API service
	 *
	 * @var Facebook
	 */
	private $fb_api;

	/**
	 * The DB provider
	 *
	 * @var DbProvider
	 */
	private $db_provider;

	/**
	 * The default catalog configuration
	 *
	 * @var ConfigurationDefaults
	 */
	protected $default_configuration;

	/**
	 * The metabox manager
	 *
	 * @var Metaboxes
	 */
	protected $metaboxes;

	/**
	 * The feed saver service
	 *
	 * @var FeedSaverInterface
	 */
	protected $feed_saver;

	/**
	 * The background saver process instance
	 *
	 * @var BackgroundFeedSaverProcess
	 */
	protected $background_saver_process;

	/**
	 * List of all product catalogs created
	 *
	 * @var ProductCatalogManager[]
	 */
	protected $product_catalogs;

	/**
	 * ProductCatalog constructor.
	 *
	 * @param Facebook              $fb_api The Facebook API service.
	 * @param DbProvider            $db_provider The DB provider.
	 * @param ConfigurationDefaults $default_configuration The default catalog configuration.
	 * @param Metaboxes             $metaboxes The metabox manager instance.
	 * @param Filesystem|null       $filesystem The filesystem layer.
	 */
	public function __construct(
		Facebook $fb_api,
		DbProvider $db_provider,
		ConfigurationDefaults $default_configuration,
		Metaboxes $metaboxes,
		Filesystem $filesystem = null
	) {
		$this->fb_api                = $fb_api;
		$this->db_provider           = $db_provider;
		$this->default_configuration = $default_configuration;
		$this->metaboxes             = $metaboxes;
		$this->filesystem            = $filesystem;
	}

	/**
	 * Setup the necessary hooks
	 *
	 * @return void
	 */
	public function setup() {
		// Badly the WP_Async_Request add some hooks inside the constructor, so I need to instantiate the object separately.
		$this->background_saver_process = new BackgroundFeedSaverProcess();
	}

	/**
	 * Returns the default configuration of a product catalog
	 *
	 * @return ConfigurationDefaults
	 */
	public function get_defaults() {
		return $this->default_configuration;
	}

	/**
	 * Get the product catalogs from DB
	 *
	 * @return ProductCatalogManager[]
	 */
	public function get_product_catalogs() {
		// No cache please.
		return array_map( array( $this, 'map_manager_instance' ), $this->db_provider->get_product_catalogs() );
	}

	/**
	 * Get the product catalog instance
	 *
	 * @param string $id The product catalog ID.
	 *
	 * @return ProductCatalogManager
	 */
	public function get_product_catalog( $id ) {
		$product_catalogs = $this->get_product_catalogs();
		return $product_catalogs[ $id ];
	}

	/**
	 * Detect if at least one product catalog is created
	 *
	 * @return bool
	 */
	public function is_product_catalog_created() {
		return count( $this->get_product_catalogs() ) > 0;
	}

	/**
	 * Returns the ProductCatalogManager instance of the entity
	 *
	 * @param Entity $entity The product catalog entity instance.
	 *
	 * @return ProductCatalogManager
	 * @throws Exception\EntityException Fail when unable to load the product record from DB.
	 */
	protected function map_manager_instance( Entity $entity ) {
		return new ProductCatalogManager(
			$entity->get_id(),
			$this->db_provider,
			$this->default_configuration,
			$this->metaboxes,
			$this->fb_api
		);
	}

	/**
	 * Get the product category list from google, it's necessary for the product feed requirements
	 *
	 * @throws GoogleTaxonomyException Fail when unable to fetch the google category list.
	 *
	 * @return array
	 */
	public function get_google_categories() {
		$cache_key  = 'aepc_google_taxonomy_list';
		$categories = get_transient( $cache_key );

		if ( false === $categories ) {
			$response = wp_remote_get( 'https://www.google.com/basepages/producttype/taxonomy-with-ids.en-GB.txt' );

			if ( is_wp_error( $response ) ) {
				throw new GoogleTaxonomyException( $response->get_error_message() );
			}

			$remote_list = wp_remote_retrieve_body( $response );
			$lines       = explode( "\n", trim( $remote_list ) );

			// Remove the first line that is a comment.
			array_shift( $lines );

			$categories = array();
			foreach ( $lines as $line ) {
				list( $id, $hierarchy ) = explode( ' - ', trim( $line ) );
				$terms                  = explode( ' > ', $hierarchy );
				$hierarchy              = array( array_pop( $terms ) => array() );
				foreach ( array_reverse( $terms ) as $term ) {
					$hierarchy = array( $term => $hierarchy );
				}
				$categories = array_merge_recursive( $categories, $hierarchy );
			}

			set_transient( $cache_key, $categories, MONTH_IN_SECONDS );
		}

		return $categories;
	}

	/**
	 * Call the background saver service to generate the feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 * @param string                $context The context.
	 *
	 * @return array|\WP_Error
	 * @throws FeedException Fail when unable to save the feed.
	 */
	public function generate_feed( ProductCatalogManager $product_catalog, $context ) {
		return $product_catalog->get_feed_saver()->save( $context );
	}

	/**
	 * Save a new product catalog
	 *
	 * @param Entity $entity The product catalog entity.
	 *
	 * @return array|\WP_Error
	 * @throws \Exception Fail when unable to save the product catalog.
	 */
	public function create_product_catalog( Entity $entity ) {

		// Save into the db.
		$this->db_provider->create_product_catalog( $entity );

		// Get product manager instance.
		$product_catalog = $this->get_product_catalog( $entity->get_id() );

		// Set the filesystem if any (added for test).
		if ( $this->filesystem ) {
			$xml_writer = $product_catalog->get_feed_writer();
			if ( $xml_writer instanceof XMLWriter ) {
				$xml_writer->setFilesystem( $this->filesystem );
			}
		}

		try {
			$response = $this->generate_feed( $product_catalog, FeedSaver::NEW_CONTEXT );

			// Save product catalog in FB.
			if ( $product_catalog->configuration()->get( Configuration::OPTION_FB_ENABLE ) ) {
				$product_catalog->push_to_fb();
			}

			return $response;
		} catch ( \Exception $e ) {
			$this->db_provider->delete_product_catalog( $entity );
			throw $e;
		}
	}

	/**
	 * Update a product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 *
	 * @return array|\WP_Error
	 * @throws \Exception Fail when unable to update the product catalog.
	 */
	public function update_product_catalog( ProductCatalogManager $product_catalog ) {

		// Save new entity.
		$product_catalog->update();

		// Unschedule cron jobs.
		$product_catalog->unschedule_job();

		// Save product catalog in FB.
		if ( $product_catalog->configuration()->get( Configuration::OPTION_FB_ENABLE ) ) {
			$product_catalog->push_to_fb();
		}

		return $this->generate_feed( $product_catalog, FeedSaver::REFRESH_CONTEXT );
	}

	/**
	 * Delete a product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 *
	 * @throws Exception\EntityException Fail when unable to delete the product catalog.
	 * @throws FeedException Fail when unable to delete the product catalog.
	 *
	 * @return void
	 */
	public function delete_product_catalog( ProductCatalogManager $product_catalog ) {
		$product_catalog->delete();

		// Unschedule cron jobs.
		$product_catalog->unschedule_job();
	}

	/**
	 * Get the background saver instance
	 *
	 * We have to instantiate this class before when it must be used because the class has badly some hooks inside the
	 * constructor that might be added early
	 *
	 * @return BackgroundFeedSaverProcess
	 */
	public function get_background_saver_process() {
		return $this->background_saver_process;
	}

	/**
	 * Detect if the product feed is saving in background
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 *
	 * @return bool
	 */
	public function is_feed_saving( ProductCatalogManager $product_catalog ) {
		try {
			return $this->background_saver_process->is_updating( $product_catalog );
		} catch ( FeedException $e ) {
			return false;
		}
	}

	/**
	 * Check if the product catalog feature can work
	 *
	 * @return bool
	 */
	public function is_product_catalog_enabled() {
		return \AEPC_Addons_Support::are_detected_addons();
	}

}
