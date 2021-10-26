<?php

namespace PixelCaffeine\Dependencies\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \PixelCaffeine\Dependencies\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
