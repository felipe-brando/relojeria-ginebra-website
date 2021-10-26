<?php
/**
 * Easy Digital Downloads support
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Dependencies\FacebookAds\Object\ServerSide\UserData;
use PixelCaffeine\Interfaces\ECommerceAddOnInterface;
use PixelCaffeine\ProductCatalog\Admin\Metaboxes;
use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\DbProvider;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Easy Digital Downloads support
 *
 * @class AEPC_Edd_Addon_Support
 */
class AEPC_Edd_Addon_Support extends AEPC_Addon_Factory implements ECommerceAddOnInterface {

	const FEED_STATUS_META         = '_product_feed_status';
	const ALREADY_TRACKED_POSTMETA = '_aepc_puchase_tracked';

	/**
	 * The slug of addon, useful to identify some common resources
	 *
	 * @var string
	 */
	protected $addon_slug = 'edd';

	/**
	 * Store the name of addon. It doesn't need a translate.
	 *
	 * @var string
	 */
	protected $addon_name = 'Easy Digital Downloads';

	/**
	 * Store the main file of rthe plugin
	 *
	 * @var string
	 */
	protected $main_file = 'easy-digital-downloads/easy-digital-downloads.php';

	/**
	 * Store the URL of plugin website
	 *
	 * @var string
	 */
	protected $website_url = 'https://wordpress.org/plugins/easy-digital-downloads/';

	/**
	 * List of standard events supported for pixel firing by PHP (it's not included the events managed by JS)
	 *
	 * @var array
	 */
	protected $events_support = array( 'ViewContent', 'ViewCategory', 'AddToCart', 'Purchase', 'AddPaymentInfo', 'InitiateCheckout' );

	/**
	 * Last parameters of AddToCart event used for AJAX add to cart
	 *
	 * @var array
	 */
	protected $last_add_to_cart_params = array();

	/**
	 * Method where set all necessary hooks launched from 'init' action
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'edd_post_add_to_cart', array( $this, 'send_add_to_cart_event' ), 10, 3 );
		add_action( 'edd_ajax_add_to_cart_response', array( $this, 'append_add_to_cart_params_response' ), 10, 3 );
		add_filter( 'edd_purchase_download_form', array( $this, 'add_category_and_sku_attributes' ), 10, 2 );
		add_action( 'wp_footer', array( $this, 'register_add_payment_info_params' ), 10 );

		// Force send Purchase here because the $edd_receipt global var is defined inside the receipt shortcode which is called in the page content.
		add_action( 'edd_payment_receipt_after', array( $this, 'send_purchase_event' ) );
	}

	/**
	 * Check if the plugin is active by checking the main function is existing
	 *
	 * @return bool
	 */
	public function is_active() {
		return function_exists( 'EDD' );
	}

	/**
	 * Check if we are in a place to fire the ViewContent event
	 *
	 * @return bool
	 */
	protected function can_fire_view_content() {
		return is_singular( 'download' ) && is_main_query();
	}

	/**
	 * Check if we are in a place to fire the ViewContent event
	 *
	 * @return bool
	 */
	protected function can_fire_view_category() {
		return is_tax( 'download_category' );
	}

	/**
	 * Check if we are in a place to fire the AddToCart event
	 *
	 * @return bool
	 */
	protected function can_fire_add_to_cart() {
		return doing_filter( 'edd_post_add_to_cart' );
	}

	/**
	 * Check if we are in a place to fire the InitiateCheckout event
	 *
	 * @return bool
	 */
	protected function can_fire_initiate_checkout() {
		return edd_is_checkout();
	}

	/**
	 * Check if we are in a place to fire the Purchase event
	 *
	 * @return bool
	 */
	protected function can_fire_purchase() {
		global $edd_receipt_args;

		return edd_is_success_page() && ! empty( $edd_receipt_args['id'] ) && ! get_post_meta( $edd_receipt_args['id'], self::ALREADY_TRACKED_POSTMETA, true );
	}

	/**
	 * Send Purchase event
	 *
	 * Send programmatically the Purchase event because the automatic fetch (in AEPC_Pixel_Scripts) cannot gather Purchase because the global $edd_receipt_args
	 * is defined inside the receipt shortcode which is called inside the page content.
	 *
	 * @return void
	 */
	public function send_purchase_event() {
		$this->send_event( 'Purchase' );
	}

