<?php

namespace Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Common\HttpAsyncClientEmulator;
use Http\Client\Common\HttpClientDecorator;

/**
 * Emulate an async client.
 *
 * This should be replaced by an anonymous class in PHP 7.
 */
class EmulateAsyncClient implements HttpClient, HttpAsyncClient
{
    use HttpClientDecorator;
    use HttpAsyncClientEmulator;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
