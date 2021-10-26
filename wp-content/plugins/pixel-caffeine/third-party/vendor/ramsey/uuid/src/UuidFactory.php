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
namespace PixelCaffeine\Dependencies\Ramsey\Uuid;

use PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\NumberConverterInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Exception\InvalidUuidStringException;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Provider\NodeProviderInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Generator\RandomGeneratorInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Generator\TimeGeneratorInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Codec\CodecInterface;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Builder\UuidBuilderInterface;
class UuidFactory implements \PixelCaffeine\Dependencies\Ramsey\Uuid\UuidFactoryInterface
{
    /**
     * @var CodecInterface
     */
    private $codec = null;
    /**
     * @var NodeProviderInterface
     */
    private $nodeProvider = null;
    /**
     * @var NumberConverterInterface
     */
    private $numberConverter = null;
    /**
     * @var RandomGeneratorInterface
     */
    private $randomGenerator = null;
    /**
     * @var TimeGeneratorInterface
     */
    private $timeGenerator = null;
    /**
     * @var UuidBuilderInterface
     */
    private $uuidBuilder = null;
    /**
     * Constructs a `UuidFactory` for creating `Ramsey\Uuid\UuidInterface` instances
     *
     * @param FeatureSet $features A set of features for use when creating UUIDs
     */
    public function __construct(\PixelCaffeine\Dependencies\Ramsey\Uuid\FeatureSet $features = null)
    {
        $features = $features ?: new \PixelCaffeine\Dependencies\Ramsey\Uuid\FeatureSet();
        $this->codec = $features->getCodec();
        $this->nodeProvider = $features->getNodeProvider();
        $this->numberConverter = $features->getNumberConverter();
        $this->randomGenerator = $features->getRandomGenerator();
        $this->timeGenerator = $features->getTimeGenerator();
        $this->uuidBuilder = $features->getBuilder();
    }
    /**
     * Returns the UUID coder-decoder used by this factory
     *
     * @return CodecInterface
     */
    public function getCodec()
    {
        return $this->codec;
    }
    /**
     * Sets the UUID coder-decoder used by this factory
     *
     * @param CodecInterface $codec
     */
    public function setCodec(\PixelCaffeine\Dependencies\Ramsey\Uuid\Codec\CodecInterface $codec)
    {
        $this->codec = $codec;
    }
    /**
     * Returns the system node ID provider used by this factory
     *
     * @return NodeProviderInterface
     */
    public function getNodeProvider()
    {
        return $this->nodeProvider;
    }
    /**
     * Returns the random UUID generator used by this factory
     *
     * @return RandomGeneratorInterface
     */
    public function getRandomGenerator()
    {
        return $this->randomGenerator;
    }
    /**
     * Returns the time-based UUID generator used by this factory
     *
     * @return TimeGeneratorInterface
     */
    public function getTimeGenerator()
    {
        return $this->timeGenerator;
    }
    /**
     * Sets the time-based UUID generator this factory will use to generate version 1 UUIDs
     *
     * @param TimeGeneratorInterface $generator
     */
    public function setTimeGenerator(\PixelCaffeine\Dependencies\Ramsey\Uuid\Generator\TimeGeneratorInterface $generator)
    {
        $this->timeGenerator = $generator;
    }
    /**
     * Returns the number converter used by this factory
     *
     * @return NumberConverterInterface
     */
    public function getNumberConverter()
    {
        return $this->numberConverter;
    }
    /**
     * Sets the random UUID generator this factory will use to generate version 4 UUIDs
     *
     * @param RandomGeneratorInterface $generator
     */
    public function setRandomGenerator(\PixelCaffeine\Dependencies\Ramsey\Uuid\Generator\RandomGeneratorInterface $generator)
    {
        $this->randomGenerator = $generator;
    }
    /**
     * Sets the number converter this factory will use
     *
     * @param NumberConverterInterface $converter
     */
    public function setNumberConverter(\PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\NumberConverterInterface $converter)
    {
        $this->numberConverter = $converter;
    }
    /**
     * Returns the UUID builder this factory uses when creating `Uuid` instances
     *
     * @return UuidBuilderInterface $builder
     */
    public function getUuidBuilder()
    {
        return $this->uuidBuilder;
    }
    /**
     * Sets the UUID builder this factory will use when creating `Uuid` instances
     *
     * @param UuidBuilderInterface $builder
     */
    public function setUuidBuilder(\PixelCaffeine\Dependencies\Ramsey\Uuid\Builder\UuidBuilderInterface $builder)
    {
        $this->uuidBuilder = $builder;
    }
    /**
     * @inheritdoc
     */
    public function fromBytes($bytes)
    {
        return $this->codec->decodeBytes($bytes);
    }
    /**
     * @inheritdoc
     */
    public function fromString($uuid)
    {
        $uuid = \strtolower($uuid);
        return $this->codec->decode($uuid);
    }
    /**
     * @inheritdoc
     */
    public function fromInteger($integer)
    {
        $hex = $this->numberConverter->toHex($integer);
        $hex = \str_pad($hex, 32, '0', \STR_PAD_LEFT);
        return $this->fromString($hex);
    }
    /**
     * @inheritdoc
     */
    public function uuid1($node = null, $clockSeq = null)
    {
        $bytes = $this->timeGenerator->generate($node, $clockSeq);
        $hex = \bin2hex($bytes);
        return $this->uuidFromHashedName($hex, 1);
    }
    /**
     * @inheritdoc
     */
    public function uuid3($ns, $name)
    {
        return $this->uuidFromNsAndName($ns, $name, 3, 'md5');
    }
    /**
     * @inheritdoc
     */
    public function uuid4()
    {
        $bytes = $this->randomGenerator->generate(16);
        // When converting the bytes to hex, it turns into a 32-character
        // hexadecimal string that looks a lot like an MD5 hash, so at this
        // point, we can just pass it to uuidFromHashedName.
        $hex = \bin2hex($bytes);
        return $this->uuidFromHashedName($hex, 4);
    }
    /**
     * @inheritdoc
     */
    public function uuid5($ns, $name)
    {
        return $this->uuidFromNsAndName($ns, $name, 5, 'sha1');
    }
    /**
     * Returns a `Uuid`
     *
     * Uses the configured builder and codec and the provided array of hexadecimal
     * value UUID fields to construct a `Uuid` object.
     *
     * @param array $fields An array of fields from which to construct a UUID;
     *     see {@see \Ramsey\Uuid\UuidInterface::getFieldsHex()} for array structure.
     * @return UuidInterface
     */
    public function uuid(array $fields)
    {
        return $this->uuidBuilder->build($this->codec, $fields);
    }
    /**
     * Returns a version 3 or 5 namespaced `Uuid`
     *
     * @param string|UuidInterface $ns The UUID namespace to use
     * @param string $name The string to hash together with the namespace
     * @param int $version The version of UUID to create (3 or 5)
     * @param string $hashFunction The hash function to use when hashing together
     *     the namespace and name
     * @return UuidInterface
     * @throws InvalidUuidStringException
     */
    protected function uuidFromNsAndName($ns, $name, $version, $hashFunction)
    {
        if (!$ns instanceof \PixelCaffeine\Dependencies\Ramsey\Uuid\UuidInterface) {
            $ns = $this->codec->decode($ns);
        }
        $hash = \call_user_func($hashFunction, $ns->getBytes() . $name);
        return $this->uuidFromHashedName($hash, $version);
    }
    /**
     * Returns a `Uuid` created from `$hash` with the version field set to `$version`
     * and the variant field set for RFC 4122
     *
     * @param string $hash The hash to use when creating the UUID
     * @param int $version The UUID version to set for this hash (1, 3, 4, or 5)
     * @return UuidInterface
     */
    protected function uuidFromHashedName($hash, $version)
    {
        $timeHi = \PixelCaffeine\Dependencies\Ramsey\Uuid\BinaryUtils::applyVersion(\substr($hash, 12, 4), $version);
        $clockSeqHi = \PixelCaffeine\Dependencies\Ramsey\Uuid\BinaryUtils::applyVariant(\hexdec(\substr($hash, 16, 2)));
        $fields = ['time_low' => \substr($hash, 0, 8), 'time_mid' => \substr($hash, 8, 4), 'time_hi_and_version' => \str_pad(\dechex($timeHi), 4, '0', \STR_PAD_LEFT), 'clock_seq_hi_and_reserved' => \str_pad(\dechex($clockSeqHi), 2, '0', \STR_PAD_LEFT), 'clock_seq_low' => \substr($hash, 18, 2), 'node' => \substr($hash, 20, 12)];
        return $this->uuid($fields);
    }
}
