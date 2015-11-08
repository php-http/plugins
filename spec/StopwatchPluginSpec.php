<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Exception\NetworkException;
use Http\Client\Plugin\FulfilledPromise;
use Http\Client\Plugin\RejectedPromise;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchPluginSpec extends ObjectBehavior
{
    function it_is_initializable(Stopwatch $stopwatch)
    {
        $this->beAnInstanceOf('Http\Client\Plugin\StopwatchPlugin', [$stopwatch]);
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_records_event(Stopwatch $stopwatch, RequestInterface $request, UriInterface $uri, ResponseInterface $response)
    {
        $this->beConstructedWith($stopwatch);

        $request->getMethod()->shouldBeCalled()->willReturn('GET');
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/');

        $stopwatch->start('GET /', 'php_http.request')->shouldBeCalled();
        $stopwatch->stop('GET /', 'php_http.request')->shouldBeCalled();

        $next = function (RequestInterface $request) use ($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $this->handleRequest($request, $next, function () {});
    }

    function it_records_event_on_error(Stopwatch $stopwatch, RequestInterface $request, UriInterface $uri)
    {
        $this->beConstructedWith($stopwatch);

        $request->getMethod()->shouldBeCalled()->willReturn('GET');
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/');

        $stopwatch->start('GET /', 'php_http.request')->shouldBeCalled();
        $stopwatch->stop('GET /', 'php_http.request')->shouldBeCalled();

        $next = function (RequestInterface $request) {
            return new RejectedPromise(new NetworkException('', $request));
        };

        $this->handleRequest($request, $next, function () {});
    }
}
