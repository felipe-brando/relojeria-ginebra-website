<?php
/**
 * Main class for the product catalog manager
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelCaffeine\ProductCatalog\Admin\Metaboxes;
use PixelCaffeine\ProductCatalog\Cron\RefreshFeed;
use PixelCaffeine\ProductCatalog\Dictionary\FeedSaver;
use PixelCaffeine\ProductCatalog\Entity\ProductCatalog as Entity;
use PixelCaffeine\ProductCatalog\Entity\ProductCatalog;
use PixelCaffeine\ProductCatalog\Exception\FeedException;
use PixelCaffeine\ProductCatalog\Feed\WriterInterface;
use PixelCaffeine\ProductCatalog\Feed\XMLWriter;
use PixelCaffeine\ProductCatalog\Helper\FeedDirectoryHelper;

/**
 * Product catalog entity manager
 *
 * @class Manager
 */
class ProductCatalogManager {

	const FILTER_ALL       = 'all';
	const FILTER_SAVED     = 'saved';
	const FILTER_EDITED    = 'edited';
	const FILTER_NOT_SAVED = 'not-saved';

	/**
	 * List of allowed feed formats
	 *
	 * @var array
	 */
	private $allowed_feed_formats = array(
		'xml',
	);

	/**
	 * The product catalog ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The product catalog entity
	 *
	 * @var Entity
	 */
	protected $entity;

	/**
	 * The DB provider for the queries
	 *
	 * @var DbProvider
	 */
	protected $db_provider;

	/**
	 * The catalog configuration defaults
	 *
	 * @var ConfigurationDefaults
	 */
	protected $default_configuration;

	/**
	 * The metabox manager of the post meta
	 *
	 * @var Metaboxes
	 */
	protected $metaboxes;

	/**
	 * The feed directory helper useful for filesystem operations
	 *
	 * @var FeedDirectoryHelper
	 */
	protected $directory_helper;

	/**
	 * The feed file writer service
	 *
	 * @var WriterInterface
	 */
	protected $feed_writer;

	/**
	 * The catalog configuration manager
	 *
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * The adapter for facebook API requests
	 *
	 * @var \AEPC_Facebook_Adapter
	 */
	protected $fb_api;

	/**
	 * ProductCatalog constructor.
	 *
	 * @param string                 $id The product catalog ID.
	 * @param DbProvider             $db_provider The DB provider.
	 * @param ConfigurationDefaults  $default_configuration The default catalog configuration.
	 * @param Metaboxes              $metaboxes The metaboxes instance.
	 * @param \AEPC_Facebook_Adapter $fb_api The FB API service instance.
	 *
	 * @throws Exception\EntityException Fail when error occurred during load product catalog data.
	 */
	public function __construct(
		$id,
		DbProvider $db_provider,
		ConfigurationDefaults $default_configuration,
		Metaboxes $metaboxes,
		\AEPC_Facebook_Adapter $fb_api
	) {
		$this->id                    = $id;
		$this->db_provider           = $db_provider;
		$this->default_configuration = $default_configuration;
		$this->metaboxes             = $metaboxes;
		$this->fb_api                = $fb_api;

		// Load data from the database.
		$this->load();
	}

	/**
	 * Load the entity from the DB
	 *
	 * @throws Exception\EntityException Fail when error occurred during load product catalog data.
	 *
	 * @return void
	 */
	public function load() {
		$this->entity = $this->db_provider->get_product_catalog( $this->id );
	}

	/**
	 * Delete the product catalog
	 *
	 * @throws FeedException Fail when error during filesystem operations. Fail when error during filesystem operations.
	 * @throws Exception\EntityException Fail when error occurred during load product catalog data.
	 *
	 * @return void
	 */
	public function delete() {
		$this->get_feed_writer()->delete( FeedSaver::DELETE_CONTEXT );
		$this->db_provider->delete_product_catalog( $this->entity );
	}

	/**
	 * Edit the product catalog
	 *
	 * @throws Exception\EntityException Fail when error occurred during load product catalog data.
	 *
	 * @return void
	 */
	public function update() {
		$this->entity->set_config( $this->configuration()->get_configuration_data() );
		$this->db_provider->update_product_catalog( $this->entity );
	}

	/**
	 * Unschedule the job for this product feed
	 *
	 * @return void
	 */
	public function unschedule_job() {
		$job = new RefreshFeed();
		$job->unschedule( $this->entity->get_id() );
	}

	/**
	 * Set the entity instance
	 *
	 * @param ProductCatalog $entity The entity instance.
	 *
	 * @return void
	 */
	public function set_entity( ProductCatalog $entity ) {
		$this->entity = $entity;
	}

	/**
	 * Get the entity instance
	 *
	 * @return Entity
	 */
	public function get_entity() {
		return $this->entity;
	}

