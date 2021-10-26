<?php
/**
 * Facebook UserData object factory
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\FB;

use AEPC_Addons_Support;
use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\UserData;
use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\Util;

/**
 * Class User_Data_Factory
 */
class User_Data_Factory {

	/**
	 * Create the UserData instance from the WordPress session
	 *
	 * @return UserData
	 */
	public static function create_from_session() {
		$data = new UserData();

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			if ( $user instanceof \WP_User ) {
				$data = self::create_from_user( $user );
			}
		}

		return $data;
	}

	/**
	 * Create the UserData instance from a WP_User instance
	 *
	 * @param \WP_User $user The user.
	 *
	 * @return UserData
	 */
	public static function create_from_user( \WP_User $user ) {
		$data = ( new UserData() )
			->setEmail( $user->user_email )
			->setFirstName( $user->first_name )
			->setLastName( $user->last_name )
			->setExternalId( (string) $user->ID );

		// Add some extra information about the customer if an ecommerce addon is detected.
		return AEPC_Addons_Support::extend_user_data( $data );
	}

	/**
	 * Attach the server side info of the user
	 *
	 * Not attached in ::create_from_session() because they won't be added in the client side
	 *
	 * @param UserData $user_data The UserData instance.
	 *
	 * @return UserData
	 */
	public static function decorate_server_side( UserData $user_data ) {
		if ( empty( $user_data->getClientIpAddress() ) ) {
			$ip = self::get_ip_address();
			if ( null !== $ip ) {
				$user_data->setClientIpAddress( $ip );
			}
		}

		if ( empty( $user_data->getClientUserAgent() ) ) {
			/**
			 * The user agent.
			 *
			 * @var string|null $user_agent
			 */
			$user_agent = Util::getHttpUserAgent();
			if ( null !== $user_agent ) {
				$user_data->setClientUserAgent( Util::getHttpUserAgent() );
			}
		}

		if ( empty( $user_data->getFbc() ) ) {
			/**
			 * _fbc browser cookie.
			 *
			 * @var string|null $fbc
			 */
			$fbc = Util::getFbc();
			if ( null !== $fbc ) {
				$user_data->setFbc( Util::getFbc() );
			}
		}

		if ( empty( $user_data->getFbp() ) ) {
			/**
			 * _fbp browser cookie
			 *
			 * @var string|null $fbp
			 */
			$fbp = Util::getFbp();
			if ( null !== $fbp ) {
				$user_data->setFbp( Util::getFbp() );
			}
		}

		return $user_data;
	}

	/**
	 * Get first available IP Address
	 *
	 * Some servers send both IPv4 and IPv6 separated by comma (eg. 2001:db8::8a2e:370:7334,0.0.0.0).
	 *
	 * @return string|null
	 */
	public static function get_ip_address() {
		$ip = Util::getIpAddress();
		if ( empty( $ip ) ) {
			return null;
		}
		$available_ips = array_map( 'trim', explode( ',', Util::getIpAddress() ) );
		return array_shift( $available_ips );
	}

}
