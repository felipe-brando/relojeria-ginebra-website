<?php
/**
 * Contract of an e-commerce plugin support
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\Interfaces;

use AEPC_Addon_Product_Item;
use PixelCaffeine\ProductCatalog\Admin\Metaboxes;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

interface ECommerceAddOnInterface {

	/**
	 * Returns the human name of addon to show somewhere
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Returns the human name of addon to show somewhere
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Returns the URI of logo image to show on admin UI
	 *
	 * @return string
	 */
	public function get_logo_img();

	/**
	 * Returns the website URL, useful on frontend to link the user to the plugin website
	 *
	 * @return string
	 */
	public function get_website_url();

	/**
	 * Returns the checkout URL where the items may be purcahsed
	 *
	 * @return string
	 */
	public function get_checkout_url();

	/**
	 * Returns the array of all term objects id=>name for all categories of the shop
	 *
	 * @return array
	 */
	public function get_product_categories();

	/**
	 * Returns the array of all term objects id=>name for all tags of the shop
	 *
	 * @return array
	 */
	public function get_product_tags();

	/**
	 * Return the array with all AEPC_Addon_Product_item instances for the products to include inside the XML feed
	 *
	 * @param ProductCatalogManager $product_catalog The Product Catalog entity manager.
	 * @param Metaboxes             $metaboxes The Metaboxes manager instance.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries( ProductCatalogManager $product_catalog, Metaboxes $metaboxes );

	/**
	 * Get the feed entries to save into the feed
	 *
	 * @param ProductCatalogManager $product_catalog The Product Catalog entity manager.
	 * @param Metaboxes             $metaboxes The Metaboxes manager instance.
	 *
	 * @return AEPC_Addon_Product_item[]
	 */
	public function get_feed_entries_to_save( ProductCatalogManager $product_catalog, Metaboxes $metaboxes );

	/**
	 * Get the feed entries to edit in the feed
	 *
	 * @param ProductCatalogManager $product_catalog The Product Catalog entity manager.
	 * @param Metaboxes             $metaboxes The Metaboxes manager instance.
	 *
	 * @return AEPC_Addon_Product_item[]
	 */
	public function get_feed_entries_to_edit( ProductCatalogManager $product_catalog, Metaboxes $metaboxes );

	/**
	 * Save a meta in the product post that set the product as saved in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The Product Catalog entity manager.
	 * @param \AEPC_Addon_Product_Item $item The product feed item from the Add-on.
	 *
	 * @return void
	 */
	public function set_product_saved_in_feed( ProductCatalogManager $product_catalog, AEPC_Addon_Product_Item $item );

	/**
	 * Save the meta in the product post that set the product as edited in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The Product Catalog entity manager.
	 * @param \AEPC_Addon_Product_Item $item The product feed item from the Add-on.
	 *
	 * @return void
	 */
	public function set_product_edited_in_feed( ProductCatalogManager $product_catalog, AEPC_Addon_Product_Item $item );

	/**
	 * Delete the meta in the product post that set the product as saved in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The Product Catalog entity manager.
	 * @param \AEPC_Addon_Product_Item $item The product feed item from the Add-on.
	 *
	 * @return void
	 */
	public function set_product_not_saved_in_feed( ProductCatalogManager $product_catalog, AEPC_Addon_Product_Item $item );

	/**
	 * Perform a global delete in one query ideally for all feed status associated to the product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The Product Catalog entity manager.
	 *
	 * @return void
	 */
	public function remove_all_feed_status( ProductCatalogManager $product_catalog );

	/**
	 * Detect if there are items marked as not saved in the feed
	 *
	 * @param ProductCatalogManager $product_catalog The Product Catalog entity manager.
	 *
	 * @return bool
	 */
	public function there_are_items_to_save( ProductCatalogManager $product_catalog );

}
