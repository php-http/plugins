<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\PluginClient class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\PluginClient instead.', E_USER_DEPRECATED);

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Plugin\Exception\LoopException;
use Psr\Http\Message\RequestInterface;

/**
 * The client managing plugins and providing a decorator around HTTP Clients.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\PluginClient} instead.
 */
final class PluginClient implements HttpClient, HttpAsyncClient
{
    /**
     * @var \Http\Client\Common\PluginClient
     */
    private $pluginClient;

    /**
     * @param HttpClient|HttpAsyncClient $client
     * @param Plugin[]                   $plugins
     * @param array                      $options {
     *
     *     @var int $max_restarts
     * }
     *
     * @throws \RuntimeException if client is not an instance of HttpClient or HttpAsyncClient
     */
    public function __construct($client, array $plugins = [], array $options = [])
    {
        $this->pluginClient = new \Http\Client\Common\PluginClient($client, $plugins, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        try {
            return $this->pluginClient->sendRequest($request);
        } catch (\Http\Client\Common\Exception\LoopException $e) {
            throw new LoopException($e->getMessage(), $e->getRequest(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        try {
            return $this->pluginClient->sendAsyncRequest($request);
        } catch (\Http\Client\Common\Exception\LoopException $e) {
            throw new LoopException($e->getMessage(), $e->getRequest(), $e);
        }
    }
}
