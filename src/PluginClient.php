<?php

namespace Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Plugin\Exception\RebootChainException;
use Http\Client\Promise;
use Http\Client\Utils\EmulateAsyncClient;
use Psr\Http\Message\RequestInterface;

class PluginClient implements HttpClient, HttpAsyncClient
{
    /**
     * @var HttpAsyncClient A http async client
     */
    protected $client;

    /**
     * @var Plugin[] The plugin chain
     */
    protected $plugins;

    /**
     * @param HttpClient|HttpAsyncClient $client  A http client (async or not)
     * @param Plugin[]                   $plugins A list of plugins (middleware) to apply
     *
     * @throws \RuntimeException if client is not an instance of HttpClient or HttpAsyncClient
     */
    public function __construct($client, array $plugins = array())
    {
        if ($client instanceof HttpAsyncClient) {
            $this->client = $client;
        } elseif ($client instanceof HttpClient) {
            $this->client = new EmulateAsyncClient($client);
        } else {
            throw new \RuntimeException("Client must be an instance of Http\\Client\\HttpClient or Http\\Client\\HttpAsyncClient");
        }

        $this->plugins = $plugins;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $promise = $this->sendAsyncRequest($request);
        $promise->wait();

        if ($promise->getState() == Promise::REJECTED) {
            throw $promise->getException();
        }

        return $promise->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        $pluginChain = $this->createPluginChain($this->plugins);

        return $pluginChain($request);
    }

    /**
     * @param Plugin[] $pluginList
     *
     * @return callable
     */
    private function createPluginChain($pluginList)
    {
        $client       = $this->client;
        $lastCallable = function (RequestInterface $request) use($client) {
            return $client->sendAsyncRequest($request);
        };

        $firstCallable = $lastCallable;
        while ($plugin = array_pop($pluginList)) {
            $lastCallable = function (RequestInterface $request) use ($plugin, $lastCallable, &$firstCallable) {
                return $plugin->handleRequest($request, $lastCallable, $firstCallable);
            };

            $firstCallable = $lastCallable;
        }

        return $firstCallable;
    }
}
