<?php

namespace Http\Client\Plugin\Normalizer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Normalize a request or a response into a string or an array.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @internal Should not be used outside of the logger plugin
 */
class Normalizer
{
    /**
     * Normalize a request to string.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    public function normalizeRequestToString(RequestInterface $request)
    {
        return sprintf('%s %s %s', $request->getMethod(), $request->getRequestTarget(), $request->getProtocolVersion());
    }

    /**
     * Normalize a response to string.
     *
     * @param ResponseInterface $response
     *
     * @return string
     */
    public function normalizeResponseToString(ResponseInterface $response)
    {
        return sprintf('%s %s %s', $response->getStatusCode(), $response->getReasonPhrase(), $response->getProtocolVersion());
    }
}
