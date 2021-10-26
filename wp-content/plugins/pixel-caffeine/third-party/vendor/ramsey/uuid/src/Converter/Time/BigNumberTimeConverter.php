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
namespace PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\Time;

use PixelCaffeine\Dependencies\Moontoast\Math\BigNumber;
use PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\TimeConverterInterface;
/**
 * BigNumberTimeConverter uses the moontoast/math library's `BigNumber` to
 * provide facilities for converting parts of time into representations that may
 * be used in UUIDs
 */
class BigNumberTimeConverter implements \PixelCaffeine\Dependencies\Ramsey\Uuid\Converter\TimeConverterInterface
{
    /**
     * Uses the provided seconds and micro-seconds to calculate the time_low,
     * time_mid, and time_high fields used by RFC 4122 version 1 UUIDs
     *
     * @param string $seconds
     * @param string $microSeconds
     * @return string[] An array containing `low`, `mid`, and `high` keys
     * @link http://tools.ietf.org/html/rfc4122#section-4.2.2
     */
    public function calculateTime($seconds, $microSeconds)
    {
        $uuidTime = new \PixelCaffeine\Dependencies\Moontoast\Math\BigNumber('0');
        $sec = new \PixelCaffeine\Dependencies\Moontoast\Math\BigNumber($seconds);
        $sec->multiply('10000000');
        $usec = new \PixelCaffeine\Dependencies\Moontoast\Math\BigNumber($microSeconds);
        $usec->multiply('10');
        $uuidTime->add($sec)->add($usec)->add('122192928000000000');
        $uuidTimeHex = \sprintf('%016s', $uuidTime->convertToBase(16));
        return ['low' => \substr($uuidTimeHex, 8), 'mid' => \substr($uuidTimeHex, 4, 4), 'hi' => \substr($uuidTimeHex, 0, 4)];
    }
}
