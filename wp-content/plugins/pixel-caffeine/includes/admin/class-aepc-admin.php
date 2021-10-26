<?php
/**
 * The main class for all admin tasks
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelCaffeine\Admin\Exception\FBAPIException;
use PixelCaffeine\ProductCatalog\Admin\Metaboxes;
use PixelCaffeine\ProductCatalog\ConfigurationDefaults;
use PixelCaffeine\ProductCatalog\DbProvider;
use PixelCaffeine\ProductCatalog\ProductCatalogs;

/**
 * The main class for all admin tasks
 *
 * @class AEPC_Admin
 */
class AEPC_Admin {

	/** Plugin name used on menu and page titles */
	const PLUGIN_NAME = 'Pixel Caffeine';

	/**
	 * List of instances for each admin page
	 *
	 * @var array
	 */
	protected static $pages = array();

	/**
	 * The full list of settings, divided by pages
	 *
	 * @var array
	 */
	protected static $settings = array();

	/**
	 * The Facebook Adapter instance for the API
	 *
	 * @var AEPC_Facebook_Adapter
	 */
	public static $api = null;

	/**
	 * The product catalogs service instance
	 *
	 * @var ProductCatalogs
	 */
	public static $product_catalogs_service = null;

	/**
	 * The instance for the logger
	 *
	 * @var AEPC_Admin_Logger
	 */
	public static $logger = null;

	/**
	 * AEPC_Admin Constructor.
	 *
	 * @return void
	 */
	public static function init() {
		AEPC_Admin_Install::init();
		AEPC_Admin_Menu::init();
		AEPC_Admin_CA_Manager::init();
		AEPC_Admin_Notices::init();
		AEPC_Admin_Handlers::init();
		AEPC_Admin_Ajax::init();

		self::$api                      = new AEPC_Facebook_Adapter();
		self::$product_catalogs_service = new ProductCatalogs( self::$api, new DbProvider(), new ConfigurationDefaults(), new Metaboxes() );
		self::$logger                   = new AEPC_Admin_Logger();

		self::init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'admin_init', array( __CLASS__, 'redirect_to_dashboard_on_activation' ), 1 );
		add_action( 'admin_init', array( __CLASS__, 'redirect_to_dashboard_on_update' ), 1 );
		add_filter( 'plugin_action_links_' . plugin_basename( AEPC_PLUGIN_FILE ), array( __CLASS__, 'admin_plugin_settings_link' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'add_body_class' ), 99 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		if ( version_compare( PHP_VERSION, AEPC_PHP_VERSION_REQUIREMENT, '<' ) ) {
			add_action(
				'admin_notices',
				function() {
					$class = 'notice notice-error';
					// translators: %1$s: minimum PHP version supported, %2$s: current PHP version of the server's user.
					$message = sprintf( __( '<strong>Pixel Caffeine warning:</strong> Your current PHP version %2$s is not officially supported, so you might experience some issues in the plugin features. Please, contact your server admin/hosting and ask to update your PHP version to %1$s+.', 'pixel-caffeine' ), AEPC_PHP_VERSION_REQUIREMENT, PHP_VERSION );

					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
				}
			);
		}

		// Define some hooks of background processing class.
		self::$product_catalogs_service->setup();
		self::$logger->setup();
	}

