<?php

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://benramsey.com/projects/ramsey-uuid/ Documentation
 * @link https://packagist.org/packages/ramsey/uuid Packagist
 * @link https://github.com/ramsey/uuid GitHub
 */
namespace PixelCaffeine\Dependencies\Ramsey\Uuid\Builder;

use PixelCaffeine\Dependencies\Ramsey\Uuid\Codec\CodecInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\NumberConverterInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Uuid;
/**
 * DefaultUuidBuilder is the default UUID builder for ramsey/uuid; it builds
 * instances of Uuid objects
 */
class DefaultUuidBuilder implements \PixelCaffeine\Dependencies\Ramsey\Uuid\Builder\UuidBuilderInterface
{
    /**
     * @var NumberConverterInterface
     */
    private $converter;
    /**
     * Constructs the DefaultUuidBuilder
     *
     * @param NumberConverterInterface $converter The number converter to use when constructing the Uuid
     */
    public function __construct(\PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\NumberConverterInterface $converter)
    {
        $this->converter = $converter;
    }
    /**
     * Builds a Uuid
     *
     * @param CodecInterface $codec The codec to use for building this Uuid
     * @param array $fields An array of fields from which to construct the Uuid;
     *     see {@see \Ramsey\Uuid\UuidInterface::getFieldsHex()} for array structure.
     * @return Uuid
     */
    public function build(\PixelCaffeine\Dependencies\Ramsey\Uuid\Codec\CodecInterface $codec, array $fields)
    {
        return new \PixelCaffeine\Dependencies\Ramsey\Uuid\Uuid($fields, $this->converter, $codec);
    }
}
