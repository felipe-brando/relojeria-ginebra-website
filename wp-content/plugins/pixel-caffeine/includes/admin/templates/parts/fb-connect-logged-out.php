<?php
/**
 * HTML for the facebook connect box when user is logged out
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

<article class="sub-panel sub-panel-fb-connect">
	<div class="control-label">
		<h3 class="tit">
			<?php esc_html_e( 'Facebook Connect', 'pixel-caffeine' ); ?>
			<a href="#_" class="btn btn-fab btn-help btn-fab-mini" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Connect your Ad account in Pixel Caffeine', 'pixel-caffeine' ); ?>"></a>
		</h3>
	</div>
	<p class="text"><?php esc_html_e( 'The easiest whay to get up and running with all the advanced features. Connect your Facebook account and you\'re good to go!', 'pixel-caffeine' ); ?></p>
	<div class="alert alert-lite alert-warning">
		<?php
		printf(
			/* translators: 1: opening tag for the link to https://www.facebook.com/help/148233965247823, 2: closing link tag, 3: opening strong tag, 4: closing strong tag */
			esc_html__( '%3$sWarning: %4$s You must have the Two-factor Authentication %1$senabled in your Facebook Account%2$s!', 'pixel-caffeine' ),
			'<a href="https://www.facebook.com/help/148233965247823" target="_blank">',
			'</a>',
			'<strong>',
			'</strong>'
		)
		?>
	</div>

	<a href="<?php echo esc_url( $fb->get_login_url() ); ?>" class="btn btn-primary btn-raised btn-fb-connect btn-block">
		<?php esc_html_e( 'Facebook Connect', 'pixel-caffeine' ); ?>
	</a>
</article><!-- ./sub-panel -->