	/**
	 * Include admin files conditionally.
	 *
	 * @return void
	 */
	public static function conditional_includes() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		switch ( $screen->id ) {
			default:
				break;
		}
	}

	/**
	 * Register a flag useful for redirect to dashboard after activation
	 *
	 * @return void
	 */
	public static function register_plugin_activation() {
		add_option( 'aepc_just_activated', true );
	}

	/**
	 * Redirect to dashboard on plugin activation, if plugin isn't configured yet
	 *
	 * @return void
	 */
	public static function redirect_to_dashboard_on_activation() {
		if ( get_option( 'aepc_just_activated' ) && ! self::is_plugin_configured() ) {
			delete_option( 'aepc_just_activated' );
			wp_safe_redirect( self::get_page( 'dashboard' )->get_view_url() );
			exit();
		}
	}

	/**
	 * Redirect to dashboard on plugin update
	 *
	 * @return void
	 */
	public static function redirect_to_dashboard_on_update() {
		$version = get_option( 'aepc_updated' );

		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $version ) {
			delete_option( 'aepc_updated' );
			wp_safe_redirect(
				self::get_page( 'welcome' )->get_view_url(
					array(
						'back_to' => add_query_arg( null, null ),
						'version' => $version,
					)
				)
			);
			exit();
		}
	}

	/**
	 * Add settings link into the actions of plugins list
	 *
	 * @param array $links The list of links.
	 *
	 * @return array
	 */
	public static function admin_plugin_settings_link( $links ) {
		$dashboard_link = '<a href="' . esc_url( self::get_page( 'dashboard' )->get_view_url() ) . '">' . __( 'Dashboard', 'pixel-caffeine' ) . '</a>';
		$settings_link  = '<a href="' . esc_url( self::get_page( 'general-settings' )->get_view_url() ) . '">' . __( 'Settings', 'pixel-caffeine' ) . '</a>';
		array_unshift( $links, $settings_link );
		array_unshift( $links, $dashboard_link );

		return $links;
	}

	/**
	 * Return an instance of a single page
	 *
	 * @param string $page The string identification of the page.
	 *
	 * @return AEPC_Admin_View
	 */
	public static function get_page( $page = 'dashboard' ) {
		if ( ! isset( self::$pages[ $page ] ) ) {
			self::$pages[ $page ] = new AEPC_Admin_View( $page );
		}

		return self::$pages[ $page ];
	}

	/**
	 * Get the setting of a tab
	 *
	 * @param string $tab The string identification of the tab.
	 *
	 * @return array
	 */
	public static function get_settings_of( $tab ) {
		if ( isset( self::$settings[ $tab ] ) ) {
			return self::$settings[ $tab ];
		}

		$page                   = self::get_page( $tab );
		self::$settings[ $tab ] = empty( $page ) ? array() : $page->get_settings();

		return self::$settings[ $tab ];
	}

	/**
	 * Get all id of all fields
	 *
	 * @param string $tab The string identification of the tab.
	 *
	 * @return array
	 */
	public static function get_setting_ids( $tab = '' ) {
		$settings = self::get_settings_of( $tab );
		return array_keys( $settings );
	}

	/**
	 * Return array of all fields and own defaults
	 *
	 * @param string $tab The string identification of the tab.
	 *
	 * @return array
	 */
	public static function get_setting_defaults( $tab = '' ) {
		$settings = self::get_settings_of( $tab );
		$defaults = array();

		foreach ( $settings as $option_id => $option ) {
			$defaults[ $option_id ] = ! empty( $option['default'] ) ? $option['default'] : '';
		}

		return $defaults;
	}

	/**
	 * Locate admin template file
	 *
	 * @param string $template The name of template.
	 *
	 * @return string
	 */
	public static function locate_template( $template ) {
		return PixelCaffeine()->plugin_path() . '/includes/admin/templates/' . $template;
	}

	/**
	 * Load the admin template
	 *
	 * @param string $template The name of template.
	 * @param array  $args The arguments to pass inside the template.
	 *
	 * @return void
	 */
	public static function get_template( $template, $args = array() ) {
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore
		}

		$located = self::locate_template( $template );

		file_exists( $located ) && include $located;
	}

	/**
	 * Save settings
	 *
	 * Only settings of the actual tab will be saved, settings of other tab will be ignored
	 * All checkboxes will have 'yes' or 'no' value, instead the value of other types if not passed, will be ignored.
	 *
	 * This method must be called by a bind method, triggered on some event
	 *
	 * @param array $post_data The data of the saving request.
	 *
	 * @throws Exception Throws a general exception when error during saving.
	 *
	 * @return void
	 */
	public static function save_settings( $post_data ) {

		// Get settings for page.
		$tab_args = self::get_settings_of( $post_data['tab'] );

		// Save facebook options if defined both account id and pixel id.
		if ( ! empty( $post_data['aepc_account_id'] ) && ! empty( $post_data['aepc_pixel_id'] ) ) {
			self::save_facebook_options( $post_data );

			// Remove two keys, to exclude them from general saving below.
			unset( $post_data['aepc_account_id'], $post_data['aepc_pixel_id'] );
		}

		// Save data.
		foreach ( $tab_args as $option_id => $option ) {
			if ( 'checkbox' === $option['type'] ) {
				$post_data[ $option_id ] = isset( $post_data[ $option_id ] ) ? 'yes' : 'no';
			} elseif ( ! isset( $post_data[ $option_id ] ) ) {
				// Leave unchanged if not existing.
				continue;
			}

			// Validation.
			if ( 'array' === $option['type'] ) {
				if ( ! is_array( $post_data[ $option_id ] ) ) {
					$post_data[ $option_id ] = explode( ',', $post_data[ $option_id ] );
				}
				$value = array_map( 'trim', array_filter( (array) $post_data[ $option_id ] ) );
			} else {
				$value = sanitize_text_field( $post_data[ $option_id ] );
			}

			// Check pixel id format.
			if ( 'aepc_pixel_id' === $option_id && ! AEPC_Track::validate_pixel_id( $post_data[ $option_id ] ) ) {
				AEPC_Admin_Notices::add_notice( 'error', $option_id, __( 'The Pixel ID value must contains only numbers and must be 15 digits length.', 'pixel-caffeine' ) );
				continue;
			}

			update_option( $option_id, $value );
		}

		// Throw exception if any error occurred.
		if ( AEPC_Admin_Notices::has_notice( 'error' ) ) {
			throw new Exception( __( '<strong>Some option cannot be saved</strong> Please, check errors below.', 'pixel-caffeine' ) );
		}

		// If at least once the user save the settings, set the plugin configured.
		self::set_plugin_configured();
	}

	/**
	 * Save the business ID into the DB
	 *
	 * @param string $business_id The facebook business manager ID.
	 *
	 * @return void
	 */
	public static function save_business_id( $business_id ) {
		update_option( 'aepc_business_id', $business_id );
		self::$api->set_business_id( $business_id );
	}

	/**
	 * Save the account and pixel ID defined when the user is logged in facebook
	 *
	 * @param array $post_data The $_POST data containing aepc_account_id and aepc_pixel_id.
	 *
	 * @throws FBAPIException When API fail.
	 *
	 * @return void
	 */
	public static function save_facebook_options( $post_data ) {
		$account = json_decode( $post_data['aepc_account_id'] );
		$pixel   = json_decode( $post_data['aepc_pixel_id'] );

		update_option( 'aepc_account_id', $account->id );
		set_transient( 'aepc_account_name_' . $account->id, $account->name, WEEK_IN_SECONDS );
		update_option( 'aepc_pixel_id', $pixel->id );
		set_transient( 'aepc_pixel_name_' . $pixel->id, $pixel->name, WEEK_IN_SECONDS );

		// Save the associated Business ID.
		self::$api->set_account_id( $account->id );
		self::$api->set_pixel_id( $pixel->id );
		self::save_business_id( self::$api->get_business_id_from_account_id() );

		// Enable pixel.
		update_option( 'aepc_enable_pixel', 'yes' );

		// Set the plugin as configured when the plugin is just installed.
		self::set_plugin_configured();
	}

	/**
	 * Convert the input data from request in structured array to save, used on save and edit actions
	 *
	 * @param array $post_data The raw data from request.
	 *
	 * @return array
	 * @throws Exception Throws a general Exception when there are some error notices.
	 */
	protected static function conversion_post_data_adapter( $post_data = array() ) {
		$post_data = wp_parse_args(
			$post_data,
			array(
				'event_name'                 => '',
				'event_trigger_on'           => '',
				'event_url_condition'        => 'contains',
				'event_url'                  => '',
				'event_css'                  => '',
				'event_js_event_element'     => '',
				'event_js_event_name'        => '',
				'event_standard_events'      => '',
				'event_fire_delay'           => '',
				'event_name_custom'          => '',
				'event_enable_advanced_data' => '',
				'event_custom_params'        => array(),
			)
		);

		$raw_data = array(
			'name'               => sanitize_text_field( (string) $post_data['event_name'] ),
			'trigger'            => sanitize_text_field( (string) $post_data['event_trigger_on'] ),
			'url_condition'      => sanitize_text_field( (string) $post_data['event_url_condition'] ),
			'url'                => sanitize_text_field( (string) $post_data['event_url'] ),
			'css'                => sanitize_text_field( (string) $post_data['event_css'] ),
			'js_event_element'   => sanitize_text_field( (string) $post_data['event_js_event_element'] ),
			'js_event_name'      => sanitize_text_field( (string) $post_data['event_js_event_name'] ),
			'event'              => sanitize_text_field( (string) $post_data['event_standard_events'] ),
			'delay'              => sanitize_text_field( (string) $post_data['event_fire_delay'] ),
			'custom_event_name'  => sanitize_text_field( (string) $post_data['event_name_custom'] ),
			'pass_advanced_data' => ! empty( $post_data['event_enable_advanced_data'] ),
			'value'              => sanitize_text_field( (string) $post_data['event_field_value'] ),
			'currency'           => sanitize_text_field( (string) $post_data['event_field_currency'] ),
			'content_name'       => sanitize_text_field( (string) $post_data['event_field_content_name'] ),
			'content_category'   => sanitize_text_field( (string) $post_data['event_field_content_category'] ),
			'content_ids'        => sanitize_text_field( (string) $post_data['event_field_content_ids'] ),
			'content_type'       => sanitize_text_field( (string) $post_data['event_field_content_type'] ),
			'num_items'          => sanitize_text_field( (string) $post_data['event_field_num_items'] ),
			'search_string'      => sanitize_text_field( (string) $post_data['event_field_search_string'] ),
			'status'             => sanitize_text_field( (string) $post_data['event_field_status'] ),
			'predicted_ltv'      => sanitize_text_field( (string) $post_data['event_field_predicted_ltv'] ),
			'custom_params'      => $post_data['event_custom_params'],
		);

		// Throw exception if any error occurred.
		if ( AEPC_Admin_Notices::has_notice( 'error' ) ) {
			throw new Exception( __( 'Please, check fields errors below.', 'pixel-caffeine' ) );
		}

		// Structure data.
		$track = array(
			'name'             => $raw_data['name'],
			'trigger'          => $raw_data['trigger'],
			'url_condition'    => $raw_data['url_condition'],
			'url'              => $raw_data['url'],
			'css'              => $raw_data['css'],
			'js_event_element' => $raw_data['js_event_element'],
			'js_event_name'    => $raw_data['js_event_name'],
			'event'            => $raw_data['event'],
			'delay'            => $raw_data['delay'],
			'params'           => array(),
			'custom_params'    => array(),
		);

		// Get custom name for the event name if it is custom one.
		if ( AEPC_Track::is( 'custom', $raw_data['event'] ) ) {
			$track['event'] = $raw_data['custom_event_name'];
		}

		// Data.
		if ( $raw_data['pass_advanced_data'] ) {
			$fields = AEPC_Track::get_standard_event_fields( $raw_data['event'] );

			// Set data for standard events.
			if ( $fields ) {

				foreach ( $fields as $field ) {
					if ( 'content_ids' === $field && ! empty( $raw_data[ $field ] ) ) {
						$raw_data[ $field ] = array_map( 'trim', explode( ',', $raw_data[ $field ] ) );
					}

					if ( ! empty( $raw_data[ $field ] ) ) {
						$track['params'][ $field ] = $raw_data[ $field ];
					}
				}
			}

			// Add custom parameters.
			if ( ! empty( $raw_data['custom_params'] ) ) {
				foreach ( $raw_data['custom_params'] as $param ) {
					if ( ! empty( $param['key'] ) && ! empty( $param['value'] ) ) {
						$track['custom_params'][ $param['key'] ] = $param['value'];
					}
				}
			}
		}

		return $track;
	}

	/**
	 * Save the conversions events added by user in admin page
	 *
	 * This method must be called by a bind method, triggered on some event
	 *
	 * @param array $post_data The events data for the saving request.
	 *
	 * @throws Exception Throws a general exception during the update.
	 *
	 * @return void
	 */
	public static function save_events( $post_data ) {
		$events = AEPC_Track::get_conversions_events();
		update_option( 'aepc_conversions_events', array_merge( $events, array( self::conversion_post_data_adapter( $post_data ) ) ) );
	}

	/**
	 * Edit a conversion event
	 *
	 * This method must be called by a bind method, triggered on some event
	 *
	 * @param array $post_data The events data for the saving request.
	 *
	 * @throws Exception Throws a general exception during the update.
	 *
	 * @return void
	 */
	public static function edit_event( $post_data ) {
		$event_id = intval( $post_data['event_id'] );
		$events   = AEPC_Track::get_conversions_events();

		if ( isset( $events[ $event_id ] ) ) {
			$events[ $event_id ] = self::conversion_post_data_adapter( $post_data );

			// Fix value of url/css fields.
			if ( 'css_selector' === $events[ $event_id ]['trigger'] ) {
				$events[ $event_id ]['url_condition'] = 'contains';
				$events[ $event_id ]['url']           = '';
			} else {
				$events[ $event_id ]['css'] = '';
			}

			update_option( 'aepc_conversions_events', $events );
		}
	}

	/**
	 * Delete conversion event
	 *
	 * @param int $id The event ID to remove.
	 *
	 * @return void
	 */
	public static function delete_event( $id ) {
		$events = AEPC_Track::get_conversions_events();
		if ( ! empty( $events[ $id ] ) ) {
			unset( $events[ $id ] );
			update_option( 'aepc_conversions_events', $events );
		}
	}

	/**
	 * Check if plugin is been configured by user or not
	 *
	 * @return bool
	 */
	public static function is_plugin_configured() {
		return 'yes' === get_option( 'aepc_configured' );
	}

	/**
	 * Set plugin configured by adding an option set to yes
	 *
	 * @return void
	 */
	public static function set_plugin_configured() {
		add_option( 'aepc_configured', 'yes' );
	}

	/**
	 * Delete the flag option set if plugin is configured or not
	 *
	 * @return void
	 */
	public static function set_plugin_not_configured() {
		delete_option( 'aepc_configured' );
	}

	/**
	 * Enqueue styles and scripts in admin
	 *
	 * @param string $hook_suffix The hook suffix of the page where enqueue the assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {

		// Register common assets.
		wp_register_style( 'aepc-menu', PixelCaffeine()->build_url( 'wpcommon.css' ), array(), PixelCaffeine()->version );
		wp_enqueue_style( 'aepc-menu' );

		// General settings admin page.
		if ( in_array( $hook_suffix, array( AEPC_Admin_Menu::$hook_page ), true ) ) {

			// Add wp thickbox library to launch plugin information popup on ecommerce box.
			add_thickbox();

			wp_register_script( 'aepc-admin-settings', PixelCaffeine()->build_url( 'admin.js' ), array( 'jquery', 'wp-util' ), PixelCaffeine()->version, true );
			wp_enqueue_script( 'aepc-admin-settings' );

			// Script util arguments.
			$script_args = array(
				'ajax_url'                              => admin_url( 'admin-ajax.php' ),
				'unsaved'                               => __( 'You have unsaved changes on this page. Do you want to leave this page and discard your changes or stay on this page?', 'pixel-caffeine' ),
				'switch_unsaved'                        => __( 'You need to save!', 'pixel-caffeine' ),

				'ajax_form_saving'                      => __( 'Saving...', 'pixel-caffeine' ),

				'tooltip_clipboard_copied'              => __( 'Copied!', 'pixel-caffeine' ),
				'tooltip_clipboard_no_support'          => __( 'No support :(', 'pixel-caffeine' ),
				'tooltip_clipboard_mac_copy_suggestion' => __( 'Press ⌘-{{ key }} to copy', 'pixel-caffeine' ),
				'tooltip_clipboard_win_copy_suggestion' => __( 'Press Ctrl-{{ key }} to copy', 'pixel-caffeine' ),
				'tooltip_clipboard_mac_cut_suggestion'  => __( 'Press ⌘-{{ key }} to cut', 'pixel-caffeine' ),
				'tooltip_clipboard_win_cut_suggestion'  => __( 'Press Ctrl-{{ key }} to cut', 'pixel-caffeine' ),

				'filter_any'                            => __( 'any', 'pixel-caffeine' ),
				'filter_custom_field_placeholder'       => __( 'Write the key or select from below', 'pixel-caffeine' ),
				'filter_saving'                         => __( 'Saving...', 'pixel-caffeine' ),
				'filter_no_data_error'                  => __( '<strong>Can\'t add filter</strong> You have to select an event type', 'pixel-caffeine' ),
				'filter_no_condition_error'             => __( '<strong>Can\'t add filter</strong> You have to define at least one condition', 'pixel-caffeine' ),

				'fb_option_account_id_placeholder'      => __( 'Select an account ID', 'pixel-caffeine' ),
				'fb_option_no_account'                  => __( 'No Ad account found', 'pixel-caffeine' ),
				'fb_option_no_pixel'                    => __( 'No pixel found', 'pixel-caffeine' ),
				'fb_option_no_product_feeds'            => __( 'No products feeds found', 'pixel-caffeine' ),

				'highcharts_range_today'                => __( 'Today', 'pixel-caffeine' ),
				'highcharts_range_yesterday'            => __( 'Yesterday', 'pixel-caffeine' ),
				'highcharts_range_2days'                => __( '2 Days', 'pixel-caffeine' ),
				'highcharts_range_7days'                => __( '7 Days', 'pixel-caffeine' ),
				'highcharts_range_14days'               => __( '14 Days', 'pixel-caffeine' ),
			);

			// Add ajax utils.
			foreach ( AEPC_Admin_Ajax::$ajax_actions as $action ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$tab = ! empty( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';

				// Hooks for pages.
				$page_hooks = array(
					'dashboard'        => array(
						'get_pixel_stats',
					),

					'custom-audiences' => array(
						'get_custom_fields',
						'get_languages',
						'get_device_types',
						'get_categories',
						'get_tags',
						'get_posts',
						'get_dpa_params',
						'get_filter_statement',
						'get_currencies',
					),

					'conversions'      => array(),

					'general-settings' => array(
						'get_account_ids',
						'get_pixel_ids',
						'get_custom_fields',
					),

					'product-catalog'  => array(
						'get_posts',
					),
				);

				if (
					in_array( $action, array_merge( $page_hooks['dashboard'], $page_hooks['conversions'], $page_hooks['custom-audiences'], $page_hooks['general-settings'] ), true )
					&& ( ! isset( $page_hooks[ $tab ] ) || ! in_array( $action, $page_hooks[ $tab ], true ) )
				) {
					continue;
				}

				$script_args['actions'][ $action ] = array(
					'name'  => 'aepc_' . $action,
					'nonce' => wp_create_nonce( $action ),
				);
			}

			wp_localize_script( 'aepc-admin-settings', 'aepc_admin', $script_args );

			// Register assents for the views.
			wp_register_style( 'aepc-admin', PixelCaffeine()->build_url( 'admin.css' ), array(), PixelCaffeine()->version );
			wp_enqueue_style( 'aepc-admin' );

		}
	}

	/**
	 * Add a general class to the body
	 *
	 * @param string $classes The additional classes to add into the return string.
	 *
	 * @return string
	 */
	public static function add_body_class( $classes ) {
		return $classes . ' pixel-caffeine ';
	}

	/**
	 * Get the pixel statistics for all hours between today and two weeks ago
	 *
	 * @return array|WP_Error
	 */
	public static function get_pixel_stats_sets() {
		try {
			// Get pixel stats from facebook.
			$stats = get_transient( 'aepc_pixel_stats' );

			if ( false === $stats ) {
				$fb    = self::$api;
				$stats = $fb->get_pixel_stats();
				set_transient( 'aepc_pixel_stats', $stats, HOUR_IN_SECONDS );
			}

			$timezone = null;

			// Convert array to work better after.
			foreach ( $stats as $i => $stat ) {
				$stat->timestamp = new DateTime( $stat->start_time );

				$stats[ $stat->timestamp->format( DATE_ISO8601 ) ] = $stat->data[0]->count;
				$timezone = $stat->timestamp->getTimezone();
				unset( $stats[ $i ] );
			}

			$counter = new DateTime( '2 weeks ago', $timezone );
			$counter->setTime( 0, 0 );
			$tomorrow = new DateTime( 'tomorrow', $timezone );
			$counters = array();

			do {
				$counters[] = array(
					$counter->getTimestamp() * 1000,
					isset( $stats[ $counter->format( DATE_ISO8601 ) ] ) ? $stats[ $counter->format( DATE_ISO8601 ) ] : 0,
				);

				$counter->modify( '+1 hour' );
			} while ( $counter < $tomorrow );

			return $counters;

		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * SIDEBAR TOOLS
	 */

	/**
	 * Fetch the banners for the sidebar from a remote JSON file
	 *
	 * @return stdClass|null|false
	 */
	public static function fetch_sidebar() {
		$sidebar = get_transient( 'aepc_sidebar' );

		if ( false === $sidebar ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$response = wp_remote_get( 'https://adespresso.com/wp-content/uploads/pixel-caffeine-sidebar.json' );

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$body    = wp_remote_retrieve_body( $response );
				$sidebar = json_decode( $body );

				set_transient( 'aepc_sidebar', $sidebar, 6 * HOUR_IN_SECONDS );
			} else {
				return null;
			}
		}

		return $sidebar;
	}

	/**
	 * Fetch the blog posts from official site for sidebar
	 *
	 * @param string|array|object $args The query configuration for sidebar posts fetching.
	 *
	 * @return array
	 */
	public static function fetch_sidebar_posts( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'feed'  => '',
				'items' => 3,
			)
		);

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $args['feed'] ) ) {
			$rss = fetch_feed( $args['feed'] );

			if ( is_wp_error( $rss ) ) {
				if ( is_admin() || current_user_can( 'manage_ads' ) ) {
					return array(
						/* translators: %s: the error messages during the sidebar info fetching */
						'error' => '<p>' . sprintf( __( '<strong>RSS Error</strong>: %s', 'pixel-caffeine' ), $rss->get_error_message() ) . '</p>',
					);
				}

				return array();
			}

			if ( ! $rss->get_item_quantity() ) {
				$rss->__destruct();
				unset( $rss );

				return array(
					'error' => '<p>' . __( 'An error has occurred, which probably means the feed is down. Try again later.', 'pixel-caffeine' ) . '</p>',
				);
			}

			$posts = array();

			foreach ( (array) $rss->get_items( 0, $args['items'] ) as $item ) {
				/**
				 * $item is a SimplePie_Item instance
				 *
				 * @var SimplePie_Item $item
				 */

				$posts[] = array(
					'link'        => $item->get_link(),
					'title'       => $item->get_title(),
					'description' => $item->get_description(),
					'date'        => $item->get_date(),
				);
			}

			set_transient( 'aepc_rss_posts', $posts, 12 * HOUR_IN_SECONDS );

		} else {
			$posts = (array) get_transient( 'aepc_rss_posts' );
		}

		return array_filter( $posts );
	}

	/**
	 * Clear the transients saved on db
	 *
	 * @return void
	 */
	public static function clear_transients() {
		global $wpdb;

		$tranients_to_clear = array(
			'aepc_account_name_%',
			'aepc_pixel_name_%',
			'aepc_pixel_stats',
			'aepc_sidebar',
			'aepc_rss_posts',
			'aepc_fb_users',
			'aepc_account_name_%',
			'aepc_pixel_name_%',
			'aepc_event_%',
		);

		// Convert wildcard in complete transient name, in order to use the function delete_transient after.
		foreach ( $tranients_to_clear as $i => &$transient ) {
			if ( false !== strpos( $transient, '%' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$transients_found = $wpdb->get_col(
					$wpdb->prepare(
						"
SELECT DISTINCT REPLACE( REPLACE( option_name, '_transient_timeout_', '' ), '_transient_', '' )
FROM {$wpdb->options}
WHERE option_name LIKE %s
",
						'%' . $transient
					)
				);

				unset( $tranients_to_clear[ $i ] );

				foreach ( $transients_found as $transient_found ) {
					$tranients_to_clear[] = $transient_found;
				}
			}
		}

		// Delete transients.
		foreach ( $tranients_to_clear as $transient ) {
			delete_transient( $transient );
		}

		do_action( 'aepc_delete_transients' );
	}

	/**
	 * Clear the transients saved on db
	 *
	 * @return void
	 */
	public static function reset_fb_connection() {
		self::$api->set_disconnected();

		delete_option( 'aepc_fb_access_expired' );
		delete_option( 'aepc_fb_uuid' );

		do_action( 'aepc_reset_fb_connection' );
	}

}
