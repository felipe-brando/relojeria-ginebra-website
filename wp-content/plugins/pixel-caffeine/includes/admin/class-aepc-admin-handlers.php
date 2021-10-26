<?php
/**
 * All admin request handlers
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Logs\Entity\Log;
use PixelCaffeine\Logs\LogRepository;
use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\Dictionary\FeedSaver;
use PixelCaffeine\ProductCatalog\Entity\ProductCatalog;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class for all admin request handlers
 *
 * @class AEPC_Admin_Handlers
 */
class AEPC_Admin_Handlers {

	/**
	 * AEPC_Admin_Handlers Constructor.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'admin_hooks' ) );
	}

	/**
	 * Hook actions on admin_init
	 *
	 * @return void
	 */
	public static function admin_hooks() {
		// Fb connect/disconnect - Must be run before connect of Facebook adapter.
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'pixel_disconnect' ), 4 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'save_facebook_options' ), 4 );

		// Conversions/events.
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'save_settings' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'save_events' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'edit_event' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'delete_event' ), 5 );

		// CA management.
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'save_audience' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'edit_audience' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'duplicate_audience' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'delete_audience' ), 5 );

		// Product Catalogs.
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'save_product_catalog' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'update_product_catalog' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'delete_product_catalog_feed' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'refresh_product_catalog_feed' ), 5 );
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'save_product_feed_refresh_interval' ), 5 );

		// Tools.
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'clear_transients' ), 5 );

		// Logs.
		add_action( 'load-' . AEPC_Admin_Menu::$hook_page, array( __CLASS__, 'download_log_report' ), 5 );
	}

	/**
	 * Simply delete the option saved with pixel ID
	 *
	 * @return void
	 */
	public static function pixel_disconnect() {
		$screen = get_current_screen();

		if (
			empty( $screen->id )
			|| AEPC_Admin_Menu::$hook_page !== $screen->id
			|| 'pixel-disconnect' !== filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING ), 'pixel_disconnect' )
		) {
			return;
		}

		// Delete the option.
		delete_option( 'aepc_pixel_id' );

		// Send success notice.
		AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Pixel ID disconnected.', 'pixel-caffeine' ) );

		// If all good, redirect in the same page.
		self::redirect_to( remove_query_arg( array( 'action', '_wpnonce' ) ) );
	}

	/**
	 * Save the account id and pixel id
	 *
	 * @return bool
	 */
	public static function save_facebook_options() {
		if (
			'aepc_save_facebook_options' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'save_facebook_options' )
		) {
			return false;
		}

		try {

			if ( empty( $_POST['aepc_account_id'] ) ) {
				AEPC_Admin_Notices::add_notice( 'error', 'account_id', __( 'Set the account ID', 'pixel-caffeine' ) );
			}

			if ( empty( $_POST['aepc_pixel_id'] ) ) {
				AEPC_Admin_Notices::add_notice( 'error', 'pixel_id', __( 'Set the pixel ID', 'pixel-caffeine' ) );
			}

			if ( AEPC_Admin_Notices::has_notice( 'error' ) ) {
				AEPC_Admin_Notices::add_notice( 'error', 'main', __( 'Please, check again all fields value.', 'pixel-caffeine' ) );
				return false;
			}

			AEPC_Admin::save_facebook_options( stripslashes_deep( $_POST ) );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Facebook Ad Account connected successfully.', 'pixel-caffeine' ) );

			// If all good, redirect in the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( remove_query_arg( 'ref' ) );
			}

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
		}

		return false;
	}

	/**
	 * General method for all standard settings, defined on "settings" directory, triggered when a page form is submitted
	 *
	 * @return void
	 */
	public static function save_settings() {
		$screen = get_current_screen();

		if (
			empty( $screen->id )
			|| AEPC_Admin_Menu::$hook_page !== $screen->id
			|| ! current_user_can( 'manage_ads' )
			|| empty( $_POST )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'save_general_settings' )
		) {
			return;
		}

		try {

			// Save.
			AEPC_Admin::save_settings( (array) wp_unslash( $_POST ) );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Settings saved properly.', 'pixel-caffeine' ) );

			// If all good, redirect in the same page.
			self::redirect_to( remove_query_arg( 'ref' ) );
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', __( 'Please, check again all fields value.', 'pixel-caffeine' ) );
		}
	}

	/**
	 * Save the conversions events added by user in admin page
	 *
	 * @return bool
	 */
	public static function save_events() {
		if (
			empty( $_POST )
			|| 'aepc_save_tracking_conversion' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'save_tracking_conversion' )
		) {
			return false;
		}

		try {

			// Save events.
			AEPC_Admin::save_events( (array) wp_unslash( $_POST ) );

			// Send success notice.
			/* translators: 1. opening tag for the link to https://developers.facebook.com/docs/facebook-pixel/using-the-pixel#verify, 2: closing tag */
			AEPC_Admin_Notices::add_notice( 'success', 'main', sprintf( __( '<strong>Conversion event added properly!</strong> Follow the instructions on %1$sthis link%2$s to verify if the pixel tracking event you added works properly.', 'pixel-caffeine' ), '<a href="https://developers.facebook.com/docs/facebook-pixel/using-the-pixel#verify">', '</a>' ) );

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
			return false;
		}
	}

	/**
	 * Edit a conversion event
	 *
	 * @return bool
	 */
	public static function edit_event() {
		if (
			empty( $_POST )
			|| 'aepc_edit_tracking_conversion' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! isset( $_POST['event_id'] )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'edit_tracking_conversion' )
		) {
			return false;
		}

		try {

			// Edit event.
			AEPC_Admin::edit_event( (array) wp_unslash( $_POST ) );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Conversion changed successfully.', 'pixel-caffeine' ) );

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
			return false;
		}
	}

	/**
	 * Delete conversion event
	 *
	 * @return void
	 */
	public static function delete_event() {
		$screen = get_current_screen();

		if (
			empty( $screen->id )
			|| AEPC_Admin_Menu::$hook_page !== $screen->id
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING ), 'delete_tracking_conversion' )
		) {
			return;
		}

		// Delete event.
		AEPC_Admin::delete_event( filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT ) );

		// Send success notice.
		AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Configuration removed properly!!', 'pixel-caffeine' ) );

		// Redirect to the same page.
		self::redirect_to( remove_query_arg( array( 'id', '_wpnonce' ) ) );
	}

	/**
	 * CA MAnagement
	 */

	/**
	 * Add new custom audience
	 *
	 * @return bool
	 */
	public static function save_audience() {
		if (
			'aepc_add_custom_audience' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'add_custom_audience' )
		) {
			return false;
		}

		try {

			// Save custom audience.
			AEPC_Admin_CA_Manager::save( (array) wp_unslash( $_POST ) );

			// Send success notice.
			/* translators: 1. opening tag for the link to user facebook ad account to the custom audience saved, 2: closing tag */
			AEPC_Admin_Notices::add_notice( 'success', 'main', sprintf( __( '<strong>New custom audience added!</strong> You will find this new custom audience also in %1$syour facebook ad account%2$s.', 'pixel-caffeine' ), '<a href="https://www.facebook.com/ads/manager/audiences/manage/?act=' . AEPC_Admin::$api->get_account_id() . '" target="_blank">', '</a>' ) );

			// If all good, redirect in the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( remove_query_arg( 'paged' ) );
			}

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
		}

		return false;
	}

	/**
	 * Edit a conversion event
	 *
	 * @return bool
	 */
	public static function edit_audience() {
		if (
			! isset( $_POST['ca_id'] )
			|| 'aepc_edit_custom_audience' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'edit_custom_audience' )
		) {
			return false;
		}

		try {

			// Edit event.
			AEPC_Admin_CA_Manager::edit( (array) wp_unslash( $_POST ) );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Custom audience changed successfully.', 'pixel-caffeine' ) );

			// Redirect to the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( add_query_arg( null, null ) );
			}

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
		}

		return false;
	}

	/**
	 * Duplicate custom audience event
	 *
	 * @return bool
	 */
	public static function duplicate_audience() {
		if (
			'aepc_duplicate_custom_audience' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'duplicate_custom_audience' )
		) {
			return false;
		}

		try {

			// Delete event.
			AEPC_Admin_CA_Manager::duplicate( (array) wp_unslash( $_POST ) );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', __( '<strong>Custom audience duplicated</strong> It is duplicated also on your facebook Ad account.', 'pixel-caffeine' ) );

			// Redirect to the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( add_query_arg( null, null ) );
			}

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );

			return false;
		}
	}

	/**
	 * Delete custom audience event
	 *
	 * @return void
	 */
	public static function delete_audience() {
		$screen = get_current_screen();

		if (
			empty( $screen->id )
			|| AEPC_Admin_Menu::$hook_page !== $screen->id
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING ), 'delete_custom_audience' )
			|| empty( $_GET['id'] )
		) {
			return;
		}

		try {
			// Delete event.
			AEPC_Admin_CA_Manager::delete( intval( $_GET['id'] ) );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', __( '<strong>Custom audience removed</strong> It was removed also on your facebook Ad account.', 'pixel-caffeine' ) );
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', '<strong>' . __( 'Unable to delete', 'pixel-caffeine' ) . '</strong> ' . $e->getMessage() );
		}

		// Redirect to the same page.
		self::redirect_to( remove_query_arg( array( 'id', '_wpnonce' ) ) );
	}

	/**
	 * Product Catalogs
	 */

	/**
	 * Adjust values for DB
	 *
	 * @param array $post_data The data of the product catalog.
	 *
	 * @return array
	 */
	protected static function process_product_catalog_post_data( array $post_data ) {
		$service  = AEPC_Admin::$product_catalogs_service;
		$defaults = $service->get_defaults();

		$post_data = wp_parse_args(
			wp_unslash( $post_data ),
			array(
				Configuration::OPTION_FEED_NAME   => $defaults->get( Configuration::OPTION_FEED_NAME ),
				Configuration::OPTION_FEED_FORMAT => $defaults->get( Configuration::OPTION_FEED_FORMAT ),
				Configuration::OPTION_FEED_CONFIG => array(),
			)
		);

		// Convert all array types.
		foreach ( $post_data[ Configuration::OPTION_FEED_CONFIG ] as &$value ) {
			if ( is_string( $value ) && strpos( $value, ',' ) !== false ) {
				$value = explode( ',', $value );
			}
		}

		// Check for checkboxes.
		foreach ( array(
			Configuration::OPTION_FEED_CONFIG => array(
				Configuration::OPTION_ENABLE_BACKGROUND_SAVE,
				Configuration::OPTION_SKU_FOR_ID,
				Configuration::OPTION_FILTER_ON_SALE,
				Configuration::OPTION_NO_VARIATIONS,
				Configuration::OPTION_FB_ENABLE,
			),
		) as $group => $options ) {
			if ( ! is_array( $options ) ) {
				$post_data[ $options ] = isset( $post_data[ $options ] ) && 'yes' === $post_data[ $options ];
			}

			foreach ( $options as $option ) {
				$post_data[ $group ][ $option ] = isset( $post_data[ $group ][ $option ] ) && 'yes' === $post_data[ $group ][ $option ];
			}
		}

		// Remove eventual empty item in the google category option value.
		$post_data[ Configuration::OPTION_FEED_CONFIG ][ Configuration::OPTION_GOOGLE_CATEGORY ] = array_filter( $post_data[ Configuration::OPTION_FEED_CONFIG ][ Configuration::OPTION_GOOGLE_CATEGORY ] );

		// Get schedule options in base of the action.
		$schedule_action                                = $post_data[ Configuration::OPTION_FEED_CONFIG ][ Configuration::OPTION_FB_ACTION ];
		$post_data[ Configuration::OPTION_FEED_CONFIG ] = array_merge(
			$post_data[ Configuration::OPTION_FEED_CONFIG ],
			$post_data[ Configuration::OPTION_FEED_CONFIG ][ $schedule_action ]
		);

		return $post_data;
	}

	/**
	 * Add new product catalog
	 *
	 * @return AEPC_Admin_Response
	 * @phpcs:disable Squiz.Commenting.FunctionCommentThrowTag
	 */
	public static function save_product_catalog() {
		if (
			'aepc_save_product_catalog' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'save_product_catalog' )
		) {
			return new AEPC_Admin_Response( false );
		}

		try {
			$service   = AEPC_Admin::$product_catalogs_service;
			$post_data = self::process_product_catalog_post_data( filter_input( INPUT_POST, 'product_catalog', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY ) );

			// Create entity object.
			$entity = new ProductCatalog();
			$entity->set_id( $post_data[ Configuration::OPTION_FEED_NAME ] );
			$entity->set_format( $post_data[ Configuration::OPTION_FEED_FORMAT ] );
			$entity->set_config( $post_data[ Configuration::OPTION_FEED_CONFIG ] );

			// Save create catalog and start feed file saving.
			$response = $service->create_product_catalog( $entity );

			if ( is_wp_error( $response ) ) {
				/* translators: %s: is the error message from the product catalog creation */
				throw new Exception( sprintf( __( 'Unable to generate the feed: %s', 'pixel-caffeine' ), $response->get_error_message() ) );
			}

			$error_title = '<strong>' . __( 'Product catalog saved!', 'pixel-caffeine' ) . '</strong>';

			if ( $service->get_product_catalog( $entity->get_id() )->must_be_saved_in_background() ) {
				AEPC_Admin_Notices::add_notice( 'success', 'main', $error_title . ' ' . __( 'The system is saving your feed in background. Feel free to navigate away and we will keep you updated in the box below.', 'pixel-caffeine' ) );
			} else {
				AEPC_Admin_Notices::add_notice( 'success', 'main', $error_title );
			}

			// If all good, redirect in the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( remove_query_arg( 'paged' ) );
			}

			return new AEPC_Admin_Response(
				true,
				array(
					'background_saving' => $service->get_product_catalog( $entity->get_id() )->must_be_saved_in_background(),
				)
			);
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
			return new AEPC_Admin_Response( false );
		}
	}

	/**
	 * Add new product catalog
	 *
	 * @return bool
	 */
	public static function save_product_feed_refresh_interval() {
		if (
			'aepc_save_product_feed_refresh_interval' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'save_product_feed_refresh_interval' )
		) {
			return false;
		}

		try {
			$service = AEPC_Admin::$product_catalogs_service;

			// Create entity object.
			$product_catalog = $service->get_product_catalog( filter_input( INPUT_POST, 'product_catalog_id', FILTER_SANITIZE_STRING ) );
			$product_catalog->configuration()->set( Configuration::OPTION_REFRESH_CYCLE, intval( filter_input( INPUT_POST, 'cycle', FILTER_SANITIZE_STRING ) ) );
			$product_catalog->configuration()->set( Configuration::OPTION_REFRESH_CYCLE_TYPE, filter_input( INPUT_POST, 'cycle_type', FILTER_SANITIZE_STRING ) );

			// Update DB and feed.
			$product_catalog->update();
			$product_catalog->unschedule_job();

			AEPC_Admin_Notices::add_notice( 'success', 'main', __( 'Refresh option updated!', 'pixel-caffeine' ) );

			// If all good, redirect in the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( remove_query_arg( 'paged' ) );
			}

			return true;
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
		}

		return false;
	}

	/**
	 * Update a product catalog
	 *
	 * @return AEPC_Admin_Response
	 */
	public static function update_product_catalog() {
		if (
			'aepc_update_product_catalog' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'update_product_catalog' )
		) {
			return new AEPC_Admin_Response( false );
		}

		try {
			$service   = AEPC_Admin::$product_catalogs_service;
			$post_data = self::process_product_catalog_post_data( filter_input( INPUT_POST, 'product_catalog', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY ) );

			// Get product catalog.
			$product_catalog = $service->get_product_catalog( $post_data['name'] );

			// Update entity.
			$product_catalog->get_entity()->set_format( $post_data[ Configuration::OPTION_FEED_FORMAT ] );
			$product_catalog->get_entity()->set_config( $post_data[ Configuration::OPTION_FEED_CONFIG ] );

			// Save product catalog.
			$service->update_product_catalog( $product_catalog );

			// Send success notice.
			AEPC_Admin_Notices::add_notice( 'success', 'main', '<strong>' . __( 'Product catalog updated!', 'pixel-caffeine' ) . '</strong>' );

			// If all good, redirect in the same page.
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				self::redirect_to( remove_query_arg( 'paged' ) );
			}

			return new AEPC_Admin_Response(
				true,
				array(
					'background_saving' => $product_catalog->must_be_saved_in_background(),
				)
			);
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
			return new AEPC_Admin_Response( false );
		}
	}

	/**
	 * Refresh the product catalog feed
	 *
	 * @return AEPC_Admin_Response
	 */
	public static function refresh_product_catalog_feed() {
		if (
			'aepc_refresh_product_catalog_feed' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'refresh_product_catalog_feed' )
		) {
			return new AEPC_Admin_Response( false );
		}

		try {
			$service  = AEPC_Admin::$product_catalogs_service;
			$defaults = $service->get_defaults();

			$post_data = wp_parse_args(
				wp_unslash( $_POST ),
				array(
					Configuration::OPTION_FEED_NAME => $defaults->get( Configuration::OPTION_FEED_NAME ),
				)
			);

			$product_catalog = AEPC_Admin::$product_catalogs_service->get_product_catalog( $post_data['name'] );

			// Then generate again with background processing.
			$service->generate_feed( $product_catalog, FeedSaver::REFRESH_CONTEXT );

			$error_title = '<strong>' . __( 'Product catalog saved!', 'pixel-caffeine' ) . '</strong>';

			if ( $product_catalog->must_be_saved_in_background() ) {
				AEPC_Admin_Notices::add_notice( 'success', 'main', $error_title . ' ' . __( 'The system is saving your feed in background. Feel free to navigate away and we will keep you updated in the box below.', 'pixel-caffeine' ) );
			} else {
				AEPC_Admin_Notices::add_notice( 'success', 'main', $error_title );
			}

			return new AEPC_Admin_Response(
				true,
				array(
					'background_saving' => $product_catalog->must_be_saved_in_background(),
				)
			);
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
		}

		return new AEPC_Admin_Response( false );
	}

	/**
	 * Delete the product catalog feed
	 *
	 * @return AEPC_Admin_Response
	 */
	public static function delete_product_catalog_feed() {
		if (
			'aepc_delete_product_catalog_feed' !== filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING ), 'delete_product_catalog_feed' )
		) {
			return new AEPC_Admin_Response( false );
		}

		try {
			$defaults = AEPC_Admin::$product_catalogs_service->get_defaults();

			$post_data = wp_parse_args(
				wp_unslash( $_POST ),
				array(
					Configuration::OPTION_FEED_NAME => $defaults->get( Configuration::OPTION_FEED_NAME ),
				)
			);

			$service         = AEPC_Admin::$product_catalogs_service;
			$product_catalog = $service->get_product_catalog( $post_data['name'] );
			$service->delete_product_catalog( $product_catalog );

			AEPC_Admin_Notices::add_notice( 'success', 'main', '<strong>' . __( 'Feed deleted correctly!', 'pixel-caffeine' ) . '</strong>' );

			return new AEPC_Admin_Response( true );
		} catch ( Exception $e ) {
			AEPC_Admin_Notices::add_notice( 'error', 'main', $e->getMessage() );
		}

		return new AEPC_Admin_Response( false );
	}

	/**
	 * Clear transients used for facebook api requests
	 *
	 * @return void
	 */
	public static function clear_transients() {
		if (
			'aepc_clear_transients' !== filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING ), 'clear_transients' )
		) {
			return;
		}

		// Clear the transients.
		AEPC_Admin::clear_transients();

		// Redirect to the same page.
		self::redirect_to( remove_query_arg( array( 'action', '_wpnonce' ) ) );
	}

	/**
	 * Download the log file with the log report
	 *
	 * @return void
	 */
	public static function download_log_report() {
		if (
			'aepc_download_log_report' !== filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING )
			|| ! current_user_can( 'manage_ads' )
			|| ! wp_verify_nonce( filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING ), 'aepc_download_log_report' )
		) {
			return;
		}

		$log_repository = new LogRepository();
		$log            = $log_repository->find_by_id( intval( filter_input( INPUT_GET, 'log', FILTER_SANITIZE_NUMBER_INT ) ) );

		if ( $log instanceof Log ) {
			ob_start();

			printf( "---- Report: %s ----\n\n", esc_html( $log->get_date()->format( 'c' ) ) );
			printf( "Exception: %s\n\n", esc_html( $log->get_exception() ) );
			printf( "--------------------\n\n" );
			printf( "Message: %s\n\n", esc_html( $log->get_message() ) );
			printf( "---- Context ----\n\n" );
			echo( wp_json_encode( $log->get_context() ) ?: '{}' );

			$content = ob_get_clean();

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename=report.log' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: Keep-Alive' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . strlen( (string) $content ) );

			// phpcs:ignore
			echo $content;
		}

		exit;
	}

	/**
	 * Used on requests, to redirect to a page after end request
	 *
	 * @param string $to The link where redirect to.
	 *
	 * @return void
	 */
	protected static function redirect_to( $to ) {
		//phpcs:ignore WordPress.Security.NonceVerification
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX || isset( $_GET['ajax'] ) && 1 === (int) $_GET['ajax'] ) {
			wp_send_json_success();
		}

		wp_safe_redirect( $to );
		exit();
	}

}
