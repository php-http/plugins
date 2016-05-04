<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\HeaderRemovePlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\HeaderRemovePlugin instead.', E_USER_DEPRECATED);

use Psr\Http\Message\RequestInterface;

/**
 * Removes headers from the request.
 *
 * @author Soufiane Ghzal <sghzal@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\HeaderRemovePlugin} instead.
 */
class HeaderRemovePlugin implements Plugin
{
    private $headers = [];

    /**
     * @param array $headers headers to remove from the request
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
        foreach ($this->headers as $header) {
            if ($request->hasHeader($header)) {
                $request = $request->withoutHeader($header);
            }
        }

        return $next($request);
    }
}
