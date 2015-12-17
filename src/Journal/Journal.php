<?php

namespace Http\Client\Plugin\Journal;

use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Journal.
 *
 * Records history of http calls.
 */
interface Journal
{
    /**
     * Record a successful call.
     *
     * @param RequestInterface  $request  Request use to make the call
     * @param ResponseInterface $response Response returned by the call
     */
    public function addSuccess(RequestInterface $request, ResponseInterface $response);

    /**
     * Record a failed call.
     *
     * @param RequestInterface $request   Request use to make the call
     * @param Exception        $exception Exception returned by the call
     */
    public function addFailure(RequestInterface $request, Exception $exception);
}
