<?php
/**
 * Template for the created status of the product catalog
 *
 * @var AEPC_Admin_View $page
 * @var ProductCatalogManager $product_catalog
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $product_catalog ) ) {
	return;
}

/**
 * Variables required in this template
 */
if ( ! isset( $page ) ) {
	throw new InvalidArgumentException( 'The template ' . __FILE__ . ' must have $page as instance of AEPC_Admin_View passed in.' );
}

// This flag could be passed in the template to forcing to show the refreshing status (used in the ajax call).
if ( ! isset( $is_refreshing ) ) {
	$is_refreshing = false;
}

$products_count = $product_catalog->get_entity()->get_products_count();
$errormsg       = $product_catalog->get_entity()->get_last_error_message()

?>

<div class="panel panel-feed-info panel-prd-catalog panel-feed-created form-horizontal js-product-feed-info<?php echo $is_refreshing ? ' updating' : ''; ?><?php echo ! AEPC_Admin::$product_catalogs_service->is_product_catalog_enabled() ? ' disabled-box' : ''; ?>">

	<div class="panel-heading">
		<h2 class="tit"><?php esc_html_e( 'Generate Product Feed', 'pixel-caffeine' ); ?></h2>
	</div>

	<div class="panel-body">

		<!--Feed URL-->
		<div class="data-group data-group-main with-actions">

			<span class="data"><?php esc_html_e( 'URL', 'pixel-caffeine' ); ?>:</span>

			<div class="value">
				<?php if ( $is_refreshing ) : ?>
					<span class="value-info text-status-pending url-feed-alert loading-data">
						<?php esc_html_e( 'The feed is refreshing, this process may take a few minutes!', 'pixel-caffeine' ); ?>
					</span>

				<?php elseif ( $errormsg ) : ?>
					<span class="value-info text-danger url-feed-danger">
						<?php echo wp_kses_post( $errormsg ); ?>
					</span>
					<div class="actions">
						<button type="button" class="btn btn-default js-product-feed-refresh"><?php esc_html_e( 'Try again', 'pixel-caffeine' ); ?></button>
					</div>

				<?php elseif ( ! $product_catalog->get_feed_directory_helper()->is_feed_existing() ) : ?>
					<span class="value-info text-danger url-feed-danger">
						<?php esc_html_e( 'The file xml it seems not existing. Click into the "Try Again" button in order to refresh it!', 'pixel-caffeine' ); ?>
					</span>
					<div class="actions">
						<button type="button" class="btn btn-default js-product-feed-refresh"><?php esc_html_e( 'Try again', 'pixel-caffeine' ); ?></button>
					</div>

				<?php else : ?>
					<!--generete link-->
					<span class="value-info">
						<a class="feed-url" href="<?php echo esc_url( $product_catalog->get_url() ); ?>" rel="nofollow" target="_blank">
							<?php echo esc_url( $product_catalog->get_url() ); ?>
						</a>
					</span>
				<?php endif; ?>

				<?php if ( ! $is_refreshing ) : ?>
					<div class="actions">
						<div class="btn-group-sm">
							<a
								href="#_"
								class="btn btn-fab btn-edit btn-primary js-feed-edit"
								data-feed-id="<?php echo esc_attr( $product_catalog->get_entity()->get_id() ); ?>"
								data-tooltip=""
								title=""
								data-original-title="Edit"
							></a><!--buttons-->

							<a
								href="#_"
								class="btn btn-fab btn-fab-mini btn-delete btn-danger js-feed-delete"
								data-feed-id="<?php echo esc_attr( $product_catalog->get_entity()->get_id() ); ?>"
								data-tooltip=""
								title=""
								data-original-title="Delete"
							></a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $product_catalog->configuration()->get( Configuration::OPTION_FB_ENABLE ) ) : ?>
		<!--FB Product Feed ID-->
		<div class="data-group data-group-main">
			<span class="data"><?php esc_html_e( 'FB Product Feed ID', 'pixel-caffeine' ); ?>:</span>
			<div class="value">
				<span class="value-info">
									<?php
									printf(
										'<a class="facebook-feed-id" href="https://www.facebook.com/products/catalogs/%1$s/feeds/%2$s/overview" target="_blank">%2$s</a>',
										esc_attr( $product_catalog->configuration()->get( Configuration::OPTION_FB_PRODUCT_CATALOG_ID ) ),
										esc_attr( $product_catalog->configuration()->get( Configuration::OPTION_FB_PRODUCT_FEED_ID ) )
									)
									?>
							</span>
			</div>
		</div>
		<?php endif; ?>

		<!--Feed Product contains-->
		<div class="data-group ">
			<span class="data"><?php esc_html_e( 'Your product feed contains', 'pixel-caffeine' ); ?>:</span>
			<div class="value">
				<span class="value-info prd-feed-number">
					<?php
					/* translators: %s: number of products (complete example: Your product feed contains 5 products) */
					printf( esc_html( _n( '%d product', '%d products', $products_count, 'pixel-caffeine' ) ), esc_html( number_format_i18n( $products_count ) ) );
					?>
				</span>
			</div>
		</div>


		<!--Set refersh Feed-->
		<div class="form-horizontal-inline">
			<div class="form-group multiple-fields-inline js-refresh-interval-option set-refresh">
				<label class="control-label" for="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_REFRESH_CYCLE ); ?>">
					<?php esc_html_e( 'Refresh every', 'pixel-caffeine' ); ?>:
				</label>

				<div class="control-wrap">
					<select class="form-control" name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_REFRESH_CYCLE ); ?>" id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_REFRESH_CYCLE ); ?>">
						<?php foreach ( array( 1, 2, 6 ) as $cycle ) : ?>
							<option value="<?php echo esc_attr( (string) $cycle ); ?>" <?php selected( $cycle, $page->get_feed_field_value( $product_catalog, Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_REFRESH_CYCLE ) ); ?>>
								<?php
								/* translators: %s: hour cycle expressed in number (example: Refresh every 5 hours) */
								printf( esc_html( _n( '%d hour', '%d hours', $cycle, 'pixel-caffeine' ) ), esc_html( (string) $cycle ) );
								?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="actions">
					<button type="button" class="btn btn-primary btn-raised js-product-feed-save-interval" data-feed-id="<?php echo esc_attr( $product_catalog->get_entity()->get_id() ); ?>"><?php esc_html_e( 'Apply', 'pixel-caffeine' ); ?></button>
					<button type="button" class="btn btn-default btn-refresh js-product-feed-refresh"> <?php esc_html_e( 'Refresh now', 'pixel-caffeine' ); ?> </button>
					<small class="text">
					<?php
						printf(
							/* translators: %s: 2 minutes (complete example: Generated 2 minutes ago) */
							esc_html__( 'Generated %s ago', 'pixel-caffeine' ),
							esc_html( human_time_diff( $product_catalog->get_entity()->get_last_update_date()->getTimestamp() ) )
						);
						?>
						</small>
				</div>

			</div>
		</div>
	</div>
	<div class="panel-body form-horizontal js-edit-form collapse">
		<hr>

		<?php
		$page->get_form_fields(
			'product-catalog',
			array(
				'action'          => 'update',
				'product_catalog' => $product_catalog,
			)
		);
		?>
	</div>
</div>
