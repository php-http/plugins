<?php

namespace Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;

/**
 * Transform a HttpClient into a HttpAsyncClient
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class FakeAsyncClient implements HttpAsyncClient
{
    /** @var \Http\Client\HttpClient  */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        try {
            return new FulfilledPromise($this->httpClient->sendRequest($request));
        } catch (Exception $e) {
            return new RejectedPromise($e);
        }
    }
}
 