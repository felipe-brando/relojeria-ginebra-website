<?php
/**
 * General admin settings page
 *
 * This is the template with the HTML code for the General Settings admin page
 *
 * @var AEPC_Admin_View $page
 * @var array $widget
 *
 * @package Pixel Caffeine
 *
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$posts = AEPC_Admin::fetch_sidebar_posts( $widget );

if ( ! isset( $posts['error'] ) && empty( $posts ) ) {
	return;
}

$utm = array(
	'utm_source'   => 'wordpressdashboard',
	'utm_campaign' => 'pixelcaffeine',
	'utm_medium'   => 'referral',
);

?>

<div class="plugin-sidebar-item">
	<h5 class="list-group-tit"><?php esc_html_e( 'AdEspresso News', 'pixel-caffeine' ); ?></h5>

	<?php if ( ! empty( $posts['error'] ) ) : ?>
		<p><?php echo esc_html( $posts['error'] ); ?></p>

	<?php elseif ( ! empty( $posts ) ) : ?>
	<div class="list-group no-icon">

		<?php foreach ( $posts as $post ) : ?>
		<div class="list-group-item">
			<div class="row-content">
				<a href="<?php echo esc_url( add_query_arg( $utm, $post['link'] ) ); ?>" class="list-group-item-heading" target="_blank"><?php echo esc_html( $post['title'] ); ?></a>
				<span class="list-group-item-date">
					<?php
					/* translators: %s: human date diff (example: 2 days ago, 2 hours ago, etc.) */
					printf( esc_html__( '%s ago', 'pixel-caffeine' ), esc_html( human_time_diff( strtotime( $post['date'] ) ) ) );
					?>
				</span>

				<p class="list-group-item-text"><?php echo esc_attr( wp_trim_words( $post['description'], 10 ) ); ?></p>
			</div>
		</div>
		<div class="list-group-separator"></div>
		<?php endforeach; ?>

	</div>
	<?php endif; ?>
</div>