	/**
	 * Returns the URL of the feed
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->get_feed_directory_helper()->get_feed_url();
	}

	/**
	 * Returns the URL of the product catalog in the business manager page
	 *
	 * @return string
	 */
	public function get_fb_url() {
		$product_catalog_id = $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_CATALOG_ID );
		return sprintf( 'https://www.facebook.com/products/catalogs/%d/diagnostics#', $product_catalog_id );
	}

	/**
	 * Get the configurator class of this product catalog
	 *
	 * @return Configuration
	 */
	public function configuration() {
		if ( ! $this->configuration instanceof Configuration ) {
			$this->set_configurator();
		}

		return $this->configuration;
	}

	/**
	 * Set the configurator into this product catalog
	 *
	 * @return void
	 */
	protected function set_configurator() {
		$this->configuration = new Configuration( $this->entity, $this->default_configuration );
	}

	/**
	 * Returns the only selected addons, from the detected ones
	 *
	 * @return \AEPC_Edd_Addon_Support[]|\AEPC_Woocommerce_Addon_Support[]
	 */
	protected function get_addon_selected() {
		$addons          = array();
		$selected_addons = (array) $this->configuration()->get( Configuration::OPTION_SELECTED_ADDON );

		foreach ( \AEPC_Addons_Support::get_detected_addons() as $addon ) {
			if ( in_array( $addon->get_slug(), $selected_addons, true ) ) {
				$addons[] = $addon;
			}
		}

		return $addons;
	}

	/**
	 * Get the items filtered by feed status
	 *
	 * @param string $filter One of 'all', 'not_saved' or 'edited'.
	 *
	 * @return FeedMapper[]
	 */
	public function get_items( $filter ) {
		$items = array();
		foreach ( $this->get_addon_selected() as $addon ) {
			if ( self::FILTER_ALL === $filter ) {
				$feed_entries = $addon->get_feed_entries( $this, $this->metaboxes );
			} elseif ( self::FILTER_NOT_SAVED === $filter ) {
				$feed_entries = $addon->get_feed_entries_to_save( $this, $this->metaboxes );
			} elseif ( self::FILTER_EDITED === $filter ) {
				$feed_entries = $addon->get_feed_entries_to_edit( $this, $this->metaboxes );
			} else {
				continue;
			}

			$items = array_merge( $items, $feed_entries );
		}

		// Assign to each the FeedMapper instance.
		$configuration = $this->configuration();
		$items         = array_map(
			function( \AEPC_Addon_Product_Item $item ) use ( $configuration ) {
				return new FeedMapper( $item, $configuration );
			},
			$items
		);

		return $items;
	}

	/**
	 * Remove all feed status flag associated to this product catalog from all products in each addon
	 *
	 * @return void
	 */
	public function remove_all_feed_status_flags() {
		foreach ( $this->get_addon_selected() as $addon ) {
			$addon->remove_all_feed_status( $this );
		}
	}

	/**
	 * Get the allowed feed formats.
	 *
	 * Give ability to external developers to specify own new format and define the specific class
	 *
	 * @return array
	 */
	public function get_allowed_feed_formats() {
		return apply_filters( 'aepc_allowed_feed_formats', $this->allowed_feed_formats );
	}

	/**
	 * Let know if the format specified is supported
	 *
	 * @param string $format The extension to check.
	 *
	 * @return bool
	 */
	public function is_feed_format_allowed( $format ) {
		return in_array( $format, $this->get_allowed_feed_formats(), true );
	}

	/**
	 * Return the directory helper of the feed
	 *
	 * @return FeedDirectoryHelper
	 */
	public function get_feed_directory_helper() {
		if ( ! $this->directory_helper instanceof FeedDirectoryHelper ) {
			$this->set_feed_directory_helper( new FeedDirectoryHelper( $this ) );
		}

		return $this->directory_helper;
	}

	/**
	 * Sets the output writer manually
	 *
	 * @param FeedDirectoryHelper $directory_helper The feed directory helper instance.
	 *
	 * @return void
	 */
	public function set_feed_directory_helper( FeedDirectoryHelper $directory_helper ) {
		$this->directory_helper = $directory_helper;
	}

	/**
	 * Return the output writer
	 *
	 * @return WriterInterface
	 * @throws FeedException Fail when error during filesystem operations.
	 */
	public function get_feed_writer() {
		if ( ! $this->feed_writer instanceof WriterInterface ) {
			$this->set_feed_writer_by_format( $this->get_entity()->get_format() );
		}

		return $this->feed_writer;
	}

	/**
	 * Sets the output writer manually
	 *
	 * @param WriterInterface $feed_writer The feed writer instance.
	 *
	 * @return void
	 */
	public function set_feed_writer( WriterInterface $feed_writer ) {
		$this->feed_writer = $feed_writer;
	}

	/**
	 * Set the feed writer instance by the format of the feed
	 *
	 * @param string $format The extension.
	 *
	 * @throws FeedException Fail when error during filesystem operations.
	 *
	 * @return void
	 */
	protected function set_feed_writer_by_format( $format ) {
		if ( ! $this->is_feed_format_allowed( $format ) ) {
			throw FeedException::formatNotSupported( $format );
		}

		switch ( $format ) {

			case 'xml':
				$this->feed_writer = new XMLWriter( $this, $this->db_provider, $this->get_feed_directory_helper() );
				break;

			default:
				$this->feed_writer = apply_filters( 'aepc_feed_writer', null, $format, $this );

				if ( ! $this->feed_writer instanceof WriterInterface ) {
					throw FeedException::writerNotInitialized( $format );
				}

				break;
		}
	}

	/**
	 * Get the feed saver based on configuration.
	 *
	 * It can be background saver or foreground saver.
	 *
	 * @return FeedSaverInterface
	 */
	public function get_feed_saver() {
		if ( $this->must_be_saved_in_background() ) {
			return new BackgroundFeedSaver( $this );
		} else {
			return new ForegroundFeedSaver( $this );
		}
	}

	/**
	 * Detect if the product catalog is configured to be saved with background process
	 *
	 * @return bool
	 */
	public function must_be_saved_in_background() {
		return $this->configuration()->get( Configuration::OPTION_ENABLE_BACKGROUND_SAVE );
	}

	/**
	 * Detect if there are items to save in the feed yet
	 *
	 * @return bool
	 */
	public function there_are_items_to_save() {
		foreach ( $this->get_addon_selected() as $addon ) {
			if ( $addon->there_are_items_to_save( $this ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Mark the product catalog feed in saving
	 *
	 * @return void
	 */
	public function mark_feed_in_saving() {
		$this->db_provider->mark_feed_in_saving( $this );
	}

	/**
	 * Mark the product catalog saving as complete
	 *
	 * @return void
	 */
	public function mark_feed_saving_complete() {
		$this->db_provider->mark_feed_saving_complete( $this );
	}

	/**
	 * Set the Facebook API service for the api requests
	 *
	 * @param \AEPC_Facebook_Adapter $fb_api The Facebook API service.
	 *
	 * @return void
	 */
	public function set_fb_api( \AEPC_Facebook_Adapter $fb_api ) {
		$this->fb_api = $fb_api;
	}

	/**
	 * Create the product catalog in the Facebook account and also create add the product feed inside with XML associated
	 *
	 * @throws \Exception Fail when error during pushing into Facebook.
	 *
	 * @return void
	 */
	public function push_to_fb() {
		$product_catalog_id   = $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_CATALOG_ID );
		$product_feed_id      = $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_ID );
		$product_catalog_name = $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_CATALOG_NAME );
		$product_feed_name    = sprintf( 'Automatic product feed from %s', untrailingslashit( preg_replace( '/http(s)?:\/\//', '', home_url() ) ?: '' ) );
		$schedule_options     = array(
			'url'            => esc_url( $this->get_url() ),
			'interval'       => $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ),
			'interval_count' => $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL_COUNT ),
			'day_of_week'    => $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_DAY_OF_WEEK ),
			'hour'           => $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_HOUR ),
			'minute'         => $this->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_MINUTE ),
			'timezone'       => date_default_timezone_get(),
		);

		// Create a new product catalog if any.
		if ( empty( $product_catalog_id ) ) {
			$product_catalog_id = $this->fb_api->create_product_catalog( $product_catalog_name );
			$this->configuration()->set( Configuration::OPTION_FB_ACTION, Configuration::VALUE_FB_ACTION_UPDATE );
			$this->configuration()->set( Configuration::OPTION_FB_PRODUCT_CATALOG_ID, $product_catalog_id );
		}

		if ( empty( $product_feed_id ) || 'new' === $product_feed_id ) {
			// Create product feed if any.
			$product_feed_id = $this->fb_api->add_product_feed( $product_catalog_id, $product_feed_name, $schedule_options );
			$this->configuration()->set( Configuration::OPTION_FB_PRODUCT_FEED_ID, $product_feed_id );
			$this->configuration()->set( Configuration::OPTION_FB_PRODUCT_FEED_NAME, $product_feed_name );
		} else {
			// Update schedule options in an existing feed.
			$product_feed_id = $this->fb_api->update_product_feed( $product_feed_id, $schedule_options );
		}

		// Associate pixel to.
		$this->fb_api->associate_pixel_to_product_catalog( $product_catalog_id );

		// Save product catalog in the db.
		$this->update();
	}

}
