<?php

use Automattic\Jetpack\Connection\Secrets;

class Jetpack_Keyring_Service_Helper {
	/**
	 * @var Jetpack_Keyring_Service_Helper
	 **/
	private static $instance = null;

	/**
	 * Whether the `sharing` page is registered.
	 *
	 * @var bool
	 */
	private static $is_sharing_page_registered = false;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Jetpack_Keyring_Service_Helper;
		}

		return self::$instance;
	}

	public static $SERVICES = array(
		'facebook' => array(
			'for' => 'publicize'
		),
		'twitter' => array(
			'for' => 'publicize'
		),
		'linkedin' => array(
			'for' => 'publicize'
		),
		'tumblr' => array(
			'for' => 'publicize'
		),
		'path' => array(
			'for' => 'publicize'
		),
		'google_plus' => array(
			'for' => 'publicize'
		),
		'google_site_verification' => array(
			'for' => 'other'
		)
	);

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'register_sharing_page' ) );

		add_action( 'load-settings_page_sharing', array( __CLASS__, 'admin_page_load' ), 9 );
	}

	/**
	 * We need a `sharing` page to be able to connect and disconnect services.
	 */
	public static function register_sharing_page() {
		if ( self::$is_sharing_page_registered ) {
			return;
		}

		self::$is_sharing_page_registered = true;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $_registered_pages;

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$hookname = get_plugin_page_hookname( 'sharing', 'options-general.php' );
		add_action( $hookname, array( __CLASS__, 'admin_page_load' ) );
		$_registered_pages[ $hookname ] = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	function get_services( $filter = 'all' ) {
		$services = array();

		if ( 'all' === $filter ) {
			return $services;
		} else {
			$connected_services = array();
			foreach ( $services as $service => $empty ) {
				$connections = $this->get_connections( $service );
				if ( $connections ) {
					$connected_services[ $service ] = $connections;
				}
			}
			return $connected_services;
		}
	}

	/**
	 * Gets a URL to the public-api actions. Works like WP's admin_url
	 *
	 * @param string $service Shortname of a specific service.
	 *
	 * @return URL to specific public-api process
	 */
	// on WordPress.com this is/calls Keyring::admin_url
	static function api_url( $service = false, $params = array() ) {
		/**
		 * Filters the API URL used to interact with WordPress.com.
		 *
		 * @since 2.0.0
		 *
		 * @param string https://public-api.wordpress.com/connect/?jetpack=publicize Default Publicize API URL.
		 */
		$url = apply_filters( 'publicize_api_url', 'https://public-api.wordpress.com/connect/?jetpack=publicize' );

		if ( $service ) {
			$url = add_query_arg( array( 'service' => $service ), $url );
		}

		if ( count( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		return $url;
	}

	static function connect_url( $service_name, $for ) {
		return add_query_arg(
			array(
				'action'   => 'request',
				'service'  => $service_name,
				'kr_nonce' => wp_create_nonce( 'keyring-request' ),
				'nonce'    => wp_create_nonce( "keyring-request-$service_name" ),
				'for'      => $for,
			),
			admin_url( 'options-general.php?page=sharing' )
		);
	}

	static function refresh_url( $service_name, $for ) {
		return add_query_arg(
			array(
				'action'   => 'request',
				'service'  => $service_name,
				'kr_nonce' => wp_create_nonce( 'keyring-request' ),
				'refresh'  => 1,
				'for'      => $for,
				'nonce'    => wp_create_nonce( "keyring-request-$service_name" ),
			),
			admin_url( 'options-general.php?page=sharing' )
		);
	}

	static function disconnect_url( $service_name, $id ) {
		return add_query_arg(
			array(
				'action'   => 'delete',
				'service'  => $service_name,
				'id'       => $id,
				'kr_nonce' => wp_create_nonce( 'keyring-request' ),
				'nonce'    => wp_create_nonce( "keyring-request-$service_name" ),
			),
			admin_url( 'options-general.php?page=sharing' )
		);
	}

	static function admin_page_load() {
		if ( isset( $_GET['action'] ) ) {
			if ( isset( $_GET['service'] ) ) {
				$service_name = $_GET['service'];
			}

			switch ( $_GET['action'] ) {

				case 'request':
					check_admin_referer( 'keyring-request', 'kr_nonce' );
					check_admin_referer( "keyring-request-$service_name", 'nonce' );

					$verification = ( new Secrets() )->generate( 'publicize' );
					if ( ! $verification ) {
						$url = Jetpack::admin_url( 'jetpack#/settings' );
						wp_die( sprintf( __( "Jetpack is not connected. Please connect Jetpack by visiting <a href='%s'>Settings</a>.", 'jetpack' ), $url ) );

					}
					$stats_options = get_option( 'stats_options' );
					$wpcom_blog_id = Jetpack_Options::get_option( 'id' );
					$wpcom_blog_id = ! empty( $wpcom_blog_id ) ? $wpcom_blog_id : $stats_options['blog_id'];

					$user     = wp_get_current_user();
					$redirect = Jetpack_Keyring_Service_Helper::api_url( $service_name, urlencode_deep( array(
						'action'       => 'request',
						'redirect_uri' => add_query_arg( array( 'action' => 'done' ), menu_page_url( 'sharing', false ) ),
						'for'          => 'publicize',
						// required flag that says this connection is intended for publicize
						'siteurl'      => site_url(),
						'state'        => $user->ID,
						'blog_id'      => $wpcom_blog_id,
						'secret_1'     => $verification['secret_1'],
						'secret_2'     => $verification['secret_2'],
						'eol'          => $verification['exp'],
					) ) );
					wp_redirect( $redirect );
					exit;
					break;

				case 'completed':
					$xml = new Jetpack_IXR_Client();
					$xml->query( 'jetpack.fetchPublicizeConnections' );

					if ( ! $xml->isError() ) {
						$response = $xml->getResponse();
						Jetpack_Options::update_option( 'publicize_connections', $response );
					}

					break;

				case 'delete':
					$id = $_GET['id'];

					check_admin_referer( 'keyring-request', 'kr_nonce' );
					check_admin_referer( "keyring-request-$service_name", 'nonce' );

					Jetpack_Keyring_Service_Helper::disconnect( $service_name, $id );

					do_action( 'connection_disconnected', $service_name );
					break;
			}
		}
	}

	/**
	 * Remove a Publicize connection
	 */
	static function disconnect( $service_name, $connection_id, $_blog_id = false, $_user_id = false, $force_delete = false ) {
		$xml = new Jetpack_IXR_Client();
		$xml->query( 'jetpack.deletePublicizeConnection', $connection_id );

		if ( ! $xml->isError() ) {
			Jetpack_Options::update_option( 'publicize_connections', $xml->getResponse() );
		} else {
			return false;
		}
	}

}
