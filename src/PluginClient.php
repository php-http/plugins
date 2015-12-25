<?php

namespace Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Plugin\Exception\LoopException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The client managing plugins and providing a decorator around HTTP Clients.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class PluginClient implements HttpClient, HttpAsyncClient
{
    /**
     * An HTTP async client.
     *
     * @var HttpAsyncClient
     */
    protected $client;

    /**
     * The plugin chain.
     *
     * @var Plugin[]
     */
    protected $plugins;

    /**
     * A list of options.
     *
     * @var array
     */
    protected $options;

    /**
     * @param HttpClient|HttpAsyncClient $client
     * @param Plugin[]                   $plugins
     * @param array                      $options
     *
     * @throws \RuntimeException if client is not an instance of HttpClient or HttpAsyncClient
     */
    public function __construct($client, array $plugins = [], array $options = [])
    {
        if ($client instanceof HttpAsyncClient) {
            $this->client = $client;
        } elseif ($client instanceof HttpClient) {
            $this->client = new EmulateAsyncClient($client);
        } else {
            throw new \RuntimeException('Client must be an instance of Http\\Client\\HttpClient or Http\\Client\\HttpAsyncClient');
        }

        $this->plugins = $plugins;
        $this->options = $this->configure($options);
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $promise = $this->sendAsyncRequest($request);

        return $promise->wait();
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
     * Configure the plugin client.
     *
     * @param array $options
     *
     * @return array
     */
    protected function configure(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'max_restarts' => 10,
        ]);

        return $resolver->resolve($options);
    }

    /**
     * @param Plugin[] $pluginList
     *
     * @return callable
     */
    private function createPluginChain($pluginList)
    {
        $client = $this->client;
        $options = $this->options;

        $lastCallable = function (RequestInterface $request) use ($client) {
            return $client->sendAsyncRequest($request);
        };

        $firstCallable = $lastCallable;
        while ($plugin = array_pop($pluginList)) {
            $lastCallable = function (RequestInterface $request) use ($plugin, $lastCallable, &$firstCallable) {
                return $plugin->handleRequest($request, $lastCallable, $firstCallable);
            };

            $firstCallable = $lastCallable;
        }

        $firstCalls = 0;
        $firstCallable = function (RequestInterface $request) use ($options, $lastCallable, &$firstCalls) {
            if ($firstCalls > $options['max_restarts']) {
                throw new LoopException('Too many restarts in plugin client', $request);
            }

            ++$firstCalls;

            return $lastCallable($request);
        };

        return $firstCallable;
    }
}
