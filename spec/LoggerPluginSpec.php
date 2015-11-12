<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Exception\HttpException;
use Http\Client\Exception\NetworkException;
use Http\Client\Plugin\Normalizer\Normalizer;
use Http\Client\Utils\Promise\FulfilledPromise;
use Http\Client\Utils\Promise\RejectedPromise;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoggerPluginSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger, Normalizer $normalizer)
    {
        $this->beConstructedWith($logger, $normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\LoggerPlugin');
    }

    function it_is_a_plugin()
    {
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_logs(LoggerInterface $logger, Normalizer $normalizer, RequestInterface $request, ResponseInterface $response)
    {
        $logger->info('Emit request: "GET / 1.1"', ['request' => $request])->shouldBeCalled();
        $logger->info('Receive response: "200 Ok 1.1" for request: "GET / 1.1"', ['request' => $request, 'response' => $response])->shouldBeCalled();

        $normalizer->normalizeRequestToString($request)->willReturn('GET / 1.1');
        $normalizer->normalizeResponseToString($response)->willReturn('200 Ok 1.1');

        $next = function () use ($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $this->handleRequest($request, $next, function () {});
    }

    function it_logs_exception(LoggerInterface $logger, Normalizer $normalizer, RequestInterface $request)
    {
        $exception = new NetworkException('Cannot connect', $request->getWrappedObject());

        $logger->info('Emit request: "GET / 1.1"', ['request' => $request])->shouldBeCalled();
        $logger->error('Error: "Cannot connect" when emitting request: "GET / 1.1"', ['request' => $request, 'exception' => $exception])->shouldBeCalled();

        $normalizer->normalizeRequestToString($request)->willReturn('GET / 1.1');

        $next = function () use ($exception) {
            return new RejectedPromise($exception);
        };

        $this->handleRequest($request, $next, function () {});
    }

    function it_logs_response_within_exception(LoggerInterface $logger, Normalizer $normalizer, RequestInterface $request, ResponseInterface $response)
    {
        $exception = new HttpException('Forbidden', $request->getWrappedObject(), $response->getWrappedObject());

        $logger->info('Emit request: "GET / 1.1"', ['request' => $request])->shouldBeCalled();
        $logger->error('Error: "Forbidden" with response: "403 Forbidden 1.1" when emitting request: "GET / 1.1"', [
            'request'   => $request,
            'response'  => $response,
            'exception' => $exception
        ])->shouldBeCalled();

        $normalizer->normalizeRequestToString($request)->willReturn('GET / 1.1');
        $normalizer->normalizeResponseToString($response)->willReturn('403 Forbidden 1.1');

        $next = function () use ($exception) {
            return new RejectedPromise($exception);
        };

        $this->handleRequest($request, $next, function () {});
    }
}
