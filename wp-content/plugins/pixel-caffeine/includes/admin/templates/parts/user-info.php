<?php
/**
 * General admin settings page
 *
 * This is the template with the HTML code for the General Settings admin page
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

<div class="user-info">
	<img class="user-avatar" src="<?php echo esc_url( $fb->get_user_photo_uri() ); ?>">
	<div class="user-info-account">
		<?php esc_html_e( 'You are connected to Facebook as', 'pixel-caffeine' ); ?>
		<strong class="user-name"><?php echo esc_html( $fb->get_user_name() ); ?></strong>.
		<a href="<?php echo esc_url( $fb->get_logout_url() ); ?>" class="user-disconnect" data-toggle="modal" data-target="#modal-confirm-disconnect-fb" data-remote="false"><?php esc_html_e( 'Disconnect', 'pixel-caffeine' ); ?></a>
	</div>
</div>
