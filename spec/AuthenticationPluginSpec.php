<?php

namespace spec\Http\Client\Plugin;

use Http\Authentication\Authentication;
use Http\Client\Promise;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;

class AuthenticationPluginSpec extends ObjectBehavior
{
    function it_is_initializable(Authentication $authentication)
    {
        $this->beAnInstanceOf('Http\Client\Plugin\AuthenticationPlugin', [$authentication]);
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_sends_an_authenticated_request(Authentication $authentication, RequestInterface $notAuthedRequest, RequestInterface $authedRequest, Promise $promise)
    {
        $this->beConstructedWith($authentication);
        $authentication->authenticate($notAuthedRequest)->shouldBeCalled()->willReturn($authedRequest);


        $next = function (RequestInterface $request) use($authedRequest, $promise) {
            if (Argument::is($authedRequest->getWrappedObject())->scoreArgument($request)) {
                return $promise->getWrappedObject();
            }
        };

        $this->handleRequest($notAuthedRequest, $next, function () {})->shouldReturn($promise);
    }
}
