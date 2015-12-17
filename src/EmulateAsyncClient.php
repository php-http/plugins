<?php

namespace Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Tools\HttpAsyncClientEmulator;
use Http\Client\Tools\HttpClientDecorator;

/**
 * Emulate an async client.
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
