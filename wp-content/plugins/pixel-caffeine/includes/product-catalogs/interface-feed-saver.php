<?php
/**
 * Contract of Product Feed Saver service
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog;

interface FeedSaverInterface {

	/**
	 * Run the save process of the feed
	 *
	 * @param string $context The saving context.
	 *
	 * @return mixed
	 */
	public function save( $context );

}
