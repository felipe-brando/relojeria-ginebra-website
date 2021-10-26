<?php
/**
 * Form add/edit of conversion
 *
 * @var AEPC_Admin_View $page
 * @var ProductCatalogManager $product_catalog
 * @var string $group
 * @var string $product_feed_id This is passed via AJAX when the user is selecting an existing product catalog and feed
 *
 * @package Pixel Caffeine
 */

use PixelCaffeine\ProductCatalog\Configuration;
use PixelCaffeine\ProductCatalog\ProductCatalogManager;

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
if ( ! isset( $group ) ) {
	throw new InvalidArgumentException( 'The template ' . __FILE__ . ' must have $group as string passed in.' );
}

$product_catalog = isset( $product_catalog ) ? $product_catalog : null;

/**
 * The fields must be hidden when:
 * - the page is loaded and the user chosen to select an existing product catalog and product feed
 */
$show = Configuration::VALUE_FB_ACTION_NEW === $group || ! empty( $product_feed_id );

// Default values.
$interval             = $page->get_feed_field_value( $product_catalog, Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL );
$interval_count       = $page->get_feed_field_value( $product_catalog, Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL_COUNT );
$interval_day_of_week = $page->get_feed_field_value( $product_catalog, Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_DAY_OF_WEEK );
$schedule_hour        = $page->get_feed_field_value( $product_catalog, Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_HOUR );
$schedule_minute      = $page->get_feed_field_value( $product_catalog, Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_MINUTE );

if ( ! empty( $product_feed_id ) && 'new' !== $product_feed_id && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	try {
		// Retrieve data from FB API.
		$product_feed = AEPC_Admin::$api->get_product_feed( $product_feed_id );

		if ( $product_feed ) {
			$interval             = $product_feed->schedule->interval;
			$interval_count       = $product_feed->schedule->interval_count;
			$interval_day_of_week = ! empty( $product_feed->schedule->day_of_week ) ? $product_feed->schedule->day_of_week : '';
			$schedule_hour        = $product_feed->schedule->hour;
			$schedule_minute      = $product_feed->schedule->minute;
		}
	} catch ( Exception $e ) {
		$fberror = $e->getMessage();
	}
}
?>

<div class="js-schedule-options <?php echo esc_attr( $group ); ?><?php echo ! $show ? ' hide' : ''; ?>">

	<?php
	if ( ! empty( $fberror ) ) {
		$page->print_notice(
			'warning',
			/* translators: %s: the facebook api error */
			nl2br( sprintf( esc_html__( "We couldn't retrieve the schedule options from the selected feed: %s.\nThe default ones are loaded.", 'pixel-caffeine' ), $fberror ) )
		);
	}
	?>

	<h2 class="sub-tit"><?php esc_html_e( 'Schedule Your Uploads', 'pixel-caffeine' ); ?></h2>

	<div class="form-group form-radio">
		<div class="control-wrap">

			<div class="radio">
				<label>
					<input
						type="radio"
						name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ); ?>"
						id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ); ?>"
						value="<?php echo esc_attr( \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_DAILY ); ?>"
						data-toggle="schedule-interval"
						<?php checked( \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_DAILY, $interval ); ?>
					><?php esc_html_e( 'Daily', 'pixel-caffeine' ); ?>
				</label>
			</div>

			<div class="radio">
				<label>
					<input
						type="radio"
						name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ); ?>"
						id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ); ?>"
						value="<?php echo esc_attr( \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_HOURLY ); ?>"
						data-toggle="schedule-interval"
						data-dep="hourly"
						<?php checked( \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_HOURLY, $interval ); ?>
					><?php esc_html_e( 'Hourly', 'pixel-caffeine' ); ?>
				</label>
			</div>

			<div class="radio">
				<label>
					<input
						type="radio"
						name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ); ?>"
						id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL ); ?>"
						value="<?php echo esc_attr( \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_WEEKLY ); ?>"
						data-toggle="schedule-interval"
						data-dep="weekly"
						<?php checked( \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_WEEKLY, $interval ); ?>
					><?php esc_html_e( 'Weekly', 'pixel-caffeine' ); ?>
				</label>
			</div>

		</div>
	</div>

	<div class="form-group multiple-fields-inline">

		<div class="control-wrap <?php echo \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_HOURLY === $interval ? '' : 'hide'; ?>" data-schedule-option="hourly">
			<label for="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL_COUNT ); ?>" class="control-label">
				<?php esc_html_e( 'Repeat', 'pixel-caffeine' ); ?>
			</label>
			<select
				class="form-control"
				id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL_COUNT ); ?>"
				name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_INTERVAL_COUNT ); ?>"
			>
				<?php foreach ( array( 1, 2, 3, 4, 6, 8, 12 ) as $count ) : ?>
					<option value="<?php echo esc_attr( (string) $count ); ?>"<?php selected( $count, $interval_count ); ?>>
						<?php
						printf(
							/* translators: %s: the number of hours */
							esc_html( _n( 'Every %s hour', 'Every %s hours', $count, 'pixel-caffeine' ) ),
							$count > 1 ? esc_html( (string) $count ) : ''
						);
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="control-wrap <?php echo \AEPC_Facebook_Adapter::FEED_SCHEDULE_INTERVAL_WEEKLY === $interval ? '' : 'hide'; ?>" data-schedule-option="weekly">
			<label for="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_DAY_OF_WEEK ); ?>" class="control-label">
				<?php esc_html_e( 'Repeat', 'pixel-caffeine' ); ?>
			</label>
			<select
				class="form-control"
				id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_DAY_OF_WEEK ); ?>"
				name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_DAY_OF_WEEK ); ?>"
			>
				<?php foreach ( $page->get_feed_weekly_options() as $k => $display ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $interval_day_of_week, $k ); ?>>
						<?php echo esc_html( $display ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="control-wrap">
			<label
				for="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_HOUR ); ?>"
				class="control-label"
			><?php esc_html_e( 'Time', 'pixel-caffeine' ); ?></label>
			<select
				class="form-control"
				id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_HOUR ); ?>"
				name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_HOUR ); ?>"
			>
				<?php for ( $hh = 0; $hh < 24; $hh++ ) : ?>
					<option value="<?php echo esc_attr( (string) $hh ); ?>"<?php selected( $hh, $schedule_hour ); ?>>
						<?php echo esc_html( str_pad( (string) $hh, 2, '0', STR_PAD_LEFT ) ); ?>
					</option>
				<?php endfor; ?>
			</select>
			<select
				class="form-control"
				id="<?php $page->feed_field_id( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_MINUTE ); ?>"
				name="<?php $page->feed_field_name( Configuration::OPTION_FEED_CONFIG, $group, Configuration::OPTION_FB_PRODUCT_FEED_SCHEDULE_MINUTE ); ?>"
			>
				<?php for ( $mm = 0; $mm < 60; $mm++ ) : ?>
					<option value="<?php echo esc_attr( (string) $mm ); ?>"<?php selected( $mm, $schedule_minute ); ?>>
						<?php echo esc_html( str_pad( (string) $mm, 2, '0', STR_PAD_LEFT ) ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</div>

	</div>

</div>
