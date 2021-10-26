<?php
/**
 * Base class for an Add-on support
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\UserData;
use PixelCaffeine\ProductCatalog\Admin\Metaboxes;
use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Base class for an Add-on support
 *
 * @class AEPC_Addon_Factory
 */
abstract class AEPC_Addon_Factory {

	/**
	 * The slug of addon, useful to identify some common resources
	 *
	 * @var string
	 */
	protected $addon_slug = '';

	/**
	 * Store the name of addon. It doesn't need a translate.
	 *
	 * @var string
	 */
	protected $addon_name = '';

	/**
	 * Store the main file of the plugin
	 *
	 * @var string
	 */
	protected $main_file = '';

	/**
	 * Store the URL of plugin website
	 *
	 * @var string
	 */
	protected $website_url = '';

	/**
	 * List of standard events supported
	 *
	 * @var array
	 */
	protected $events_support = array();

	/**
	 * List of events enabled for this add-on
	 *
	 * @var bool[]
	 */
	protected $events_enabled = array();

	/**
	 * The path for the logo images
	 */
	const LOGO_IMG_PATH = 'includes/admin/assets/img/store-logo/';

	/**
	 * Method where set all necessary hooks launched from 'init' action
	 *
	 * @return void
	 */
	abstract public function setup();

	/**
	 * Returns the human name of addon to show somewhere
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->addon_name;
	}

	/**
	 * Returns the human name of addon to show somewhere
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->addon_slug;
	}

	/**
	 * Get the main file of addon
	 *
	 * @return string
	 */
	public function get_main_file() {
		return $this->main_file;
	}

	/**
	 * Returns the website URL, useful on frontend to link the user to the plugin website
	 *
	 * @return string
	 */
	public function get_website_url() {
		return $this->website_url;
	}

	/**
	 * Returns the URI of logo image to show on admin UI
	 *
	 * @return string
	 */
	public function get_logo_img() {
		return PixelCaffeine()->plugin_url() . '/' . self::LOGO_IMG_PATH . $this->addon_slug . '.png';
	}

	/**
	 * Dynamic Ads methods
	 */

	/**
	 * Check if the add on supports the event name passed in parameter, useful when the code should know what events
	 * must fire
	 *
	 * @param string $event_name The event name.
	 *
	 * @return bool
	 */
	public function supports_event( $event_name ) {
		return in_array( $event_name, $this->events_support, true );
	}

	/**
	 * Get the events supported by this addon
	 *
	 * @return array
	 */
	public function get_event_supported() {
		return $this->events_support;
	}

	/**
	 * Check if an event is enabled
	 *
	 * @param string $event The event name for which check is status.
	 *
	 * @return bool
	 */
	public function is_event_enabled( $event ) {
		return isset( $this->events_enabled[ $event ] ) ? $this->events_enabled[ $event ] : false;
	}

	/**
	 * Set the status of an event (enabled or disabled)
	 *
	 * @param string $event The event name.
	 * @param bool   $status The status (true = enabled, false = disabled).
	 *
	 * @return void
	 */
	public function set_event_status( $event, $status ) {
		$this->events_enabled[ $event ] = $status;
	}

	/**
	 * Set an event as enabled
	 *
	 * @param string $event The name of event to be enabled.
	 *
	 * @return void
	 */
	public function enable_event( $event ) {
		$this->set_event_status( $event, true );
	}

	/**
	 * Set an event as disabled
	 *
	 * @param string $event The name of event to be disabled.
	 *
	 * @return void
	 */
	public function disable_event( $event ) {
		$this->set_event_status( $event, false );
	}

