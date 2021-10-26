<?php
/**
 * Collect the defaults value of product catalog configuration
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

/**
 * Class ConfigurationDefaults
 *
 * @package PixelCaffeine\ProductCatalog
 */
class ConfigurationDefaults {

	/**
	 * The default configuration values.
	 *
	 * @var array
	 */
	protected $default_values;

	/**
	 * ConfigurationDefaults constructor.
	 */
	public function __construct() {
		$addons = array();
		foreach ( \AEPC_Addons_Support::get_detected_addons() as $addon ) {
			$addons[] = $addon->get_slug();
		}

		$this->set_defaults(
			array(
				Configuration::OPTION_FEED_NAME          => 'main-product-catalog',
				Configuration::OPTION_FEED_FORMAT        => 'xml',
				Configuration::OPTION_ENABLE_BACKGROUND_SAVE => false,
				Configuration::OPTION_GOOGLE_CATEGORY    => array(),
				Configuration::OPTION_FILE_NAME          => '',
				Configuration::OPTION_DIRECTORY_PATH     => '',
				Configuration::OPTION_DIRECTORY_URL      => '',
				Configuration::OPTION_CHUNK_LIMIT        => 500,
				Configuration::OPTION_SKU_FOR_ID         => true,
				Configuration::OPTION_IMAGE_SIZE         => 'full',
				Configuration::OPTION_SELECTED_ADDON     => $addons,
				Configuration::OPTION_FILTER_BY_TYPE     => array(),
				Configuration::OPTION_FILTER_BY_CATEGORY => array(),
				Configuration::OPTION_FILTER_BY_TAG      => array(),
				Configuration::OPTION_FILTER_BY_STOCK    => array(),
				Configuration::OPTION_FILTER_ON_SALE     => false,
				Configuration::OPTION_NO_VARIATIONS      => false,
				Configuration::OPTION_REFRESH_CYCLE      => 1,
				Configuration::OPTION_REFRESH_CYCLE_TYPE => 'hour',
				Configuration::OPTION_PRODUCTS_COUNT     => 0,
				Configuration::OPTION_MAP_BRAND          => trim( get_bloginfo( 'name' ) ),
				Configuration::OPTION_MAP_CONDITION      => 'new',
				Configuration::OPTION_MAP_DESCRIPTION    => 'full-description',
				Configuration::OPTION_MAP_PRICE          => 'price-no-tax',
				Configuration::OPTION_MAP_CUSTOM_LABEL_0 => '',
				Configuration::OPTION_MAP_CUSTOM_LABEL_1 => '',
				Configuration::OPTION_MAP_CUSTOM_LABEL_2 => '',
				Configuration::OPTION_MAP_CUSTOM_LABEL_3 => '',
				Configuration::OPTION_MAP_CUSTOM_LABEL_4 => '',
				Configuration::OPTION_FB_ENABLE          => false,
				Configuration::OPTION_FB_ACTION          => Configuration::VALUE_FB_ACTION_NEW,
				Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL => \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_DAILY,
				Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL_COUNT => 1,
				Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_HOUR => gmdate( 'G' ),
				Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_MINUTE => gmdate( 'i' ),
			)
		);
	}

	/**
	 * Set whole array of defaults
	 *
	 * @param array $defaults The default configuration value.
	 *
	 * @return void
	 */
	public function set_defaults( array $defaults ) {
		$this->default_values = $defaults;
	}

	/**
	 * Set a key into the existing defaults array
	 *
	 * @param string $key The configuration key.
	 * @param mixed  $value The configuration value.
	 *
	 * @return void
	 */
	public function set( $key, $value ) {
		$this->default_values[ $key ] = $value;
	}

	/**
	 * Get the value of a specific
	 *
	 * @param string $key The configuration key.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		if ( isset( $this->default_values[ $key ] ) ) {
			return $this->default_values[ $key ];
		}

		return null;
	}

}
