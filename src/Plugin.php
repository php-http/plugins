<?php

namespace Http\Client\Plugin;

use Http\Client\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * A plugin is a middleware to transform the request and/or
 * the response and use the next callable for
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
interface Plugin
{
    /**
     * handle the request and return the response coming from the next callable
     *
     * @param RequestInterface $request Request to use
     * @param callable         $next    Callback to call to have the request, it muse have the request as it first argument
     * @param callable         $first   First element in the plugin chain, used to to restart a request from the beginning
     *
     * @return Promise
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first);
}
