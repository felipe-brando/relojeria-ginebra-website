<?php
/**
 * Main fixes for Third-Party plugin conflicts
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AEPC_Third_Party_Fixes
 */
class AEPC_Third_Party_Fixes {

	/**
	 * Hooks to include scripts.
	 *
	 * @return void
	 */
	public static function init() {
		/**
		 * SG Optimizer conflict with Lazy Loading options and image_link inside product catalog
		 */
		add_action(
			'admin_init',
			function() {
				if ( false !== strpos( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ), 'aepc_' ) ) {
					add_filter( 'pre_option_siteground_optimizer_lazyload_images', '__return_false' );
					add_filter( 'pre_site_option_siteground_optimizer_lazyload_images', '__return_false' );
				}
			}
		);
	}

}
