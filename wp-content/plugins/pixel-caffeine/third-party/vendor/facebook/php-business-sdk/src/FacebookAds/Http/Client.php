<?php

/**
 * Copyright (c) 2014-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace PixelCaffeine\Dependencies\FacebookAds\Http;

use PixelCaffeine\Dependencies\FacebookAds\ApiConfig;
use PixelCaffeine\Dependencies\FacebookAds\Http\Adapter\AdapterInterface;
use PixelCaffeine\Dependencies\FacebookAds\Http\Adapter\CurlAdapter;
use PixelCaffeine\Dependencies\FacebookAds\Http\Exception\EmptyResponseException;
use PixelCaffeine\Dependencies\FacebookAds\Http\Exception\RequestException;
class Client
{
    /**
     * @var string
     */
    const DEFAULT_GRAPH_BASE_DOMAIN = 'facebook.com';
    /**
     * @var string
     */
    const DEFAULT_LAST_LEVEL_DOMAIN = 'graph';
    /**
     * @var RequestInterface
     */
    protected $requestPrototype;
    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;
    /**
     * @var Headers
     */
    protected $defaultRequestHeaders;
    /**
     * @var AdapterInterface
     */
    protected $adapter;
    /**
     * @var string
     */
    protected $caBundlePath;
    /**
     * @var string
     */
    protected $defaultGraphBaseDomain = self::DEFAULT_GRAPH_BASE_DOMAIN;
    /**
     * @return RequestInterface
     */
    public function getRequestPrototype()
    {
        if ($this->requestPrototype === null) {
            $this->requestPrototype = new \PixelCaffeine\Dependencies\FacebookAds\Http\Request($this);
        }
        return $this->requestPrototype;
    }
    /**
     * @param RequestInterface $prototype
     */
    public function setRequestPrototype(\PixelCaffeine\Dependencies\FacebookAds\Http\RequestInterface $prototype)
    {
        $this->requestPrototype = $prototype;
    }
    /**
     * @return RequestInterface
     */
    public function createRequest()
    {
        return $this->getRequestPrototype()->createClone();
    }
    /**
     * @return ResponseInterface
     */
    public function getResponsePrototype()
    {
        if ($this->responsePrototype === null) {
            $this->responsePrototype = new \PixelCaffeine\Dependencies\FacebookAds\Http\Response();
        }
        return $this->responsePrototype;
    }
    /**
     * @param ResponseInterface $prototype
     */
    public function setResponsePrototype(\PixelCaffeine\Dependencies\FacebookAds\Http\ResponseInterface $prototype)
    {
        $this->responsePrototype = $prototype;
    }
    /**
     * @return ResponseInterface
     */
    public function createResponse()
    {
        return clone $this->getResponsePrototype();
    }
    /**
     * @return Headers
     */
    public function getDefaultRequestHeaderds()
    {
        if ($this->defaultRequestHeaders === null) {
            $this->defaultRequestHeaders = new \PixelCaffeine\Dependencies\FacebookAds\Http\Headers(array('User-Agent' => 'fbbizsdk-php-v' . \PixelCaffeine\Dependencies\FacebookAds\ApiConfig::SDKVersion, 'Accept-Encoding' => '*'));
        }
        return $this->defaultRequestHeaders;
    }
    /**
     * @param Headers $headers
     */
    public function setDefaultRequestHeaders(\PixelCaffeine\Dependencies\FacebookAds\Http\Headers $headers)
    {
        $this->defaultRequestHeaders = $headers;
    }
    /**
     * @return string
     */
    public function getDefaultGraphBaseDomain()
    {
        return $this->defaultGraphBaseDomain;
    }
    /**
     * @param string $domain
     */
    public function setDefaultGraphBaseDomain($domain)
    {
        $this->defaultGraphBaseDomain = $domain;
    }
    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        if ($this->adapter === null) {
            $this->adapter = new \PixelCaffeine\Dependencies\FacebookAds\Http\Adapter\CurlAdapter($this);
        }
        return $this->adapter;
    }
    /**
     * @param AdapterInterface $adapter
     */
    public function setAdapter(\PixelCaffeine\Dependencies\FacebookAds\Http\Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }
    /**
     * @return string
     */
    public function getCaBundlePath()
    {
        if ($this->caBundlePath === null) {
            $this->caBundlePath = __DIR__ . \DIRECTORY_SEPARATOR . \str_repeat('..' . \DIRECTORY_SEPARATOR, 3) . 'fb_ca_chain_bundle.crt';
        }
        return $this->caBundlePath;
    }
    /**
     * @param string $path
     */
    public function setCaBundlePath($path)
    {
        $this->caBundlePath = $path;
    }
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws RequestException
     */
    public function sendRequest(\PixelCaffeine\Dependencies\FacebookAds\Http\RequestInterface $request)
    {
        $response = $this->getAdapter()->sendRequest($request);
        $response->setRequest($request);
        $response_content = $response->getContent();
        if ($response_content === null) {
            throw new \PixelCaffeine\Dependencies\FacebookAds\Http\Exception\EmptyResponseException($response);
        }
        if (\is_array($response_content) && \array_key_exists('error', $response_content)) {
            throw \PixelCaffeine\Dependencies\FacebookAds\Http\Exception\RequestException::create($response);
        }
        return $response;
    }
}
