<?php

namespace spec\Http\Client\Plugin;

use Http\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf('Http\Client\Plugin\ErrorPlugin');
    }

    function it_is_a_plugin()
    {
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_throw_request_exception_on_500_error(RequestInterface $request, ResponseInterface $response)
    {
        $response->getStatusCode()->willReturn('500');

        $next = function (RequestInterface $receivedRequest) use($request, $response) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($response->getWrappedObject());
            }
        };

        $this->handleRequest($request, $next, function () {})->shouldReturnAnInstanceOf('Http\Promise\RejectedPromise');
    }

    function it_returns_response(RequestInterface $request, ResponseInterface $response)
    {
        $response->getStatusCode()->willReturn('200');

        $next = function (RequestInterface $receivedRequest) use($request, $response) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($response->getWrappedObject());
            }
        };

        $this->handleRequest($request, $next, function () {})->shouldReturnAnInstanceOf('Http\Promise\FulfilledPromise');
    }

    function it_throws_request_exception_on_custom_regex(RequestInterface $request, ResponseInterface $response)
    {
        $this->beConstructedWith('302');

        $response->getStatusCode()->willReturn('302');
        $next = function (RequestInterface $receivedRequest) use($request, $response) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($response->getWrappedObject());
            }
        };

        $this->handleRequest($request, $next, function () {})->shouldReturnAnInstanceOf('Http\Promise\RejectedPromise');
    }
}
