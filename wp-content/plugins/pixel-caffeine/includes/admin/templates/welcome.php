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

$back_to         = filter_input( INPUT_GET, 'back_to', FILTER_SANITIZE_STRING ) ?: false;
$updated_version = filter_input( INPUT_GET, 'version', FILTER_SANITIZE_STRING ) ?: false;

?>

<div class="pixel-caffeine-wrapper">
	<div class="wrap wrap-dashboard">

	<h1 class="page-title"><?php $page->the_title(); ?></h1>

	<?php $page->get_template_part( 'nav-tabs' ); ?>

	<section class="plugin-sec">
		<div class="plugin-content">

			<?php $page->get_template_part( 'welcomes/' . $updated_version, array( 'back_to' => $back_to ? esc_url( $back_to ) : false ) ); ?>

		</div><!-- ./plugin-content -->

		<?php $page->get_template_part( 'sidebar' ); ?>
	</section>

	</div><!--/.wrap -->
</div><!--/.pixel-caffeine-wrapper -->
