<?php

namespace PixelCaffeine\Dependencies\GuzzleHttp\Exception;

use PixelCaffeine\Dependencies\Psr\Http\Message\RequestInterface;
use PixelCaffeine\Dependencies\Psr\Http\Message\ResponseInterface;
/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 */
class BadResponseException extends \PixelCaffeine\Dependencies\GuzzleHttp\Exception\RequestException
{
    public function __construct($message, \PixelCaffeine\Dependencies\Psr\Http\Message\RequestInterface $request, \PixelCaffeine\Dependencies\Psr\Http\Message\ResponseInterface $response = null, \Exception $previous = null, array $handlerContext = [])
    {
        if (null === $response) {
            @\trigger_error('Instantiating the ' . __CLASS__ . ' class without a Response is deprecated since version 6.3 and will be removed in 7.0.', \E_USER_DEPRECATED);
        }
        parent::__construct($message, $request, $response, $previous, $handlerContext);
    }
}
