<?php
/**
 * Child theme functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Text Domain: oceanwp
 * @link http://codex.wordpress.org/Plugin_API
 *
 */

/**
 * Load the parent style.css file
 *
 * @link http://codex.wordpress.org/Child_Themes
 */
function oceanwp_child_enqueue_parent_style() {
	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
	$theme   = wp_get_theme( 'OceanWP' );
	$version = $theme->get( 'Version' );
	// Load the stylesheet
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'oceanwp-style' ), $version );
	
}
add_action( 'wp_enqueue_scripts', 'oceanwp_child_enqueue_parent_style' );



function wpm_traduction($texte) { 
	$texte = str_ireplace('Gracias. Tu pedido ha sido recibido.', '¡Tu pedido ha sido recibido! No olvides verificar tu mensageria para consultar el recapitulativo completo de tu pedido.', $texte); 
	$texte = str_ireplace('Descartar', 'Cerrar', $texte); 
	$texte = str_ireplace('Aquí es donde puedes añadir nuevos productos en tu tienda.', 'Aquí es donde puedes añadir nuevos productos a tu carrito de compras.', $texte);	
	return $texte; 
} 

add_filter('gettext', 'wpm_traduction'); 
add_filter('ngettext', 'wpm_traduction');



/* Afficher le pourcentage de réduction des promotions WooCommerce */

add_action( 'woocommerce_before_shop_loop_item_title', 'wpm_show_sale_percentage_loop', 25 );

function wpm_show_sale_percentage_loop() {
global $product;

// Si le produit est en promotion
if ( $product->is_on_sale() ) {

// Si le produit n'est pas un produit variable
if ( ! $product->is_type( 'variable' ) ) {
$max_percentage = round( ( ( $product->regular_price - $product->sale_price ) / $product->regular_price ) * 100 );
} else {

// Sinon si c'est un produit variable, on récupère les différents prix
foreach ( $product->get_children() as $child_id ) :
$variation = $product->get_child( $child_id );
$price = $variation->get_regular_price();
$sale = $variation->get_sale_price();

// On calcule le pourcentage
$percentage = $price != 0 && ! empty( $sale ) ? ( ( $price - $sale ) / $price * 100 ) : $max_percentage;
if ( $percentage >= $highest_percentage ) :
    $max_percentage = $percentage;
    $regular_price = $product->get_variation_regular_price( 'min' );
    $sale_price = $product->get_variation_sale_price( 'min' );
endif;
endforeach;
}

// On affiche le pourcentage en l'arrondissant
echo "<div class='pourcentage'>-" . round($max_percentage) . "%</div>";
}
}


/* Exclure des Départements colombiens */

add_filter('woocommerce_states', 'eliminar_departamentos');

function eliminar_departamentos( $departamentos ) {

unset($departamentos['CO']['AMZ']);

unset($departamentos['CO']['SAP']);

return $departamentos;

}

/*  Pour Black Friday 

add_filter( 'woocommerce_sale_flash', function( $texto ) {
	return str_replace( __( 'Sale!', 'woocommerce' ), 'Black Friday', $texto );
}, 10, 1 );

*/

