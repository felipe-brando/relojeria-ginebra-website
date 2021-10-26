<?php
/**
 * Manage the notices in the admin
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class for the admin notices
 *
 * @class AEPC_Admin_Notices
 */
class AEPC_Admin_Notices {

	const ERROR_TYPE   = 'error';
	const SUCCESS_TYPE = 'success';
	const WARNING_TYPE = 'warning';
	const INFO_TYPE    = 'info';

	/**
	 * Save all notices occur in the admin pages
	 *
	 * @var array
	 */
	protected static $notices = array();

	/**
	 * Add useful hooks for initialization
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'get_notices_from_user_meta' ) );
		add_action( 'shutdown', array( __CLASS__, 'save_notices_in_user_meta' ) );
	}

	/**
	 * Add the notice, by $type and $id
	 *
	 * @param string $type One of 'error', 'success', 'warning' and 'info'.
	 * @param string $id The string identification for the notice.
	 * @param string $message The notice message.
	 * @param string $dismiss_action The action for the dismiss request.
	 *
	 * @return void
	 */
	public static function add_notice( $type, $id, $message, $dismiss_action = '' ) {
		if ( ! isset( self::$notices[ $type ][ $id ] ) ) {
			self::$notices[ $type ][ $id ] = array();
		}

		// Add the notice.
		self::$notices[ $type ][ $id ][] = array(
			'text'           => $message,
			'dismiss_action' => $dismiss_action,
		);
	}

	/**
	 * Check if there is some error for the type and ID
	 *
	 * @param string $type One of 'error', 'success', 'warning' and 'info'.
	 * @param string $id The string identification for the notice.
	 *
	 * @return bool
	 */
	public static function has_notice( $type = '', $id = '' ) {
		if ( 'any' === $type ) {
			$type = '';
		}

		if ( empty( $type ) && empty( $id ) ) {
			// Check for all.
			foreach ( array_keys( self::$notices ) as $type ) {
				if ( ! empty( self::$notices[ $type ] ) ) {
					return true;
				}
			}
		} elseif ( empty( $type ) && ! empty( $id ) ) {
			// Check for ID of any type.
			foreach ( array_keys( self::$notices ) as $type ) {
				if ( ! empty( self::$notices[ $type ][ $id ] ) ) {
					return true;
				}
			}
		} elseif ( ! empty( $type ) && empty( $id ) ) {
			// Check any ID of specific type.
			return ! empty( self::$notices[ $type ] );
		} elseif ( ! empty( $type ) && ! empty( $id ) ) {
			// Check specific ID of specific type.
			return ! empty( self::$notices[ $type ][ $id ] );
		}

		return false;
	}

	/**
	 * Return the notices, by $type and $id
	 *
	 * @param string $type One of 'error', 'success', 'warning' and 'info'.
	 * @param string $id The string identification for the notice.
	 *
	 * @return array
	 */
	public static function get_notices( $type = '', $id = '' ) {
		if ( 'any' === $type ) {
			$type = '';
		}

		// Init array to return with all empty keys.
		$notices = array_map( '__return_empty_array', array_flip( array_keys( self::$notices ) ) );

		if ( empty( $type ) && empty( $id ) ) {
			// Check for all.
			foreach ( array_keys( self::$notices ) as $type ) {
				if ( ! empty( self::$notices[ $type ] ) ) {
					$notices[ $type ] = self::$notices[ $type ];
				}
			}
		} elseif ( empty( $type ) && ! empty( $id ) ) {
			// Check for ID of any type.
			foreach ( array_keys( self::$notices ) as $type ) {
				if ( ! empty( self::$notices[ $type ][ $id ] ) ) {
					$notices[ $type ][ $id ] = self::$notices[ $type ][ $id ];
				}
			}
		} elseif ( ! empty( $type ) && empty( $id ) && ! empty( self::$notices[ $type ] ) ) {
			// Check any ID of specific type.
			return self::$notices[ $type ];
		} elseif ( ! empty( $type ) && ! empty( $id ) && ! empty( self::$notices[ $type ][ $id ] ) ) {
			// Check specific ID of specific type.
			return self::$notices[ $type ][ $id ];
		}

		return array_filter( $notices );
	}

	/**
	 * Remove the notices, by defining $type and $id, both optional
	 *
	 * @param string $type One of 'error', 'success', 'warning' and 'info'.
	 * @param string $id The string identification for the notice.
	 *
	 * @return void
	 */
	public static function remove_notices( $type = '', $id = '' ) {
		if ( 'any' === $type ) {
			$type = '';
		}

		if ( empty( $type ) && empty( $id ) ) {
			// Check for all.
			self::$notices = array_map( '__return_empty_array', self::$notices );
		} elseif ( empty( $type ) && ! empty( $id ) ) {
			// Check for ID of any type.
			foreach ( array_keys( self::$notices ) as $type ) {
				unset( self::$notices[ $type ][ $id ] );
			}
		} elseif ( ! empty( $type ) && empty( $id ) && ! empty( self::$notices[ $type ] ) ) {
			// Check any ID of specific type.
			self::$notices[ $type ] = array();
		} elseif ( ! empty( $type ) && ! empty( $id ) && ! empty( self::$notices[ $type ][ $id ] ) ) {
			// Check specific ID of specific type.
			unset( self::$notices[ $type ][ $id ] );
		}
	}

	/**
	 * Get the notices from user meta, saved on php shutdown
	 *
	 * @return void
	 */
	public static function get_notices_from_user_meta() {
		$saved_notices = get_user_meta( get_current_user_id(), 'aepc_admin_notices', true );
		if ( $saved_notices ) {
			self::$notices = $saved_notices;
			delete_user_meta( get_current_user_id(), 'aepc_admin_notices' );
		}
	}

	/**
	 * This method is triggered on php shutdown, because if some notice remains, it will be shown on frontend
	 * as soon as possible
	 *
	 * @return void
	 */
	public static function save_notices_in_user_meta() {
		if ( self::has_notice() ) {
			update_user_meta( get_current_user_id(), 'aepc_admin_notices', self::get_notices() );
		}
	}

	/**
	 * Performs an action for each ID of notice dismissed
	 *
	 * @param string $id The ID of the dismiss action.
	 *
	 * @return void
	 */
	public static function dismiss_notice( $id ) {
		switch ( $id ) {

			case 'ca_bug_warning':
				update_option( 'aepc_show_warning_ca_bug', false );
				break;

		}
	}

}
