<?php
/**
 * Support class for currencies
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Support class for currencies
 *
 * @class AEPC_Currency
 */
class AEPC_Currency {

	/**
	 * The cached list of currencies already fetched.
	 *
	 * @var array|null
	 */
	private static $currencies = null;

	/**
	 * Get all currencies supported by facebook
	 *
	 * @return array
	 */
	public static function get_currencies() {
		if ( is_null( self::$currencies ) ) {
			WP_Filesystem();
			global $wp_filesystem;
			/**
			 * Get the global filesystem layer from WP
			 *
			 * @var WP_Filesystem_Base $wp_filesystem
			 */

			self::$currencies = (array) json_decode( $wp_filesystem->get_contents( __DIR__ . '/resources/currencies.json' ) );
		}

		return apply_filters( 'aepc_currencies', self::$currencies );
	}

	/**
	 * Return amount with eventual offset, in base of currency
	 *
	 * The methods is kept for legacy. Before, it was written to include this inside:
	 *   if ( in_array( $currency, array_keys( self::get_currencies() ) ) ) {
	 *     $amount *= self::get_offset( $currency );
	 *   }
	 *
	 * It doesn't need anymore, but I decided to keep the method for better compatibility.
	 *
	 * @param float $amount The amount.
	 *
	 * @return float
	 */
	public static function get_amount( $amount ) {
		return $amount;
	}

	/**
	 * Return the offset for a currency
	 *
	 * @param string $currency The currency code.
	 *
	 * @return integer
	 */
	public static function get_offset( $currency ) {
		$currencies = self::get_currencies();
		return isset( $currencies[ $currency ] ) ? $currencies[ $currency ]->offset : 1;
	}
}
