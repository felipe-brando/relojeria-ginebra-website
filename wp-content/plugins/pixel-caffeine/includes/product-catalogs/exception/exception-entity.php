<?php
/**
 * Exception for product catalog entity
 *
 * @package Pixel Caffeine
 */

namespace PixelCaffeine\ProductCatalog\Exception;

use PixelCaffeine\Admin\Exception\AEPCException;
use PixelCaffeine\ProductCatalog\Entity\ProductCatalog as Entity;

/**
 * Class for Feed specific exceptions
 *
 * @package PixelCaffeine\ProductCatalog\Exception
 */
class EntityException extends AEPCException {

	/**
	 * Create exception instance for the product catalog not existing.
	 *
	 * @param string $id The product catalog ID.
	 *
	 * @return EntityException
	 */
	public static function does_not_exist( $id ) {
		/* translators: %s: the name of the product catalog */
		return new self( sprintf( __( 'The product catalog "%s" does not exist.', 'pixel-caffeine' ), $id ), 1 );
	}

	/**
	 * Create exception for the already existing error.
	 *
	 * @param Entity $entity The product catalog entity.
	 *
	 * @return EntityException
	 */
	public static function is_already_existing( Entity $entity ) {
		/* translators: %s: the name of the product catalog */
		return new self( sprintf( __( 'The product catalog "%s" already exists.', 'pixel-caffeine' ), $entity->get_id() ), 2 );
	}

	/**
	 * Create the exception for no entity defined yet.
	 *
	 * @return EntityException
	 */
	public static function no_entity_defined() {
		return new self( __( 'No entity defined yet', 'pixel-caffeine' ), 3 );
	}

	/**
	 * Create the exception when the entity has not a name.
	 *
	 * @return EntityException
	 */
	public static function name_is_empty() {
		return new self( __( 'Please, give a name to the product catalog', 'pixel-caffeine' ), 4 );
	}

}
