<?php
/**
 * General admin settings page
 *
 * This is the template with the HTML code for the General Settings admin page
 *
 * @var AEPC_Admin_View $page
 * @var stdClass $widget
 *
 * @package Pixel Caffeine
 *
 * @phpcs:disable VariableAnalysis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="plugin-sidebar-item banner-wrap">
	<a href="<?php echo esc_url( $widget->link ); ?>" target="_blank">
		<img src="<?php echo esc_url( $widget->img ); ?>">
	</a>
</div>
