<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\AuthenticationPlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\AuthenticationPlugin instead.', E_USER_DEPRECATED);

use Http\Message\Authentication;
use Psr\Http\Message\RequestInterface;

/**
 * Send an authenticated request.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\AuthenticationPlugin} instead.
 */
class AuthenticationPlugin implements Plugin
{
    /**
     * @var Authentication An authentication system
     */
    private $authentication;

    /**
     * @param Authentication $authentication
     */
    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $request = $this->authentication->authenticate($request);

        return $next($request);
    }
}
