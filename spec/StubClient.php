<?php

namespace spec\Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class StubClient implements HttpAsyncClient, HttpClient
{
    public function sendAsyncRequest(RequestInterface $request)
    {
    }

    public function sendRequest(RequestInterface $request)
    {
    }
}
