<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\CachePlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\CachePlugin instead.', E_USER_DEPRECATED);

use Http\Message\StreamFactory;
use Http\Promise\FulfilledPromise;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Allow for caching a response with a PSR-6 compatible caching engine.
 *
 * It can follow the RFC-7234 caching specification or use a fixed cache lifetime.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\CachePlugin} instead.
 */
class CachePlugin implements Plugin
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    /**
     * @var StreamFactory
     */
    private $streamFactory;

    /**
     * @var array
     */
    private $config;

    /**
     * @param CacheItemPoolInterface $pool
     * @param StreamFactory          $streamFactory
     * @param array                  $config        {
     *
     *     @var bool $respect_cache_headers Whether to look at the cache directives or ignore them
     *     @var int $default_ttl If we do not respect cache headers or the headers specify cache control, use this value
     * }
     */
    public function __construct(CacheItemPoolInterface $pool, StreamFactory $streamFactory, array $config = [])
    {
        $this->pool = $pool;
        $this->streamFactory = $streamFactory;

        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $this->config = $optionsResolver->resolve($config);
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $method = strtoupper($request->getMethod());

        // if the request is not cacheable, move to $next
        if ('GET' !== $method && 'HEAD' !== $method) {
            return $next($request);
        }

        $key = $this->createCacheKey($request);
        $cacheItem = $this->pool->getItem($key);

        if ($cacheItem->isHit()) {
            $data = $cacheItem->get();
            /** @var ResponseInterface $response */
            $response = $data['response'];
            $response = $response->withBody($this->streamFactory->createStream($data['body']));

            return new FulfilledPromise($response);
        }

        return $next($request)->then(function (ResponseInterface $response) use ($cacheItem) {
            if ($this->isCacheable($response)) {
                $bodyStream = $response->getBody();
                $body = (string) $bodyStream;
                if ($bodyStream->isSeekable()) {
                    $bodyStream->rewind();
                } else {
                    $response = $response->withBody($this->streamFactory->createStream($body));
                }

                $cacheItem->set(['response' => $response, 'body' => $body])
                    ->expiresAfter($this->getMaxAge($response));
                $this->pool->save($cacheItem);
            }

            return $response;
        });
    }

    /**
     * Verify that we can cache this response.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    protected function isCacheable(ResponseInterface $response)
    {
        if (!in_array($response->getStatusCode(), [200, 203, 300, 301, 302, 404, 410])) {
            return false;
        }
        if (!$this->config['respect_cache_headers']) {
            return true;
        }
        if ($this->getCacheControlDirective($response, 'no-store') || $this->getCacheControlDirective($response, 'private')) {
            return false;
        }

        return true;
    }

    /**
     * Get the value of a parameter in the cache control header.
     *
     * @param ResponseInterface $response
     * @param string            $name     The field of Cache-Control to fetch
     *
     * @return bool|string the value of the directive, true if directive without value, false if directive not present
     */
    private function getCacheControlDirective(ResponseInterface $response, $name)
    {
        $headers = $response->getHeader('Cache-Control');
        foreach ($headers as $header) {
            if (preg_match(sprintf('|%s=?([0-9]+)?|i', $name), $header, $matches)) {
                // return the value for $name if it exists
                if (isset($matches[1])) {
                    return $matches[1];
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    private function createCacheKey(RequestInterface $request)
    {
        return md5($request->getMethod().' '.$request->getUri());
    }

    /**
     * Get a ttl in seconds. It could return null if we do not respect cache headers and got no defaultTtl.
     *
     * @param ResponseInterface $response
     *
     * @return int|null
     */
    private function getMaxAge(ResponseInterface $response)
    {
        if (!$this->config['respect_cache_headers']) {
            return $this->config['default_ttl'];
        }

        // check for max age in the Cache-Control header
        $maxAge = $this->getCacheControlDirective($response, 'max-age');
        if (!is_bool($maxAge)) {
            $ageHeaders = $response->getHeader('Age');
            foreach ($ageHeaders as $age) {
                return $maxAge - ((int) $age);
            }

            return $maxAge;
        }

        // check for ttl in the Expires header
        $headers = $response->getHeader('Expires');
        foreach ($headers as $header) {
            return (new \DateTime($header))->getTimestamp() - (new \DateTime())->getTimestamp();
        }

        return $this->config['default_ttl'];
    }

    /**
     * Configure an options resolver.
     *
     * @param OptionsResolver $resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'default_ttl' => null,
            'respect_cache_headers' => true,
        ]);

        $resolver->setAllowedTypes('default_ttl', ['int', 'null']);
        $resolver->setAllowedTypes('respect_cache_headers', 'bool');
    }
}
