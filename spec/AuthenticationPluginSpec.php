<?php

namespace spec\Http\Client\Plugin;

use Http\Authentication\Authentication;
use Http\Client\Promise;
use Psr\Http\Message\RequestInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthenticationPluginSpec extends ObjectBehavior
{
    function let(Authentication $authentication)
    {
        $this->beConstructedWith($authentication);
    }

    function it_is_initializable(Authentication $authentication)
    {
        $this->shouldHaveType('Http\Client\Plugin\AuthenticationPlugin');
    }

    function it_is_a_plugin()
    {
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_sends_an_authenticated_request(Authentication $authentication, RequestInterface $notAuthedRequest, RequestInterface $authedRequest, Promise $promise)
    {
        $authentication->authenticate($notAuthedRequest)->willReturn($authedRequest);


        $next = function (RequestInterface $request) use($authedRequest, $promise) {
            if (Argument::is($authedRequest->getWrappedObject())->scoreArgument($request)) {
                return $promise->getWrappedObject();
            }
        };

        $this->handleRequest($notAuthedRequest, $next, function () {})->shouldReturn($promise);
    }
}
