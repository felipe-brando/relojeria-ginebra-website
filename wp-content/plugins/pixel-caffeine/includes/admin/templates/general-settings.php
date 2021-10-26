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

$fb = AEPC_Admin::$api;

$highlight_fbpixel = filter_input( INPUT_GET, 'ref', FILTER_SANITIZE_STRING ) === 'fblogin' && $fb->is_logged_in() && $fb->get_account_id() === '';

?>

<div class="pixel-caffeine-wrapper">
	<div class="wrap wrap-settings">

		<h1 class="page-title"><?php $page->the_title(); ?></h1>

		<?php $page->get_template_part( 'nav-tabs' ); ?>

		<section class="plugin-sec<?php echo $highlight_fbpixel ? ' sec-overlay' : ''; ?>">
			<div class="plugin-content">

				<div class="alert-wrap">
					<?php $page->print_notices(); ?>
				</div>

				<form method="post" id="mainform">

					<?php $page->get_template_part( 'panels/set-facebook-pixel', array( 'fb' => $fb ) ); ?>

					<?php $page->get_template_part( 'panels/server-side-settings', array( 'fb' => $fb ) ); ?>

					<div class="panel panel-settings-ca">
						<div class="panel-heading">
							<h2 class="tit"><?php esc_html_e( 'Custom audiences', 'pixel-caffeine' ); ?></h2>
							<div class="form-group form-toggle">
								<label for="<?php $page->field_id( 'aepc_enable_advanced_events' ); ?>" class="control-label"><?php esc_html_e( 'Enable', 'pixel-caffeine' ); ?></label>
								<div class="togglebutton">
									<label>
										<input
											type="checkbox"
											name="<?php $page->field_name( 'aepc_enable_ca_events' ); ?>"
											id="<?php $page->field_id( 'aepc_enable_ca_events' ); ?>"
											class="js-switch-labeled-tosave"
											data-original-value="<?php echo esc_attr( $page->get_value( 'aepc_enable_ca_events' ) ); ?>"
											<?php checked( $page->get_value( 'aepc_enable_ca_events' ), 'yes' ); ?>>
									</label>
								</div>
								<?php if ( 'yes' === $page->get_value( 'aepc_enable_ca_events' ) ) : ?>
									<span class="text-status text-status-on text-success"><?php esc_html_e( 'Advanced Tracking is ON!', 'pixel-caffeine' ); ?></span>
								<?php else : ?>
									<span class="text-status text-status-on text-danger"><?php esc_html_e( 'Advanced Tracking is OFF!', 'pixel-caffeine' ); ?></span>
								<?php endif; ?>
							</div>
						</div><!-- ./panel-heading -->

						<div class="panel-body">
							<p><strong><?php esc_html_e( 'Advanced tracking info', 'pixel-caffeine' ); ?>:</strong> <?php esc_html_e( 'Get the most out of Pixel Caffeine enabling our advanced tracking to create Custom Audiences based on WP custom fields, taxonomies and more.', 'pixel-caffeine' ); ?></p>
							<div class="form-group form-track form-horizontal">
								<div class="control-label">
									<h3 class="tit"><?php esc_html_e( 'Track Custom Fields Based Events', 'pixel-caffeine' ); ?>
										<a href="#_" class="btn btn-fab btn-fab-mini btn-help" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Automatically add the value of specific post metas you define below in the pixel that Pixel Caffeine will track.', 'pixel-caffeine' ); ?>"></a>
									</h3>
								</div>
								<div class="control-wrap">
									<input
										type="text"
										class="form-control multi-tags custom-fields"
										value="<?php echo esc_attr( $page->get_value( 'aepc_custom_fields_event' ) ); ?>"
										name="<?php $page->field_name( 'aepc_custom_fields_event' ); ?>"
										id="<?php $page->field_id( 'aepc_custom_fields_event' ); ?>" />
								</div>
								<p><?php esc_html_e( 'Start typing the name of the custom fields you want to track to create laser-focused Custom Audiences!', 'pixel-caffeine' ); ?></p>
							</div>

							<div class="sub-panel sub-panel-adv-opt">
								<h4 class="tit"><?php esc_html_e( 'Advanced data tracking', 'pixel-caffeine' ); ?></h4>
								<div class="form-group">
									<div class="control-wrap">
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_advanced_events' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_advanced_events' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_advanced_events' ), 'yes' ); ?>>
												<?php esc_html_e( 'Enable advanced tracking', 'pixel-caffeine' ); ?>
											</label>
											<small class="text"><?php esc_html_e( 'Enable to track post type, login status, browser info and more.', 'pixel-caffeine' ); ?></small>
										</div>
									</div><!-- ./control-wrap -->
								</div><!-- ./form-group -->

								<div class="form-group">
									<div class="control-wrap">
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_taxonomy_events' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_taxonomy_events' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_taxonomy_events' ), 'yes' ); ?>>
												<?php esc_html_e( 'Enable taxonomy tracking', 'pixel-caffeine' ); ?>
											</label>
											<small class="text"><?php esc_html_e( 'Enable to track custom taxnomies for each page or post.', 'pixel-caffeine' ); ?></small>
										</div>
									</div><!-- ./control-wrap -->
								</div><!-- ./form-group -->

								<div class="form-group">
									<div class="control-wrap">
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_utm_tags' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_utm_tags' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_utm_tags' ), 'yes' ); ?>>
												<?php esc_html_e( 'Enable UTM tracking', 'pixel-caffeine' ); ?>
											</label>
											<small class="text"><?php esc_html_e( 'Add UTM tags as parameters in all Pixels if they exist.', 'pixel-caffeine' ); ?></small>
										</div>
									</div><!-- ./control-wrap -->
								</div><!-- ./form-group -->

								<div class="form-group">
									<div class="control-wrap">
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_advanced_matching' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_advanced_matching' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_advanced_matching' ), 'yes' ); ?>>
												<?php esc_html_e( 'Enable Advanced Matching', 'pixel-caffeine' ); ?>
											</label>
											<small class="text"><?php esc_html_e( 'Enable the advanced matching in the pixels.', 'pixel-caffeine' ); ?></small>
										</div>
									</div><!-- ./control-wrap -->
								</div><!-- ./form-group -->

								<div class="form-group">
									<div class="control-wrap">
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_search_event' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_search_event' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_search_event' ), 'yes' ); ?>>
												<?php esc_html_e( 'Enable Search Event', 'pixel-caffeine' ); ?>
											</label>
											<small class="text"><?php esc_html_e( 'Enable the automatic tracking of the "Search" event when a user performs a search.', 'pixel-caffeine' ); ?></small>
										</div>
									</div><!-- ./control-wrap -->
								</div><!-- ./form-group -->
							</div>
						</div><!-- ./panel-body -->
					</div><!-- ./panel-settings-set-fb-px -->




					<div class="panel panel-settings-conversions<?php echo $page->get_addons_detected() ? ' detected' : ' not-detected'; ?>">
						<div class="panel-heading">
							<h2 class="tit"><?php esc_html_e( 'Conversions', 'pixel-caffeine' ); ?></h2>
							<div class="form-group form-toggle">
								<label for="<?php $page->field_id( 'aepc_enable_dpa' ); ?>" class="control-label"><?php esc_html_e( 'Enable', 'pixel-caffeine' ); ?></label>
								<div class="togglebutton">
									<label>
										<input
											type="checkbox"
											name="<?php $page->field_name( 'aepc_enable_dpa' ); ?>"
											id="<?php $page->field_id( 'aepc_enable_dpa' ); ?>"
											class="js-switch-labeled-tosave"
											data-original-value="<?php echo esc_attr( $page->get_value( 'aepc_enable_dpa' ) ); ?>"
											<?php checked( $page->get_value( 'aepc_enable_dpa' ), 'yes' ); ?>>
									</label>
								</div>
								<?php if ( 'yes' === $page->get_value( 'aepc_enable_dpa' ) ) : ?>
									<span class="text-status text-status-on text-success"><?php esc_html_e( 'eCommerce Tracking is ON!', 'pixel-caffeine' ); ?></span>
								<?php else : ?>
									<span class="text-status text-status-on text-danger"><?php esc_html_e( 'eCommerce Tracking is OFF!', 'pixel-caffeine' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<div class="panel-body">
							<?php if ( $page->get_addons_detected() ) : ?>
								<div class="ecomm-detect">
									<h3 class="tit">
										<?php esc_html_e( 'eCommerce plugin detected', 'pixel-caffeine' ); ?>:
										<?php foreach ( $page->get_addons_detected() as $addon ) : ?>
										<span class="ecomm-plugin-logo">
											<img src="<?php echo esc_url( $addon->get_logo_img() ); ?>" title="<?php echo esc_attr( $addon->get_name() ); ?>" alt="<?php echo esc_attr( $addon->get_name() ); ?>">
										</span>
										<?php endforeach; ?>
									</h3>
								</div>

								<div class="form-group ecomm-conversions">
									<div class="control-label">
										<h3 class="tit">
											<?php esc_html_e( 'Track this eCommerce Conversions', 'pixel-caffeine' ); ?>
											<a href="#_" class="btn btn-fab btn-fab-mini btn-help" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Enable the DPA event you want to track for your website. Pixel Caffeine will automatically track it when they should from the supported plugins.', 'pixel-caffeine' ); ?>"></a>
										</h3>
									</div>

									<div class="control-wrap">

										<?php if ( AEPC_Addons_Support::is_event_supported( 'ViewContent' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_viewcontent' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_viewcontent' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_viewcontent' ), 'yes' ); ?>>
												<?php esc_html_e( 'View category', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'ViewCategory' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_viewcategory' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_viewcategory' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_viewcategory' ), 'yes' ); ?>>
												<?php esc_html_e( 'View product', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'AddToCart' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_addtocart' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_addtocart' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_addtocart' ), 'yes' ); ?>>
												<?php esc_html_e( 'Add to cart', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'InitiateCheckout' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_initiatecheckout' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_initiatecheckout' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_initiatecheckout' ), 'yes' ); ?>>
												<?php esc_html_e( 'View Checkout', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'AddPaymentInfo' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_addpaymentinfo' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_addpaymentinfo' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_addpaymentinfo' ), 'yes' ); ?>>
												<?php esc_html_e( 'Add payment info', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'Purchase' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_purchase' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_purchase' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_purchase' ), 'yes' ); ?>>
												<?php esc_html_e( 'Purchase', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'Lead' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_lead' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_lead' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_lead' ), 'yes' ); ?>>
												<?php esc_html_e( 'Lead', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>

										<?php if ( AEPC_Addons_Support::is_event_supported( 'CompleteRegistration' ) ) : ?>
										<div class="checkbox">
											<label>
												<input
													type="checkbox"
													name="<?php $page->field_name( 'aepc_enable_completeregistration' ); ?>"
													id="<?php $page->field_id( 'aepc_enable_completeregistration' ); ?>"
													<?php checked( $page->get_value( 'aepc_enable_completeregistration' ), 'yes' ); ?>>
												<?php esc_html_e( 'CompleteRegistration', 'pixel-caffeine' ); ?>
											</label>
										</div>
										<?php endif; ?>
									</div><!-- ./control-wrap -->
								</div>

							<?php else : ?>

								<div class="ecomm-detect">
									<h3 class="tit"><?php esc_html_e( 'eCommerce plugin not detected', 'pixel-caffeine' ); ?></h3>
									<span class="info">
										<?php
										printf(
											/* translators: 1: the opening tag for the link to the "Conversions/Events" tab, 2: closing link tag */
											esc_html__( 'You can still track custom conversions %1$shere%2$s', 'pixel-caffeine' ),
											'<a href="' . esc_url( $page->get_view_url( 'tab=conversions' ) ) . '">',
											'</a>'
										);
										?>
									</span>
								</div>

								<div class="sub-panel sub-panel-supported-plugin">
									<h4 class="tit"><?php esc_html_e( 'Supported plugins', 'pixel-caffeine' ); ?>:</h4>
									<ul class="list-supported-plugin">
										<?php foreach ( $page->get_addons_supported() as $addon ) : ?>
										<li class="item">
											<a href="<?php echo esc_url( $addon->get_website_url() ); ?>" class="ecomm-plugin-logo">
												<img src="<?php echo esc_url( $addon->get_logo_img() ); ?>" title="<?php echo esc_attr( $addon->get_name() ); ?>" alt="<?php echo esc_attr( $addon->get_name() ); ?>">
											</a>
										</li>
										<?php endforeach; ?>
									</ul>
								</div>

							<?php endif ?>
						</div><!-- ./panel-body -->
					</div><!-- ./panel-settings-set-fb-px -->

					<?php $page->get_template_part( 'advanced-settings' ); ?>

					<footer class="sec-footer">
						<button name="save" class="btn btn-raised btn-success btn-save btn-plugin" type="submit"><?php esc_html_e( 'Save', 'pixel-caffeine' ); ?></button>
						<input type="hidden" name="tab" value="<?php echo esc_attr( filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ); ?>" />
						<?php wp_nonce_field( 'save_general_settings' ); ?>
					</footer>
				</form>
			</div><!-- ./plugin-content -->

			<?php $page->get_template_part( 'sidebar' ); ?>
		</section>

		<?php

		if ( ! empty( $fb ) && $fb->is_logged_in() ) {
			$page->get_template_part( 'modals/confirm-disconnect-fb' );
			$page->get_template_part( 'modals/fb-connect-options', array( 'fb' => $fb ) );
		} else {
			$page->get_template_part( 'modals/confirm-disconnect-pixel' );
		}

		?>
	</div><!--/.wrap -->
</div><!--/.pixel-caffeine-wrapper -->
