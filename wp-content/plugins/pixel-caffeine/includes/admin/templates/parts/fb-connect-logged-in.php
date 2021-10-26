<?php
/**
 * HTML for the facebook connect box when user is logged in
 *
 * @var AEPC_Admin_View $page
 * @var AEPC_Facebook_Adapter $fb
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Admin\Exception\FBAPIException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Variables required in this template
 */
if ( ! isset( $page ) ) {
	throw new InvalidArgumentException( 'The template ' . __FILE__ . ' must have $page as instance of AEPC_Admin_View passed in.' );
}

/**
 * Variables required in this template
 */
if ( ! isset( $fb ) ) {
	throw new InvalidArgumentException( 'The template ' . __FILE__ . ' must have $fb as instance of AEPC_Facebook_Adapter passed in.' );
}

try {
	$user_error = $fb->get_user();
} catch ( Exception $e ) {
	/* translators: %s: the error from the facebook API */
	$user_error = sprintf( esc_html__( 'Error fetching the user info: %s', 'pixel-caffeine' ), $e->getMessage() );
}

?>

<article class="sub-panel sub-panel-fb-connect active">
	<h3 class="tit">
		<?php esc_html_e( 'Facebook Connect', 'pixel-caffeine' ); ?>
		<a href="#_" class="btn btn-fab btn-help btn-fab-mini" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'One click setup! Recommended option', 'pixel-caffeine' ); ?>"></a>
	</h3>

	<?php if ( is_wp_error( $user_error ) ) : ?>
		<?php $page->print_notice( 'error', $user_error->get_error_message() ); ?>

	<?php elseif ( is_string( $user_error ) ) : ?>
		<?php $page->print_notice( 'error', $user_error ); ?>

		<?php
	else :

		try {
			$account    = '';
			$pixel      = '';
			$account_id = $fb->get_account_id();
			$pixel_id   = $fb->get_pixel_id();

			if ( $account_id ) {
				$account = array(
					'id'   => $account_id,
					'name' => $fb->get_account_name(),
				);
			}

			if ( $pixel_id ) {
				$pixel = array(
					'id'   => $pixel_id,
					'name' => $fb->get_pixel_name(),
				);
			}

			?>
		<div class="fb-connect-info">
			<span class="pixel-id"><?php esc_html_e( 'Pixel ID', 'pixel-caffeine' ); ?>: <strong class="pixel-id-value">#<?php echo esc_html( $fb->get_pixel_id() ); ?></strong></span>

			<div class="user-info">

				<img class="user-avatar" src="<?php echo esc_url( $fb->get_user_photo_uri() ); ?>">

				<div class="user-info-account">
					<span class="user-ad-account"><?php esc_html_e( 'Ad Account', 'pixel-caffeine' ); ?>: <strong class="user-ad-account-value"><?php echo esc_html( $fb->get_account_name() ); ?></strong></span>
					<span class="user-name"><?php echo esc_html( $fb->get_user_name() ); ?></span>
				</div>

			</div>
		</div>
		<div class="user-actions">
			<a href="<?php echo esc_url( $fb->get_logout_url() ); ?>" class="user-disconnect" data-toggle="modal" data-target="#modal-confirm-disconnect-fb" data-remote="false"><?php esc_html_e( 'Disconnect', 'pixel-caffeine' ); ?></a>
			<a href="#_" class="user-edit" data-toggle="modal" data-target="#modal-fb-connect-options"><?php esc_html_e( 'Edit', 'pixel-caffeine' ); ?></a>

			<input type="hidden" name="aepc_account_id" id="aepc_account_id" value="<?php echo esc_attr( wp_json_encode( $account ) ?: '' ); ?>" />
			<input type="hidden" name="aepc_pixel_id" id="aepc_pixel_id" value="<?php echo esc_attr( wp_json_encode( $pixel ) ?: '' ); ?>" />
		</div>
			<?php
		} catch ( FBAPIException $e ) {
			/* translators: %s: the error from the facebook API */
			$page->print_notice( 'error', sprintf( esc_html__( 'Error fetching the FB account info: %s', 'pixel-caffeine' ), $e->getMessage() ) );
		} catch ( Exception $e ) {
			/* translators: %s: the error from the facebook API */
			$page->print_notice( 'error', sprintf( esc_html__( 'Error fetching the FB account info: %s', 'pixel-caffeine' ), $e->getMessage() ) );
		}
		?>
	<?php endif; ?>
</article>
