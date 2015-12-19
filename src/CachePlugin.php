<?php

namespace Http\Client\Plugin;

use Http\Client\Tools\Promise\FulfilledPromise;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Allow for caching a response.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CachePlugin implements Plugin
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    /**
     * Default time to store object in cache. This value is used if CachePlugin::respectCacheHeaders is false or
     * if cache headers are missing.
     *
     * @var int
     */
    private $defaultTtl;

    /**
     * Look at the cache headers to know whether this response may be cached and to 
     * decide how it can be cached.
     *
     * @var bool Defaults to true
     */
    private $respectCacheHeaders;

    /**
     * Available options are
     *  - respect_cache_headers: Whether to look at the cache directives or ignore them.
     * 
     * @param CacheItemPoolInterface $pool
     * @param array                  $options
     */
    public function __construct(CacheItemPoolInterface $pool, array $options = [])
    {
        $this->pool = $pool;
        $this->defaultTtl = isset($options['default_ttl']) ? $options['default_ttl'] : null;
        $this->respectCacheHeaders = isset($options['respect_cache_headers']) ? $options['respect_cache_headers'] : true;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $method = strtoupper($request->getMethod());

        // if the request not is cachable, move to $next
        if ($method !== 'GET' && $method !== 'HEAD') {
            return $next($request);
        }

        // If we can cache the request
        $key = $this->createCacheKey($request);
        $cacheItem = $this->pool->getItem($key);

        if ($cacheItem->isHit()) {
            // return cached response
            return new FulfilledPromise($cacheItem->get());
        }

        return $next($request)->then(function (ResponseInterface $response) use ($cacheItem) {
            if ($this->isCacheable($response)) {
                $cacheItem->set($response)
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
        if (!$this->respectCacheHeaders) {
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
     * @return bool|string The value of the directive, true if directive without value, false if directive not present.
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
        if (!$this->respectCacheHeaders) {
            return $this->defaultTtl;
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

        return $this->defaultTtl;
    }
}
