<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\HeaderSetPlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\HeaderSetPlugin instead.', E_USER_DEPRECATED);

use Psr\Http\Message\RequestInterface;

/**
 * Set headers to the request.
 * If the header does not exist it wil be set, if the header already exists it will be replaced.
 *
 * @author Soufiane Ghzal <sghzal@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\HeaderSetPlugin} instead.
 */
class HeaderSetPlugin implements Plugin
{
    private $headers = [];

    /**
     * @param array $headers headers to set to the request
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        foreach ($this->headers as $header => $headerValue) {
            $request = $request->withHeader($header, $headerValue);
        }

        return $next($request);
    }
}
