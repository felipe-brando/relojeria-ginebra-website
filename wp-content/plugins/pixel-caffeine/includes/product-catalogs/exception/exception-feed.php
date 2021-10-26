<?php
/**
 * General exceptions for the feed
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog\Exception;

use PixelCaffeine\Admin\Exception\AEPCException;
use PixelCaffeine\ProductCatalog\FeedMapper;

/**
 * Class for Feed specific exceptions
 *
 * @package PixelCaffeine\ProductCatalog\Exception
 */
class FeedException extends AEPCException {

	/**
	 * Create exception for format not supported error
	 *
	 * @param string $format Feed format.
	 *
	 * @return FeedException
	 */
	public static function formatNotSupported( $format ) {
		/* translators: %s: feed format */
		return new self( sprintf( __( 'The format "%s" for the feed is not supported yet.', 'pixel-caffeine' ), $format ), 1 );
	}

	/**
	 * Create exception for the writer not initialized yet error.
	 *
	 * @param string $format Feed format.
	 *
	 * @return FeedException
	 */
	public static function writerNotInitialized( $format ) {
		/* translators: %s: feed format */
		return new self( sprintf( __( 'The format "%s" is not initialized for object writing.', 'pixel-caffeine' ), $format ), 2 );
	}

	/**
	 * Create exception when the weight it not supported.
	 *
	 * @param string                   $unit The weight unit.
	 * @param \AEPC_Addon_Product_Item $item The product feed item from Add-on.
	 *
	 * @return FeedException
	 */
	public static function weightUnitNotSupported( $unit, \AEPC_Addon_Product_Item $item ) {
		/* translators: 1: opening tag for the link to the product admin page, 2: the product ID, 3: closing previous tag, 4: the weight unit */
		return new self( sprintf( __( '%1$sProduct #%2$s%3$s error: the weight unit "%4$s" in product feed is not supported by Facebook.', 'pixel-caffeine' ), '<a href="' . $item->get_admin_url() . '">', $item->get_id(), '</a>', $unit ), 3 );
	}

	/**
	 * Create exception when the field is mandatory
	 *
	 * @param string                   $field The field name.
	 * @param \AEPC_Addon_Product_Item $item The product feed item from Add-on.
	 *
	 * @return FeedException
	 */
	public static function mandatoryField( $field, \AEPC_Addon_Product_Item $item ) {
		if ( $item->is_variation() ) {
			return new self(
				sprintf(
				/* translators: 1: the variation ID, 2: opening tag for the link to the parent product admin page, 3: the parent product ID, 4: closing previous tag, 5: the field name */
					__(
						'Variation #%1$s of %2$sproduct #%3$s%4$s error: the field "%5$s" in must not be empty.',
						'pixel-caffeine'
					),
					$item->get_id(),
					'<a href="' . $item->get_parent_admin_url() . '">',
					$item->get_group_id(),
					'</a>',
					$field
				),
				4
			);
		} else {
			return new self(
				sprintf(
				/* translators: 1: opening tag for the link to the product admin page, 2: the product ID, 3: closing previous tag, 4: the field name */
					__( '%1$sProduct #%2$s%3$s error: the field "%4$s" in must not be empty.', 'pixel-caffeine' ),
					'<a href="' . $item->get_admin_url() . '">',
					$item->get_id(),
					'</a>',
					$field
				),
				4
			);
		}
	}

	/**
	 * Create exception for google category mandatory error.
	 *
	 * @param \AEPC_Addon_Product_Item $item The product feed item from Add-on.
	 *
	 * @return FeedException
	 */
	public static function googleCategoryMandatory( \AEPC_Addon_Product_Item $item ) {
		/* translators: 1: opening tag for the link to the product admin page, 2: the product ID, 3: closing previous tag */
		return new self( sprintf( __( '%1$sProduct #%2$s%3$s error: a google product category must be defined in the product or at least in the product catalog configuration.', 'pixel-caffeine' ), '<a href="' . $item->get_admin_url() . '">', $item->get_id(), '</a>' ), 5 );
	}

	/**
	 * Create exception for item not existing error.
	 *
	 * @param FeedMapper $item The feed mapper instance.
	 *
	 * @return FeedException
	 */
	public static function itemDoesNotExist( FeedMapper $item ) {
		/* translators: 1: opening tag for the link to the product admin page, 2: the product title, 3: the product ID, 4: closing previous tag */
		return new self( sprintf( __( 'EDIT ERROR: The item %1$s"%2$s (#%3$s)"%4$s does not exist inside the product feed', 'pixel-caffeine' ), '<a href="' . $item->get_permalink() . '">', $item->get_title(), $item->get_id(), '</a>' ), 6 );
	}

	/**
	 * Create exception for the no backup version of the feed existing error
	 *
	 * @return FeedException
	 */
	public static function noBackupVersionOfFeed() {
		return new self( __( 'SAVING ERROR: There is no backup version of the feed to restore', 'pixel-caffeine' ), 7 );
	}

	/**
	 * Create the exception for the feed file not existing error
	 *
	 * @return FeedException
	 */
	public static function feedDoesNotExist() {
		return new self( __( 'There is not feed file to backup', 'pixel-caffeine' ), 8 );
	}

	/**
	 * Create exception when the feed is unable to be saved because of the error in parameter
	 *
	 * @param \WP_Error $wp_error The error instance.
	 *
	 * @return FeedException
	 */
	public static function feedCannotBeSaved( \WP_Error $wp_error ) {
		/* translators: %s: the error message */
		return new self( sprintf( __( 'The saving process cannot be started: %s', 'pixel-caffeine' ), $wp_error->get_error_message() ), 9 );
	}

}
