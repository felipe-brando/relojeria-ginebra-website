<?php
/**
 * Add the necessary metabox for product catalog feature and manage the post meta
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog\Admin;

/**
 * Class Metaboxes
 *
 * @package PixelCaffeine\ProductCatalog\Admin
 */
class Metaboxes {

	const GOOGLE_CATEGORY_POSTMETA = 'aepc_google_category';

	/**
	 * Get the Google Category chosen in the metabox of the post
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return mixed
	 */
	public function get_google_category( $post_id ) {
		return $this->get_post_meta( $post_id, self::GOOGLE_CATEGORY_POSTMETA );
	}

	/**
	 * Incapsulate the get_post_meta WordPress function
	 *
	 * @param int    $post_id The post ID.
	 * @param string $key The post meta key.
	 *
	 * @return mixed
	 */
	protected function get_post_meta( $post_id, $key ) {
		return get_post_meta( $post_id, '_' . $key, true );
	}

	/**
	 * Incapsulate the update_post_meta WordPress function
	 *
	 * @param int    $post_id The post ID.
	 * @param string $key The post meta key.
	 * @param string $value The post meta value.
	 *
	 * @return mixed
	 */
	protected function update_post_meta( $post_id, $key, $value ) {
		return update_post_meta( $post_id, '_' . $key, $value );
	}

}
