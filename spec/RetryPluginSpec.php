<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Exception;
use Http\Client\Utils\Promise\FulfilledPromise;
use Http\Client\Utils\Promise\RejectedPromise;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RetryPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf('Http\Client\Plugin\ErrorPlugin');
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_returns_response(RequestInterface $request, ResponseInterface $response)
    {
        $next = function (RequestInterface $receivedRequest) use($request, $response) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($response->getWrappedObject());
            }
        };

        $this->handleRequest($request, $next, function () {})->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\FulfilledPromise');
    }

    function it_throws_exception_on_multiple_exceptions(RequestInterface $request)
    {
        $exception1 = new Exception\NetworkException("Exception 1", $request->getWrappedObject());
        $exception2 = new Exception\NetworkException("Exception 2", $request->getWrappedObject());

        $count = 0;
        $next  = function (RequestInterface $receivedRequest) use($request, $exception1, $exception2, &$count) {
            $count++;
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                if ($count == 1) {
                    return new RejectedPromise($exception1);
                }

                if ($count == 2) {
                    return new RejectedPromise($exception2);
                }
            }
        };

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\RejectedPromise');
        $promise->getException()->shouldReturn($exception2);
    }

    function it_returns_response_on_second_try(RequestInterface $request, ResponseInterface $response)
    {
        $exception = new Exception\NetworkException("Exception 1", $request->getWrappedObject());

        $count = 0;
        $next  = function (RequestInterface $receivedRequest) use($request, $exception, $response, &$count) {
            $count++;
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                if ($count == 1) {
                    return new RejectedPromise($exception);
                }

                if ($count == 2) {
                    return new FulfilledPromise($response->getWrappedObject());
                }
            }
        };

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\FulfilledPromise');
        $promise->getResponse()->shouldReturn($response);
    }

    function it_does_not_keep_history_of_old_failure(RequestInterface $request, ResponseInterface $response)
    {
        $exception = new Exception\NetworkException("Exception 1", $request->getWrappedObject());

        $count = 0;
        $next  = function (RequestInterface $receivedRequest) use($request, $exception, $response, &$count) {
            $count++;
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                if ($count % 2 == 1) {
                    return new RejectedPromise($exception);
                }

                if ($count % 2 == 0) {
                    return new FulfilledPromise($response->getWrappedObject());
                }
            }
        };

        $this->handleRequest($request, $next, function () {})->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\FulfilledPromise');
        $this->handleRequest($request, $next, function () {})->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\FulfilledPromise');
    }
}
