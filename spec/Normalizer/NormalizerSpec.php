<?php

namespace spec\Http\Client\Plugin\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

class NormalizerSpec extends ObjectBehavior
{
    function it_is_initializable(LoggerInterface $logger)
    {
        $this->shouldHaveType('Http\Client\Plugin\Normalizer\Normalizer');
    }

    function it_normalize_request_to_string(RequestInterface $request, UriInterface $uri)
    {
        $uri->__toString()->willReturn('http://foo.com/bar');
        $request->getMethod()->willReturn('GET');
        $request->getUri()->willReturn($uri);
        $request->getProtocolVersion()->willReturn('1.1');

        $this->normalizeRequestToString($request)->shouldReturn('GET http://foo.com/bar 1.1');
    }

    function it_normalize_response_to_string(ResponseInterface $response)
    {
        $response->getReasonPhrase()->willReturn('Ok');
        $response->getProtocolVersion()->willReturn('1.1');
        $response->getStatusCode()->willReturn('200');

        $this->normalizeResponseToString($response)->shouldReturn('200 Ok 1.1');
    }
}