	/**
	 * Get product info from single page for ViewContent event
	 *
	 * @return array
	 */
	protected function get_view_content_params() {
		$product_id = get_the_ID();

		if ( ! $product_id ) {
			return array();
		}

		if ( ! edd_has_variable_prices( $product_id ) ) {
			$price = edd_get_download_price( $product_id );
		} else {
			$price = edd_get_lowest_price_option( $product_id );
		}

		$params = array();

		$params['content_name']     = $this->get_product_name( $product_id );
		$params['content_type']     = 'product';
		$params['content_ids']      = array( $this->maybe_sku( $product_id ) );
		$params['content_category'] = AEPC_Pixel_Scripts::content_category_list( $product_id, 'download_category' );
		$params['value']            = floatval( $price );
		$params['currency']         = edd_get_currency();

		return $params;
	}

	/**
	 * Get product info from single page for ViewContent event
	 *
	 * @return array
	 */
	protected function get_view_category_params() {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query ) {
			return array();
		}

		$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

		if ( ! $term instanceof WP_Term ) {
			return array();
		}

		$product_ids = array_values(
			array_map(
				function( $item ) {
					return $this->maybe_sku( $item->ID );
				},
				$wp_query->posts
			)
		);

		return array(
			'content_type'     => 'product',
			'content_name'     => $term->name,
			'content_category' => AEPC_Pixel_Scripts::content_category_path( $term ),
			'content_ids'      => array_slice( $product_ids, 0, 10 ),
		);
	}

	/**
	 * Save the data in session for the AddToCart pixel to fire.
	 *
	 * Because EDD after add to cart make a redirect, I cannot fire the pixel in the page are loading. So, the only way
	 * to fire the pixel is save the data to fire in the session and then after redirect read the session and fire the
	 * pixel if it founds the data saved in session.
	 *
	 * @param int   $download_id The ID of the download item.
	 * @param array $options Array of options.
	 * @param array $items The items of the cart.
	 *
	 * @return void
	 */
	public function send_add_to_cart_event( $download_id, $options, $items ) {
		$this->send_event(
			'AddToCart',
			array(
				$download_id,
				1,
				$items,
			)
		);
	}

	/**
	 * Append the pixel info for client side
	 *
	 * @param array $response The response of Add To Cart action from AJAX.
	 *
	 * @return array
	 */
	public function append_add_to_cart_params_response( $response ) {
		if ( empty( $this->last_add_to_cart_params ) ) {
			return $response;
		}

		$params   = $this->last_add_to_cart_params;
		$event_id = $params['event_id'];
		unset( $params['event_id'] );

		return array_merge(
			$response,
			array(
				'pixel' => array(
					'params'   => $params,
					'event_id' => $event_id,
				),
			)
		);
	}

	/**
	 * Get info from product when added to cart for AddToCart event
	 *
	 * @param int   $download_id The ID of the download item.
	 * @param int   $quantity The quantity of the item added into the cart.
	 * @param array $items The items of the cart.
	 *
	 * @return array
	 */
	protected function get_add_to_cart_params( $download_id = null, $quantity = 1, $items = array() ) {
		if ( null === $download_id ) {
			return array();
		}

		$price = 0;

		// Calculate the total price.
		foreach ( $items as $item ) {
			$price += $this->get_price( $download_id, $item['options'] ) * $item['quantity'];
		}

		$this->last_add_to_cart_params = array(
			'content_type'     => 'product',
			'content_ids'      => array_map( array( $this, 'maybe_sku' ), wp_list_pluck( $items, 'id' ) ),
			'content_category' => AEPC_Pixel_Scripts::content_category_list( $download_id, 'download_category' ),
			'value'            => (float) $price,
			'currency'         => edd_get_currency(),
			'event_id'         => $this->get_add_to_cart_event_id(),
		);

		return $this->last_add_to_cart_params;
	}

	/**
	 * Generate a cart hash to use as event ID for the AddToCart event
	 *
	 * @return string
	 */
	protected function get_add_to_cart_event_id() {
		$cart_contents = edd_get_cart_contents();
		$hash          = $cart_contents ? md5( wp_json_encode( $cart_contents ) . edd_cart_total( false ) ) : '';

		return apply_filters( 'aepc_add_to_cart_event_id', $hash, $cart_contents );
	}

	/**
	 * Get info from checkout page for InitiateCheckout event
	 *
	 * @return array
	 */
	protected function get_initiate_checkout_params() {
		$product_ids = array();
		$num_items   = 0;
		$total       = 0;

		foreach ( edd_get_cart_contents() as $cart_item ) {
			$product_id    = $this->maybe_sku( intval( $cart_item['id'] ) );
			$num_items    += $cart_item['quantity'];
			$product_ids[] = $product_id;
			$total        += $this->get_price( $cart_item['id'], $cart_item['options'] ) * $cart_item['quantity'];
		}

		return array(
			'content_type' => 'product',
			'content_ids'  => $product_ids,
			'num_items'    => $num_items,
			'value'        => floatval( $total ),
			'currency'     => edd_get_currency(),
		);
	}

	/**
	 * Get product info from purchase succeeded page for Purchase event
	 *
	 * @return array
	 */
	protected function get_purchase_params() {
		global $edd_receipt_args;

		$payment = get_post( $edd_receipt_args['id'] );

		if ( ! $payment instanceof WP_Post ) {
			return array();
		}

		$payment_id    = $payment->ID;
		$product_ids   = array();
		$payment_value = edd_get_payment_amount( $payment_id );

		$cart = edd_get_payment_meta_cart_details( $payment_id, true );

		foreach ( (array) $cart as $item ) {
			$product_ids[] = $this->maybe_sku( $item['id'] );
		}

		add_post_meta( $payment_id, self::ALREADY_TRACKED_POSTMETA, true );

		return array(
			'content_ids'  => $product_ids,
			'content_type' => 'product',
			'value'        => $payment_value,
			'currency'     => edd_get_payment_currency_code( $payment_id ),
			'event_id'     => hash( 'sha256', $payment_id . $payment_value . wp_json_encode( array_unique( $product_ids ) ), false ),
		);
	}

	/**
	 * Register the AddPaymentInfo parameters fired by JS when the checkout is submitted
	 *
	 * @return void
	 */
	public function register_add_payment_info_params() {
		if ( ! edd_is_checkout() ) {
			return;
		}

		$args = AEPC_Track::check_event_parameters( 'AddPaymentInfo', $this->get_initiate_checkout_params() );
		wp_localize_script( 'aepc-pixel-events', 'aepc_add_payment_info_params', $args );
	}

	/**
	 * Extend UserData entity if needed
	 *
	 * @param UserData $user_data User Data.
	 *
	 * @return UserData
	 */
	public function extend_user_data( UserData $user_data ) {
		$user    = wp_get_current_user();
		$address = $user->_edd_user_address;

		if ( empty( $address ) ) {
			return $user_data;
		}

		return $user_data
			->setCity( $address['city'] )
			->setState( $address['state'] )
			->setZipCode( $address['zip'] );
	}

	/**
	 * Returns SKU if exists, otherwise the product ID
	 *
	 * @param int $product_id The product ID which get the SKU.
	 *
	 * @return string|int
	 */
	protected function maybe_sku( $product_id ) {
		$sku = get_post_meta( $product_id, 'edd_sku', true );

		if ( AEPC_Track::can_use_sku() && edd_use_skus() && ! empty( $sku ) ) {
			return $sku;
		}

		return $product_id;
	}

	/**
	 * Retrieve the price
	 *
	 * @param int   $download_id The download ID where get the price.
	 * @param array $options When the download have different price options, this array contains the price ID.
	 *
	 * @return float
	 */
	protected function get_price( $download_id, $options = array() ) {
		return isset( $options['price_id'] ) ? edd_get_price_option_amount( $download_id, $options['price_id'] ) : edd_get_download_price( $download_id );
	}

	/**
	 * Add the data attributes for SKU and categories, used for the events fired via javascript
	 *
	 * @param string $purchase_form HTML of the whole purchase form.
	 * @param array  $args Download arguments.
	 *
	 * @return string
	 */
	public function add_category_and_sku_attributes( $purchase_form, $args ) {
		if ( is_admin() || ! PixelCaffeine()->is_pixel_enabled() ) {
			return $purchase_form;
		}

		$product_id = $args['download_id'];
		$target     = 'data-action="edd_add_to_cart" ';
		$atts       = '';

		// SKU.
		$sku = get_post_meta( $product_id, 'edd_sku', true );
		if ( edd_use_skus() && $sku ) {
			$atts .= sprintf( 'data-download-sku="%s" ', esc_attr( $sku ) );
		}

		// Categories.
		$atts .= sprintf( 'data-download-categories="%s" ', esc_attr( AEPC_Pixel_Scripts::content_category_list( $product_id, 'download_category' ) ) );

		return str_replace( $target, $target . $atts, $purchase_form );
	}

	/**
	 * Returns the checkout URL where the items may be purcahsed
	 *
	 * @return string
	 */
	public function get_checkout_url() {
		return edd_get_checkout_uri();
	}

	/**
	 * Returns the types supported by this plugin
	 *
	 * @return array
	 */
	public function get_product_types() {
		$types = edd_get_download_types();
		return array(
			'download'        => $types['0'],
			'download-bundle' => $types['bundle'],
		);
	}

	/**
	 * Returns a short description of a download item
	 *
	 * @param int $download_id The download item ID.
	 *
	 * @return string
	 */
	protected function get_short_description( $download_id ) {
		$excerpt_length = apply_filters( 'excerpt_length', 30 );
		if ( has_excerpt( $download_id ) ) {
			return wp_trim_words( get_post_field( 'post_excerpt', $download_id ), $excerpt_length );
		} else {
			return wp_trim_words( get_post_field( 'post_content', $download_id ), $excerpt_length );
		}
	}

	/**
	 * Return the AEPC_Addon_Product_item instance for the product
	 *
	 * @param EDD_Download  $product The Product item.
	 * @param Metaboxes     $metaboxes The metaboxes instance from where get the category.
	 * @param Configuration $configuration The product catalog configuration instance.
	 *
	 * @return AEPC_Addon_Product_Item
	 */
	public function get_product_item( $product, Metaboxes $metaboxes, Configuration $configuration ) {
		$product_item = new AEPC_Addon_Product_Item( $this );

		// Backwards helper variables.
		$product_id          = $product->get_ID();
		$product_slug        = $product->post_name;
		$product_description = ! empty( $product->post_content ) ? $product->post_content : $product->post_excerpt;
		/**
		 * The price might be false if variable
		 *
		 * @var bool|int $price_id
		 */
		$price_id          = edd_get_default_variable_price( $product_id );
		$product_price     = false !== $price_id && is_int( $price_id ) ? edd_get_price_option_amount( $product_id, $price_id ) : $product->get_price();
		$product_price_tax = edd_calculate_tax( $product_price );
		$sku               = $product->get_sku();
		$product_permalink = get_permalink( $product_id );

		$product_item
			->set_id( $product_id )
			->set_sku( '-' !== $sku ? $sku : '' )
			->set_slug( $product_slug )
			->set_permalink( $product_permalink ?: '' )
			->set_admin_url(
				add_query_arg(
					array(
						'post'   => $product_id,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				)
			)
			->set_title( $product->get_name() )
			->set_description( $product_description )
			->set_short_description( $this->get_short_description( $product_id ) )
			->set_link( $product_permalink ?: '' )
			->set_image_url( wp_get_attachment_image_url( get_post_thumbnail_id( $product_id ) ?: 0, $configuration->get( Configuration::OPTION_IMAGE_SIZE ) ) ?: '' )
			->set_additional_image_urls( array() )
			->set_currency( edd_get_currency() )
			->set_price( (string) $product_price )
			->set_price_tax( (string) $product_price_tax )
			->set_checkout_url( home_url( '/edd-add/' . $product_id ) )
			->set_if_needs_shipping( false )
			->set_if_variation( false )
			->set_google_category(
				$metaboxes->get_google_category(
					$product_id
				)
			);

		// Set availability.
		$product_item->set_in_stock();

		// Get categories.
		$terms = get_terms(
			array(
				'object_ids'   => $product_id,
				'taxonomy'     => 'download_category',
				'hierarchical' => true,
				'fields'       => 'id=>parent',
			)
		);

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$product_item->set_categories( $terms );
		}

		return $product_item;
	}

	/**
	 * Get the arguments of the items query
	 *
	 * @param string                $filter The filter method. One of 'not-saved', 'saved', 'edited'.
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return array
	 */
	protected function query_items_args( $filter, ProductCatalogManager $product_catalog ) {
		$products_query = array(
			'post_type'      => 'download',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => $product_catalog->configuration()->get( Configuration::OPTION_CHUNK_LIMIT ),
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		// Collect the product types to use in the query.
		$product_types = array_filter( (array) $product_catalog->configuration()->get( Configuration::OPTION_FILTER_BY_TYPE ) );
		if ( ! empty( $product_types ) ) {
			$meta_query = array( 'relation' => 'OR' );
			switch ( true ) {
				case in_array( 'download', $product_types, true ):
					$meta_query[] = array(
						'key'     => '_edd_product_type',
						'compare' => 'NOT EXISTS',
					);
					$meta_query[] = array(
						'key'     => '_edd_product_type',
						'compare' => '=',
						'value'   => 'default',
					);
					break;
				case in_array( 'download-bundle', $product_types, true ):
					$meta_query[] = array(
						'key'   => '_edd_product_type',
						'value' => 'bundle',
					);
					break;
			}
			if ( ! isset( $products_query['meta_query'] ) ) {
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				$products_query['meta_query'] = array();
			}
			$products_query['meta_query'][] = $meta_query;
		}

		$filter_cat = array_map( 'intval', array_filter( (array) $product_catalog->configuration()->get( Configuration::OPTION_FILTER_BY_CATEGORY ) ) );
		if ( ! empty( $filter_cat ) ) {
			if ( ! isset( $products_query['tax_query'] ) ) {
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$products_query['tax_query'] = array();
			}
			$products_query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $filter_cat,
			);
		}

		$filter_tag = array_map( 'intval', array_filter( (array) $product_catalog->configuration()->get( Configuration::OPTION_FILTER_BY_TAG ) ) );
		if ( ! empty( $filter_tag ) ) {
			if ( ! isset( $products_query['tax_query'] ) ) {
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$products_query['tax_query'] = array();
			}
			$products_query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'field'    => 'term_id',
				'terms'    => $filter_tag,
			);
		}

		return $this->filter_items_query( $filter, $products_query, $product_catalog );
	}

	/**
	 * Filter the WP_Query arguments with the necessary arguments in order to filter the query in base of the status
	 * of the product in the feed
	 *
	 * @param string                $filter The filter method. One of 'not-saved', 'saved', 'edited'.
	 * @param array                 $products_query The product query array.
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return array
	 */
	protected function filter_items_query( $filter, array $products_query, ProductCatalogManager $product_catalog ) {
		switch ( $filter ) {

			case 'not-saved':
				if ( ! isset( $products_query['meta_query'] ) ) {
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					$products_query['meta_query'] = array();
				}

				$products_query['meta_query'][] = array(
					'key'     => $this->get_feed_status_meta_key( $product_catalog ),
					'compare' => 'NOT EXISTS',
				);
				break;

			case 'saved':
				if ( ! isset( $products_query['meta_query'] ) ) {
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					$products_query['meta_query'] = array();
				}

				$products_query['meta_query'][] = array(
					'key'     => $this->get_feed_status_meta_key( $product_catalog ),
					'value'   => DbProvider::FEED_STATUS_SAVED,
					'compare' => '=',
				);
				break;

			case 'edited':
				if ( ! isset( $products_query['meta_query'] ) ) {
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					$products_query['meta_query'] = array();
				}

				$products_query['meta_query'][] = array(
					'key'     => $this->get_feed_status_meta_key( $product_catalog ),
					'value'   => DbProvider::FEED_STATUS_EDITED,
					'compare' => '=',
				);
				break;

		}

		return $products_query;
	}

	/**
	 * Query the items from DB in base of if get edited, saved or all
	 *
	 * @param string                $filter One of 'all', 'edited' or 'saved'.
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 * @param Metaboxes             $metaboxes The metaboxes instance from where get the category.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	protected function query_items( $filter, ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		$products_query = $this->query_items_args( $filter, $product_catalog );

		// Map WC objects.
		$products = new WP_Query( $products_query );
		$products = array_map( array( $this, 'get_download_instance' ), $products->posts );

		$items = array();

		// Map the product item object.
		foreach ( $products as $item ) {
			$items[] = $this->get_product_item( $item, $metaboxes, $product_catalog->configuration() );
		}

		return $items;
	}

	/**
	 * Get the EDD_Download instance
	 *
	 * @param WP_Post $download The post instance of the download item.
	 *
	 * @return EDD_Download
	 */
	protected function get_download_instance( WP_Post $download ) {
		return new EDD_Download( $download->ID );
	}

	/**
	 * Returns the array of all term objects id=>name for all categories of the shop
	 *
	 * @return array
	 */
	public function get_product_categories() {
		$categories = array();
		$terms      = get_terms(
			array(
				'taxonomy'   => 'download_category',
				'hide_empty' => false,
			)
		);

		if ( ! is_array( $terms ) ) {
			return array();
		}

		foreach ( $terms as $term ) {
			$categories[ $term->term_id ] = $term->name;
		}

		return $categories;
	}

	/**
	 * Returns the array of all term objects id=>name for all tags of the shop
	 *
	 * @return array
	 */
	public function get_product_tags() {
		$categories = array();
		$terms      = get_terms(
			array(
				'taxonomy'   => 'download_tag',
				'hide_empty' => false,
			)
		);

		if ( ! is_array( $terms ) ) {
			return array();
		}

		foreach ( $terms as $term ) {
			$categories[ $term->term_id ] = $term->name;
		}

		return $categories;
	}

	/**
	 * Return the array with all AEPC_Addon_Product_Item instances for the products to include inside the XML feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 * @param Metaboxes             $metaboxes The metaboxes instance from where get the category.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries( ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		return $this->query_items( ProductCatalogManager::FILTER_ALL, $product_catalog, $metaboxes );
	}

	/**
	 * Get the feed entries to save into the feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 * @param Metaboxes             $metaboxes The metaboxes instance from where get the category.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries_to_save( ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		return $this->query_items( ProductCatalogManager::FILTER_NOT_SAVED, $product_catalog, $metaboxes );
	}

	/**
	 * Get the feed entries to edit in the feed
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 * @param Metaboxes             $metaboxes The metaboxes instance from where get the category.
	 *
	 * @return AEPC_Addon_Product_Item[]
	 */
	public function get_feed_entries_to_edit( ProductCatalogManager $product_catalog, Metaboxes $metaboxes ) {
		return $this->query_items( ProductCatalogManager::FILTER_EDITED, $product_catalog, $metaboxes );
	}

	/**
	 * Get the key associated to the product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return string
	 */
	protected function get_feed_status_meta_key( ProductCatalogManager $product_catalog ) {
		return sprintf( '%s_%s', self::FEED_STATUS_META, $product_catalog->get_entity()->get_id() );
	}

	/**
	 * Save a meta in the product post that set the product as saved in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The product catalog manager instance.
	 * @param \AEPC_Addon_Product_Item $item The Product feed item.
	 *
	 * @return void
	 */
	public function set_product_saved_in_feed( ProductCatalogManager $product_catalog, \AEPC_Addon_Product_Item $item ) {
		update_post_meta( intval( $item->get_id() ), $this->get_feed_status_meta_key( $product_catalog ), DbProvider::FEED_STATUS_SAVED );
	}

	/**
	 * Save the meta in the product post that set the product as edited in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The product catalog manager instance.
	 * @param \AEPC_Addon_Product_Item $item The Product feed item.
	 *
	 * @return void
	 */
	public function set_product_edited_in_feed( ProductCatalogManager $product_catalog, \AEPC_Addon_Product_Item $item ) {
		update_post_meta( intval( $item->get_id() ), $this->get_feed_status_meta_key( $product_catalog ), DbProvider::FEED_STATUS_EDITED );
	}

	/**
	 * Delete the meta in the product post that set the product as saved in the product feed
	 *
	 * @param ProductCatalogManager    $product_catalog The product catalog manager instance.
	 * @param \AEPC_Addon_Product_Item $item The Product feed item.
	 *
	 * @return void
	 */
	public function set_product_not_saved_in_feed( ProductCatalogManager $product_catalog, \AEPC_Addon_Product_Item $item ) {
		delete_post_meta( intval( $item->get_id() ), $this->get_feed_status_meta_key( $product_catalog ) );
	}

	/**
	 * Perform a global delete in one query ideally for all feed status associated to the product catalog
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return void
	 */
	public function remove_all_feed_status( ProductCatalogManager $product_catalog ) {
		delete_post_meta_by_key( $this->get_feed_status_meta_key( $product_catalog ) );
	}

	/**
	 * Detect if there are items to save yet or not
	 *
	 * @param ProductCatalogManager $product_catalog The product catalog manager instance.
	 *
	 * @return bool
	 */
	public function there_are_items_to_save( ProductCatalogManager $product_catalog ) {
		$products_query = $this->query_items_args( ProductCatalogManager::FILTER_NOT_SAVED, $product_catalog );

		// Get only counter.
		$products_query['posts_per_age'] = 1;

		// Query.
		$products = new WP_Query( $products_query );

		return (bool) $products->found_posts;
	}

	/**
	 * HELPERS
	 */

	/**
	 * Retrieve the product name
	 *
	 * @param int $product_id The ID of product where get its name.
	 *
	 * @return string
	 */
	public function get_product_name( $product_id ) {
		return get_post_field( 'post_title', $product_id );
	}

	/**
	 * Says if the product is of addon type
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return bool
	 */
	public function is_product_of_this_addon( $product_id ) {
		return 'download' === get_post_type( $product_id );
	}

}
