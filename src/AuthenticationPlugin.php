<?php

namespace Http\Client\Plugin;

use Http\Authentication\Authentication;
use Psr\Http\Message\RequestInterface;

/**
 * Send an authenticated request
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class AuthenticationPlugin implements Plugin
{
    /**
     * @var Authentication An authentication system
     */
    private $authentication;

    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * {@inheritDoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $request = $this->authentication->authenticate($request);

        return $next($request);
    }

}
