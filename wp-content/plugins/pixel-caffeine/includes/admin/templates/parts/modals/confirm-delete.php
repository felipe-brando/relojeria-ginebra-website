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

$modal_title = isset( $modal_title ) ? $modal_title : __( 'Confirm delete action', 'pixel-caffeine' );
$message     = isset( $message ) ? $message : __( 'Are you sure you really want delete this item?', 'pixel-caffeine' );

?>

<!-- Delete modal -->
<div id="modal-confirm-delete" class="modal fade modal-centered modal-confirm modal-delete" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title"><?php echo esc_html( $modal_title ); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				if ( ! empty( $message ) ) :
					?>
					<p><?php echo esc_html( $message ); ?></p><?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e( 'No', 'pixel-caffeine' ); ?></button>
				<button type="button" class="btn btn-raised btn-danger btn-delete btn-ok"><?php esc_html_e( 'Yes', 'pixel-caffeine' ); ?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
