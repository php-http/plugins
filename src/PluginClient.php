<?php

namespace Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Plugin\Exception\RebootChainException;
use Http\Client\Promise;
use Http\Client\Utils\EmulateAsyncClient;
use Psr\Http\Message\RequestInterface;

/**
 * The client managing plugins and providing a decorator around HTTP Clients.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class PluginClient implements HttpClient, HttpAsyncClient
{
    /**
     * An HTTP async client
     *
     * @var HttpAsyncClient
     */
    protected $client;

    /**
     * The plugin chain
     *
     * @var Plugin[]
     */
    protected $plugins;

    /**
     * @param HttpClient|HttpAsyncClient $client
     * @param Plugin[]                   $plugins
     *
     * @throws \RuntimeException if client is not an instance of HttpClient or HttpAsyncClient
     */
    public function __construct($client, array $plugins = [])
    {
        if ($client instanceof HttpAsyncClient) {
            $this->client = $client;
        } elseif ($client instanceof HttpClient) {
            $this->client = new EmulateAsyncClient($client);
        } else {
            throw new \RuntimeException('Client must be an instance of Http\\Client\\HttpClient or Http\\Client\\HttpAsyncClient');
        }

        $this->plugins = $plugins;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
        $client = $this->client;
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
