<?php

namespace Http\Client\Plugin;

use Http\Client\Exception\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Throw exception when the response of a request is not acceptable.
 *
 * By default an exception will be thrown for all status codes from 400 to 599.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class ErrorPlugin implements Plugin
{
    /**
     * Status code matcher to return an exception.
     *
     * @var string
     */
    private $statusCodeRegex;

    /**
     * @param string $statusCodeRegex
     */
    public function __construct($statusCodeRegex = '[45][0-9]{2}')
    {
        $this->statusCodeRegex = $statusCodeRegex;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $promise = $next($request);

        return $promise->then(function (ResponseInterface $response) use($request) {
            if (preg_match('/'.$this->statusCodeRegex.'/', (string)$response->getStatusCode())) {
                throw new HttpException('The server returned an error', $request, $response);
            }

            return $response;
        });
    }
}
