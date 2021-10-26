<?php
/**
 * New product feed form template
 *
 * @var AEPC_Admin_View $page
 * @var null $product_catalog
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<!-- Subheader -->
<div class="jumbotron intro-dashboard intro-dude dude-prd-catalog intro-product-catalog upgraded-product-catalog">
	<div class="jumbotron-body">
		<h2 class="tit"><?php esc_html_e( 'Create the product catalog!', 'pixel-caffeine' ); ?></h2>
		<p class="text">
		<?php
			printf(
				/* translators: 1: opening link tag to https://adespresso.com/blog/facebook-dynamic-product-ads/, 2: closing link tag */
				esc_html__( 'The Product Catalog is a must have for anyone in eCommerce! It lets you create %1$sDynamic Product Ads%2$s on Facebook!', 'pixel-caffeine' ),
				'<a href="https://adespresso.com/blog/facebook-dynamic-product-ads/" target="_blank">',
				'</a>'
			);
			?>
		</p>
		<p class="text">
			<?php
			printf(
				/* translators: 1: opening strong tag, 2: closing strong tag */
				esc_html__( 'In just a few words you can automatically promote all of the products in your store (or just some of them!) to new potential customers or to visitors who checked out a specific product but didn\'t buy it. With Pixel Caffeine, you can create your product catalog with just %1$sone click%2$s and have it constantly updated with the latest products, prices, and availability!', 'pixel-caffeine' ),
				'<strong>',
				'</strong>'
			);
			?>
		</p>
	</div>
</div>

<div class="panel panel-prd-catalog form-horizontal js-product-feed-info<?php echo ! AEPC_Admin::$product_catalogs_service->is_product_catalog_enabled() ? ' disabled-box' : ''; ?>">
	<div class="panel-heading">
		<h2 class="tit"><?php esc_html_e( 'Generate Product Feed', 'pixel-caffeine' ); ?></h2>
	</div>
	<div class="panel-body">
		<?php
		$page->get_form_fields(
			'product-catalog',
			array(
				'action'          => 'save',
				'product_catalog' => $product_catalog,
			)
		)
		?>
	</div>
</div>
