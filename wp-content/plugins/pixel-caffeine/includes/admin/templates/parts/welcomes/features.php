<?php
/**
 * List of features in the welcome page
 *
 * @var AEPC_Admin_View $page
 * @var string $back_to
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="box box-features">
	<h3 class="tit"><?php esc_html_e( 'Why Pixel Caffeine?', 'pixel-caffeine' ); ?></h3>
	<ul class="list-features">
		<li class="feature">
			<i class="feature-icon material-icons">check</i>
			<h4 class="tit"><?php esc_html_e( 'Easy Pixel Setup', 'pixel-caffeine' ); ?></h4>
			<p><?php esc_html_e( 'Add Facebook retargeting Pixel to your Website with just one click with our Facebook integration.', 'pixel-caffeine' ); ?></p>
		</li>
		<li class="feature">
			<i class="feature-icon material-icons">track_changes</i>
			<h4 class="tit"><?php esc_html_e( 'Track Advanced Data', 'pixel-caffeine' ); ?></h4>
			<p><?php esc_html_e( 'Sometime more is better than less. Pixel Caffeine enhances Facebook Pixel by tracking more information with 0 effort.', 'pixel-caffeine' ); ?></p>
		</li>
		<li class="feature">
			<i class="feature-icon material-icons">people</i>
			<h4 class="tit"><?php esc_html_e( 'Create Custom Audiences', 'pixel-caffeine' ); ?></h4>
			<p><?php esc_html_e( 'Create Custom Audiences directly from WordPress. Build laser-focused audiences based on tags, categories and custom fields.', 'pixel-caffeine' ); ?></p>
		</li>
		<li class="feature">
			<i class="feature-icon material-icons">star</i>
			<h4 class="tit"><?php esc_html_e( 'Track Conversions', 'pixel-caffeine' ); ?></h4>
			<p><?php esc_html_e( 'Seamlessly add Conversion tracking to your website based on page views or custom events including the click of specific links.', 'pixel-caffeine' ); ?></p>
		</li>
		<li class="feature">
			<i class="feature-icon material-icons">developer_board</i>
			<h4 class="tit"><?php esc_html_e( 'Product Catalog', 'pixel-caffeine' ); ?></h4>
			<p><?php esc_html_e( 'If you\'re using WooCommerce or Easy Digital Download you can now upload your Product Catalog to Facebook in just one click and have it constantly updated. You can also use our advanced filters to decide what\'s included in the catalog!', 'pixel-caffeine' ); ?></p>
		</li>
		<li class="feature">
			<i class="feature-icon material-icons">shopping_cart</i>
			<h4 class="tit"><?php esc_html_e( 'WooCommerce Support', 'pixel-caffeine' ); ?></h4>
			<p><?php esc_html_e( 'Running an eCommerce store? We got you covered. In 1 click you\'ll be able to track all your conversion events.', 'pixel-caffeine' ); ?></p>
		</li>
	</ul>
</div><!-- ./box-features -->
