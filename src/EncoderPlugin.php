<?php

namespace Http\Client\Plugin;

use Psr\Http\Message\RequestInterface;

/**
 * Allow to encode request body with chunk, deflate, compress or gzip encoding
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class EncoderPlugin implements Plugin
{
    /**
     * {@inheritDoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        // TODO: Implement handleRequest() method.
    }
}
