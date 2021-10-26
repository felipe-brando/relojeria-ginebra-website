<?php
/**
 * Conversion event tracking table
 *
 * @var AEPC_Admin_View $page
 *
 * @package Pixel Caffeine
 *
 * @phpcs:disable VariableAnalysis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$conversions = $page->get_conversions();

if ( empty( $conversions ) ) {
	return;
}

?>

<div class="panel panel-ce-tracking">
	<div class="panel-heading"><h2 class="tit"><?php esc_html_e( 'Active tracking', 'pixel-caffeine' ); ?></h2></div>
	<div class="panel-body">
		<table class="table table-striped table-hover js-table">
			<thead>
			<tr>
				<th class="name"><?php esc_html_e( 'Name', 'pixel-caffeine' ); ?></th>
				<th class="url"><?php esc_html_e( 'Trigger', 'pixel-caffeine' ); ?></th>
				<th><?php esc_html_e( 'Code', 'pixel-caffeine' ); ?></th>
				<th class="actions"><?php esc_html_e( 'Actions', 'pixel-caffeine' ); ?></th>
			<tr>
			</thead>
			<tbody>

			<?php foreach ( $conversions as $i => $event ) : ?>
				<tr>
					<td class="name"><?php echo esc_html( $event['name'] ); ?></td>
					<td class="url">
						<?php
						if ( 'page_visit' === $event['trigger'] ) {
							esc_html_e( 'Page visit', 'pixel-caffeine' );
							if ( 'contains' === $event['url_condition'] ) {
								printf( '<br />%s: %s', esc_html__( 'URL contains', 'pixel-caffeine' ), esc_html( $event['url'] ) );
							} elseif ( 'exact' === $event['url_condition'] ) {
								printf( '<br />%s: %s', esc_html__( 'URL is exact', 'pixel-caffeine' ), esc_html( $event['url'] ) );
							}
						} elseif ( 'link_click' === $event['trigger'] ) {
							esc_html_e( 'Link click', 'pixel-caffeine' );
							if ( 'contains' === $event['url_condition'] ) {
								printf( '<br />%s: %s', esc_html__( 'URL contains', 'pixel-caffeine' ), esc_html( $event['url'] ) );
							} elseif ( 'exact' === $event['url_condition'] ) {
								printf( '<br />%s: %s', esc_html__( 'URL is exact', 'pixel-caffeine' ), esc_html( $event['url'] ) );
							}
						} elseif ( 'css_selector' === $event['trigger'] ) {
							esc_html_e( 'On click css selector', 'pixel-caffeine' );
							printf( '<br />"%s"', esc_html( $event['css'] ) );
						} elseif ( 'js_event' === $event['trigger'] ) {
							esc_html_e( 'On Javascript Event', 'pixel-caffeine' );
							/* translators: 1: JS Event Element 2: JS Event Name, in "Conversions/Events" page table */
							printf( '<br />' . esc_html__( 'When "%1$s" triggers "%2$s"', 'pixel-caffeine' ), esc_html( $event['js_event_element'] ), esc_html( $event['js_event_name'] ) );
						}
						?>
					</td>
					<td>
						<a
							href="#_"
							class="show-code"
							tabindex="<?php echo esc_attr( $i ); ?>"
							data-toggle="popover"
							data-trigger="focus"
							data-content="<?php echo esc_attr( $page->get_track_code( $event ) ); ?>"><?php esc_html_e( 'Show code', 'pixel-caffeine' ); ?></a>
					</td>
					<td class="actions">
						<div class="btn-group-sm">
							<a
								href="
								<?php
								echo esc_url(
									wp_nonce_url(
										add_query_arg(
											array(
												'action' => 'conversion-delete',
												'id'     => $i,
											),
											$page->get_view_url()
										),
										'delete_tracking_conversion'
									)
								);
								?>
										"
								class="btn btn-fab btn-delete btn-danger js-conversion-delete"
								data-toggle="modal" data-target="#modal-confirm-delete" data-remote="false"
							></a>
							<a href="#_" class="btn btn-fab btn-edit btn-primary"<?php $page->conversion_data_values( $i ); ?> data-toggle="modal" data-target="#modal-conversion-edit"></a>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>

			</tbody>
		</table>
	</div>

	<?php
	$page->conversions_pagination(
		array(
			'list_wrap'          => '<div class="panel-footer"><ul class="pagination pagination-sm">%1$s</ul></div>',
			'item_wrap'          => '<li>%1$s</li>',
			'item_wrap_active'   => '<li class="active">%1$s</li>',
			'item_wrap_disabled' => '<li class="disabled">%1$s</li>',
		)
	)
	?>
</div>
