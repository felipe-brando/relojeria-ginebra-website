<?php
/**
 * General admin settings page
 *
 * This is the template with the HTML code for the General Settings admin page
 *
 * @var AEPC_Admin_View $page
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="pixel-caffeine-wrapper">
	<div class="wrap wrap-dashboard">

	<h1 class="page-title"><?php $page->the_title(); ?></h1>

	<?php $page->get_template_part( 'nav-tabs' ); ?>

	<section class="plugin-sec">
		<div class="plugin-content">

			<div class="alert-wrap">
				<?php $page->print_notices(); ?>
			</div>

			<form>
				<?php if ( ! AEPC_Admin::is_plugin_configured() ) : ?>

				<div class="jumbotron intro-dashboard intro-dude dude-config">
					<div class="jumbotron-body">
						<h2 class="tit"><?php esc_html_e( 'Welcome to Pixel Caffeine!', 'pixel-caffeine' ); ?></h2>

						<p><?php esc_html_e( 'Congratulations! You\'re one step away from unleashing the true power of Facebook Custom Audiences for your blog!', 'pixel-caffeine' ); ?></p>

						<p><?php esc_html_e( 'Pixel Caffeine is the most advanced WordPress Plugin to add Facebook Pixel to your site, add conversion tracking and create laser focused Custom Audiences to take your Facebook Advertising to the next level!', 'pixel-caffeine' ); ?></p>

						<div class="embed-responsive embed-responsive-16by9">
							<iframe width="560" height="315" src="https://www.youtube.com/embed/zFAszDll_1w?rel=0&amp;showinfo=0&amp;VQ=HD1080" frameborder="0" allowfullscreen></iframe>
						</div>

						<a href="<?php echo esc_url( $page->get_view_url( 'tab=general-settings' ) ); ?>" class="btn btn-raised btn-success btn-config btn-block"><?php esc_html_e( 'Set up now!', 'pixel-caffeine' ); ?></a>
					</div>
				</div>

					<?php $page->get_template_part( 'welcomes/features' ); ?>

				<?php else : ?>

				<div class="panel panel-dashboard-main form-horizontal">
					<div class="panel-heading">
						<h2 class="tit"><?php esc_html_e( 'Main Controls', 'pixel-caffeine' ); ?></h2>
					</div>
					<div class="panel-body">
						<div class="data-group status<?php echo PixelCaffeine()->is_pixel_enabled() ? ' active' : ' inactive'; ?>">
							<span class="data"><?php esc_html_e( 'Status', 'pixel-caffeine' ); ?></span>
							<div class="value">
								<span class="value-info">
									<?php echo PixelCaffeine()->is_pixel_enabled() ? esc_html_x( 'ON', 'Plugin status ON', 'pixel-caffeine' ) : esc_html_x( 'PAUSED', 'Plugin status OFF', 'pixel-caffeine' ); ?>
								</span>
							</div>
						</div>

						<div class="data-group">
							<span class="data"><?php esc_html_e( 'Advanced tracking', 'pixel-caffeine' ); ?></span>
							<div class="value">
								<span><?php AEPC_Track::is_ca_events_active() ? esc_html_e( 'Yes', 'pixel-caffeine' ) : esc_html_e( 'No', 'pixel-caffeine' ); ?></span>
							</div>
						</div>

						<div class="data-group">
							<span class="data"><?php esc_html_e( 'eCommerce integration', 'pixel-caffeine' ); ?></span>
							<div class="value">
								<span><?php AEPC_Track::is_dpa_active() && $page->get_addons_detected() ? esc_html_e( 'Yes', 'pixel-caffeine' ) : esc_html_e( 'No', 'pixel-caffeine' ); ?></span>
							</div>
						</div>

						<div class="data-group">
							<span class="data"><?php esc_html_e( 'Conversion tracked', 'pixel-caffeine' ); ?></span>
							<div class="value">
								<a href="<?php echo esc_url( $page->get_view_url( 'tab=conversions' ) ); ?>">
									<?php
									/* translators: %d: number of active tracking */
									$page->conversions_count( __( '%d active tracking', 'pixel-caffeine' ), __( '%d active trackings', 'pixel-caffeine' ) );
									?>
								</a>
							</div>
						</div>

						<div class="data-group data-group-pixel">
							<span class="data"><?php esc_html_e( 'Pixel', 'pixel-caffeine' ); ?></span>
							<div class="value">
								<span><?php echo wp_kses_post( $page->get_pixel_status() ); ?></span>
							</div>
						</div>

						<a class="btn btn-settings" href="<?php echo esc_url( $page->get_view_url( 'tab=general-settings' ) ); ?>"><?php esc_html_e( 'Change settings', 'pixel-caffeine' ); ?></a>
					</div>
				</div><!-- ./panel-dashboard-main -->

					<?php $page->get_template_part( 'activity-box' ); ?>

				<?php endif; ?>
			</form>
		</div><!-- ./plugin-content -->

		<?php $page->get_template_part( 'sidebar' ); ?>
	</section>

	</div><!--/.wrap -->
</div><!--/.pixel-caffeine-wrapper -->
