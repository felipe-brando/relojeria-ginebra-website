<?php
/**
 * General admin settings page
 *
 * This is the HTML of the panel for facebook pixel options of general settings
 *
 * @var AEPC_Admin_View $page
 * @var AEPC_Facebook_Adapter $fb
 *
 * @package Pixel Caffeine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="panel panel-settings-ss">
	<div class="panel-heading">
		<h2 class="tit"><?php esc_html_e( 'Server Side Tracking', 'pixel-caffeine' ); ?></h2>
		<div class="form-group form-toggle">
			<label for="<?php $page->field_id( 'aepc_enable_server_side' ); ?>" class="control-label"><?php esc_html_e( 'Enable', 'pixel-caffeine' ); ?></label>
			<div class="togglebutton
			<?php
			if (
				'yes' === $page->get_value( 'aepc_enable_server_side' ) && (
					empty( $page->get_value( 'aepc_server_side_access_token' ) )
					|| ! PixelCaffeine()->is_pixel_enabled()
				)
			) {
				echo ' pending';}
			?>
			">
				<label>
					<input
						type="checkbox"
						name="<?php $page->field_name( 'aepc_enable_server_side' ); ?>"
						id="<?php $page->field_id( 'aepc_enable_server_side' ); ?>"
						class="js-switch-labeled-tosave"
						data-original-value="<?php echo esc_attr( $page->get_value( 'aepc_enable_server_side' ) ); ?>"
						<?php checked( $page->get_value( 'aepc_enable_server_side' ), 'yes' ); ?>>
				</label>
			</div>
			<?php if ( 'no' === $page->get_value( 'aepc_enable_server_side' ) ) : ?>
				<span class="text-status text-status-on text-danger"><?php esc_html_e( 'Server Side is OFF!', 'pixel-caffeine' ); ?></span>
			<?php elseif ( ! PixelCaffeine()->is_pixel_enabled() ) : ?>
				<span class="text-status text-status-pending"><?php esc_html_e( 'Server Side is OFF, you have to set the Pixel ID!', 'pixel-caffeine' ); ?></span>
			<?php elseif ( empty( $page->get_value( 'aepc_server_side_access_token' ) ) ) : ?>
				<span class="text-status text-status-pending"><?php esc_html_e( 'Server Side is OFF, you have to set the access token!', 'pixel-caffeine' ); ?></span>
			<?php else : ?>
				<span class="text-status text-status-on text-success"><?php esc_html_e( 'Server Side is ON!', 'pixel-caffeine' ); ?></span>
			<?php endif; ?>
		</div>
	</div><!-- ./panel-heading -->

	<div class="panel-body">
		<p><strong><?php esc_html_e( 'Server Side Tracking', 'pixel-caffeine' ); ?>:</strong> <?php esc_html_e( 'Enabling Server Side tracking allows you to send web events from the server directly to Facebook. Server events are linked to a pixel and are processed like browser pixel events. This means that server events are used in measurement, reporting, and optimization in the same way as browser pixel events.', 'pixel-caffeine' ); ?></p>

		<div class="form-group form-track form-horizontal">
			<div class="control-label">
				<h3 class="tit"><?php esc_html_e( 'Access token', 'pixel-caffeine' ); ?>
					<a href="#_" class="btn btn-fab btn-fab-mini btn-help" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'The access token for the server side tracking, unlinked from the Facebook Login above.', 'pixel-caffeine' ); ?>"></a>
				</h3>
			</div>
			<div class="control-wrap">
				<input
					type="text"
					class="form-control"
					value="<?php echo esc_attr( $page->get_value( 'aepc_server_side_access_token' ) ); ?>"
					name="<?php $page->field_name( 'aepc_server_side_access_token' ); ?>"
					id="<?php $page->field_id( 'aepc_server_side_access_token' ); ?>" />
			</div>
			<p><?php echo wp_kses_post( make_clickable( __( 'Please, read the doc in order to know about how to generate the access token for Server Side Tracking: https://developers.facebook.com/docs/marketing-api/conversions-api/get-started#access-token.', 'pixel-caffeine' ) ) ); ?></p>
		</div>

		<div class="form-group">
			<div class="control-wrap">
				<div class="checkbox">
					<label>
						<input
								type="checkbox"
								name="<?php $page->field_name( 'aepc_server_side_log_events' ); ?>"
								id="<?php $page->field_id( 'aepc_server_side_log_events' ); ?>"
								<?php checked( $page->get_value( 'aepc_server_side_log_events' ), 'yes' ); ?>>
						<?php esc_html_e( 'Log all events sent', 'pixel-caffeine' ); ?>
					</label>
					<small class="text"><?php esc_html_e( 'You will see all server side events sent in the "Logs" page. Please, keep in mind that this might register many records in the database according to your visits. We recommend to enable it only if needed to debug events and keep it disabled normally.', 'pixel-caffeine' ); ?></small>
				</div>
			</div><!-- ./control-wrap -->
		</div><!-- ./form-group -->

		<div class="form-group">
			<div class="control-wrap">
				<div class="checkbox">
					<label>
						<input
								type="checkbox"
								name="<?php $page->field_name( 'aepc_server_side_track_in_ajax' ); ?>"
								id="<?php $page->field_id( 'aepc_server_side_track_in_ajax' ); ?>"
								<?php checked( $page->get_value( 'aepc_server_side_track_in_ajax' ), 'yes' ); ?>>
						<?php esc_html_e( 'Track through AJAX', 'pixel-caffeine' ); ?>
					</label>
					<small class="text"><?php esc_html_e( 'This tracks all server-side events through an AJAX request in order to avoid any server side and/or any page cache enabled by hostings and/or caching plugins.', 'pixel-caffeine' ); ?></small>
				</div>
			</div><!-- ./control-wrap -->
		</div><!-- ./form-group -->

	</div>
</div>