	/**
	 * Get the parameters to send with one of standard event
	 *
	 * @param string $event One of standard events by facebook, such 'ViewContent', 'AddToCart', so on.
	 * @param array  $args The arguments to pass for the parameters callback.
	 *
	 * @return array
	 */
	public function get_parameters_for( $event, $args = array() ) {
		$sanitized_event = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $event ) ?: '' );
		$callback        = array( $this, 'get_' . $sanitized_event . '_params' );
		return AEPC_Track::check_event_parameters(
			$event,
			apply_filters( 'aepc_event_parameters', is_callable( $callback ) ? call_user_func_array( $callback, $args ) : array(), $sanitized_event )
		);
	}

	/**
	 * Send the event with correct parameters, if it's enabled and the event is set to be able to be fired
	 *
	 * @param string    $event The event name.
	 * @param array     $args The arguments to pass for the parameters callback.
	 * @param null|bool $unique Define if the event must be unique. (null = not defined).
	 *
	 * @return void
	 */
	public function send_event( $event, $args = array(), $unique = null ) {
		if ( $this->can_fire( $event ) ) {
			$params    = $this->get_parameters_for( $event, $args );
			$user_data = null;

			if ( ! empty( $params['user_data'] ) ) {
				$user_data = $params['user_data'];
				unset( $params['user_data'] );
			}

			if ( null !== $unique ) {
				$params['unique'] = $unique;
			}

			AEPC_Track::track( $event, $params, array(), $user_data );
		}
	}

	/**
	 * Check if we are in a place to fire the event passed in parameter
	 *
	 * @param string $event One of standard events by facebook, such 'ViewContent', 'AddToCart', so on.
	 *
	 * @return bool
	 */
	public function can_fire( $event ) {
		if ( ! $this->is_event_enabled( $event ) || ! $this->supports_event( $event ) ) {
			return false;
		}

		$event    = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $event ) ?: '' );
		$callback = array( $this, 'can_fire_' . $event );
		return apply_filters( 'aepc_can_track', is_callable( $callback ) ? call_user_func( $callback ) : false, $event );
	}

	/**
	 * Check if we are in a place to fire the ViewContent event
	 *
	 * @return bool
	 */
	protected function can_fire_view_content() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the ViewCategory event
	 *
	 * @return bool
	 */
	protected function can_fire_view_category() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the AddToCart event
	 *
	 * @return bool
	 */
	protected function can_fire_add_to_cart() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the InitiateCheckout event
	 *
	 * @return bool
	 */
	protected function can_fire_initiate_checkout() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the AddPaymentInfo event
	 *
	 * @return bool
	 */
	protected function can_fire_add_payment_info() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the Purchase event
	 *
	 * @return bool
	 */
	protected function can_fire_purchase() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the AddToWishlist event
	 *
	 * @return bool
	 */
	protected function can_fire_add_to_wishlist() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the Lead event
	 *
	 * @return bool
	 */
	protected function can_fire_lead() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the CompleteRegistration event
	 *
	 * @return bool
	 */
	protected function can_fire_complete_registration() {
		return false;
	}

	/**
	 * Check if we are in a place to fire the Search event
	 *
	 * @return bool
	 */
	protected function can_fire_search() {
		return false;
	}

	/**
	 * Get product info from single page for ViewContent event
	 *
	 * @return array
	 */
	protected function get_view_content_params() {
		return array();
	}

	/**
	 * Get product info from single page for ViewCategory event
	 *
	 * @return array
	 */
	protected function get_view_category_params() {
		return array();
	}

	/**
	 * Get info from product when added to cart for AddToCart event
	 *
	 * @param int|string|null $product_id The product ID (if null, it will be fetched automatically).
	 * @param int             $quantity The quantity of the item added into the cart.
	 *
	 * @return array
	 */
	protected function get_add_to_cart_params( $product_id = null, $quantity = 1 ) {
		return array();
	}

	/**
	 * Get info from checkout page for InitiateCheckout event
	 *
	 * @return array
	 */
	protected function get_initiate_checkout_params() {
		return array();
	}

	/**
	 * Get info from checkout page for AddPaymentInfo event
	 *
	 * @return array
	 */
	protected function get_add_payment_info_params() {
		return array();
	}

	/**
	 * Get product info from purchase succeeded page for Purchase event
	 *
	 * @return array
	 */
	protected function get_purchase_params() {
		return array();
	}

	/**
	 * Get info from product added to wishlist for AddToWishlist event
	 *
	 * @return array
	 */
	protected function get_add_to_wishlist_params() {
		return array();
	}

	/**
	 * Get info from lead of a sign up action for Lead event
	 *
	 * @return array
	 */
	protected function get_lead_params() {
		return array();
	}

	/**
	 * Get info from when a registration form is completed, such as signup for a service, for CompleteRegistration event
	 *
	 * @return array
	 */
	protected function get_complete_registration_params() {
		return array();
	}

	/**
	 * Get info a search of products is performed for Search event
	 *
	 * @return array
	 */
	protected function get_search_params() {
		return array();
	}

	/**
	 * Extend UserData entity if needed
	 *
	 * @param UserData $user_data User Data.
	 *
	 * @return UserData
	 */
	public function extend_user_data( UserData $user_data ) {
		return $user_data;
	}

	/**
	 * HELPERS
	 */

	/**
	 * Retrieve the product name
	 *
	 * @param int $product_id The ID of product where get its name.
	 *
	 * @return string
	 */
	public function get_product_name( $product_id ) {
		return (string) $product_id;
	}

	/**
	 * Says if the product is of addon type
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return bool
	 */
	public function is_product_of_this_addon( $product_id ) {
		return false;
	}

	/**
	 * Returns the types supported by this plugin
	 *
	 * @return array
	 */
	public function get_product_types() {
		return array();
	}

	/**
	 * Returns the checkout URL where the items may be purchased
	 *
	 * @return string
	 */
	public function get_checkout_url() {
		return '';
	}

	/**
	 * Return the AEPC_Addon_Product_Item instance for the product
	 *
	 * @param mixed         $product The product instance for that add-on.
	 * @param Metaboxes     $metaboxes The metaboxes manager instance.
	 * @param Configuration $configuration The configuration instance.
	 *
	 * @return AEPC_Addon_Product_Item
	 */
	abstract public function get_product_item( $product, Metaboxes $metaboxes, Configuration $configuration );

	/**
	 * Returns the array of all term objects id=>name for all categories of the shop
	 *
	 * @return array
	 */
	public function get_product_categories() {
		return array();
	}

	/**
	 * Returns the array of all term objects id=>name for all tags of the shop
	 *
	 * @return array
	 */
	public function get_product_tags() {
		return array();
	}

	/**
	 * Return the array with all AEPC_Addon_Product_Item instances for the products to include inside the XML feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 * @param Metaboxes             $metaboxes The metaboxes manager instance.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries( ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		return array();
	}

	/**
	 * Get the feed entries to save into the feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 * @param Metaboxes             $metaboxes The metaboxes manager instance.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries_to_save( ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		return $this->get_feed_entries( $product_catalog, $metaboxes );
	}

	/**
	 * Get the feed entries to edit in the feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 * @param Metaboxes             $metaboxes The metaboxes manager instance.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries_to_edit( ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		return $this->get_feed_entries( $product_catalog, $metaboxes );
	}

	/**
	 * Save a meta in the product post that set the product as saved in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The product catalog entity manager.
	 * @param \AEPC_Addon_Product_Item $item The add-on product item entity.
	 *
	 * @return void
	 */
	public function set_product_saved_in_feed( ProductCatalogManager $product_catalog, \AEPC_Addon_Product_Item $item ) {

	}

	/**
	 * Save the meta in the product post that set the product as edited in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The product catalog entity manager.
	 * @param \AEPC_Addon_Product_Item $item The add-on product item entity.
	 *
	 * @return void
	 */
	public function set_product_edited_in_feed( ProductCatalogManager $product_catalog, \AEPC_Addon_Product_Item $item ) {

	}

	/**
	 * Delete the meta in the product post that set the product as saved in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The product catalog entity manager.
	 * @param \AEPC_Addon_Product_Item $item The add-on product item entity.
	 *
	 * @return void
	 */
	public function set_product_not_saved_in_feed( ProductCatalogManager $product_catalog, \AEPC_Addon_Product_Item $item ) {

	}

	/**
	 * Perform a global delete in one query ideally for all feed status associated to the product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 *
	 * @return void
	 */
	public function remove_all_feed_status( ProductCatalogManager $product_catalog ) {

	}

	/**
	 * Detect if there are items to save yet or not
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog entity manager.
	 *
	 * @return bool
	 */
	public function there_are_items_to_save( ProductCatalogManager $product_catalog ) {
		return false;
	}
}
