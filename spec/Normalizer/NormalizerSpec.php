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
        $this->beAnInstanceOf('Http\Client\Plugin\Normalizer\Normalizer');
    }

    function it_normalize_request_to_string(RequestInterface $request)
    {
        $request->getMethod()->shouldBeCalled()->willReturn('GET');
        $request->getRequestTarget()->shouldBeCalled()->willReturn('/');
        $request->getProtocolVersion()->shouldBeCalled()->willReturn('1.1');

        $this->normalizeRequestToString($request)->shouldReturn('GET / 1.1');
    }

    function it_normalize_response_to_string(ResponseInterface $response)
    {
        $response->getReasonPhrase()->shouldBeCalled()->willReturn('Ok');
        $response->getProtocolVersion()->shouldBeCalled()->willReturn('1.1');
        $response->getStatusCode()->shouldBeCalled()->willReturn('200');

        $this->normalizeResponseToString($response)->shouldReturn('200 Ok 1.1');
    }
}
