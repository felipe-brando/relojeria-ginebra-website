<?php
/**
 * Logs list table
 *
 * @var AEPC_Admin_View $page
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\Logs\LogRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Variables required in this template
 */
if ( ! isset( $page ) ) {
	throw new InvalidArgumentException( 'The template ' . __FILE__ . ' must have $page as instance of AEPC_Admin_View passed in.' );
}

$log_repository = new LogRepository();
$limit          = apply_filters( 'aepc_logs_per_page', 20 );
$page_num       = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) ?: 1;
$offset         = $limit * ( $page_num - 1 );
$logs           = $log_repository->find_all( array( 'date' => 'DESC' ), $limit, $offset );
$logs_count     = $log_repository->get_count_all();

?>

<div class="panel panel-log-list">
	<div class="panel-heading">
		<h2 class="tit"><?php esc_html_e( 'Logs', 'pixel-caffeine' ); ?></h2>
		<?php if ( $logs ) : ?>
		<a class="btn js-remove-logs" href="#_"><?php esc_html_e( 'Clear all', 'pixel-caffeine' ); ?></a>
		<?php endif; ?>
	</div>
	<div class="panel-body">
		<?php if ( $logs ) : ?>
		<table class="table table-striped table-hover js-table">
			<thead>
			<tr>
				<th class="name"><?php esc_html_e( 'Exception', 'pixel-caffeine' ); ?></th>
				<th><?php esc_html_e( 'Date', 'pixel-caffeine' ); ?></th>
				<th><?php esc_html_e( 'Message', 'pixel-caffeine' ); ?></th>
				<th class="actions"><?php esc_html_e( 'Actions', 'pixel-caffeine' ); ?></th>
			</tr>
			</thead>
			<tbody>

			<?php foreach ( $logs as $log ) : ?>
			<tr>
				<td class="exception">
					<strong>
						<?php
						$exception = explode( '\\', $log->get_exception() );
						echo esc_html( array_pop( $exception ) );
						?>
					</strong>
				</td>
				<td class="date">
					<?php echo esc_html( $page->get_human_date( $log->get_date(), 'h_time' ) ); ?>
					<small class="info-extra"><?php echo esc_html( $page->get_human_date( $log->get_date(), 't_time' ) ); ?></small>
				</td>
				<td>
					<?php echo wp_kses_post( $log->get_message() ); ?>
				</td>
				<td class="actions">
					<div class="btn-group-sm">
						<a href="
						<?php
						echo esc_url_raw(
							wp_nonce_url(
								$page->get_view_url(
									array(
										'action' => 'aepc_download_log_report',
										'log'    => $log->get_id(),
									)
								),
								'aepc_download_log_report'
							)
						)
						?>
						"
							target="_blank"
							class="btn btn-download btn-primary btn-raised"
						>
							<?php esc_html_e( 'Download report', 'pixel-caffeine' ); ?>
						</a>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>

			</tbody>
		</table>
		<?php else : ?>
			<p class="text"><?php esc_html_e( 'No logs registered yet.', 'pixel-caffeine' ); ?></p>
		<?php endif ?>
	</div>
	<!-- ./panel-body -->

	<?php
	if ( $logs_count ) {
		echo wp_kses_post(
			$page->get_pagination(
				$logs_count,
				array(
					'per_page'           => $limit,
					'list_wrap'          => '<div class="panel-footer"><ul class="pagination pagination-sm">%1$s</ul></div>',
					'item_wrap'          => '<li>%1$s</li>',
					'item_wrap_active'   => '<li class="active">%1$s</li>',
					'item_wrap_disabled' => '<li class="disabled">%1$s</li>',
				)
			)
		);
	}
	?>
</div>
<!-- ./panel-ca-list -->

<?php $page->get_template_part(
	'modals/confirm-delete',
	array(
		'modal_title' => __( 'Remove all logs', 'pixel-caffeine' ),
		'message'     => __( 'Are you sure you want to remove all logs?', 'pixel-caffeine' ),
	)
) ?>
