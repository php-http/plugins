<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Exception\HttpException;
use Http\Client\Utils\Promise\FulfilledPromise;
use Http\Client\Utils\Promise\RejectedPromise;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class DecoderPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\DecoderPlugin');
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_decodes(RequestInterface $request, ResponseInterface $response, StreamInterface $stream)
    {
        $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress'])->shouldBeCalled()->willReturn($request);
        $next = function () use($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(['chunked']);
        $response->getBody()->shouldBeCalled()->willReturn($stream);
        $response->withBody(Argument::type('Http\Encoding\DechunkStream'))->shouldBeCalled()->willReturn($response);
        $response->withHeader('Transfer-Encoding', [])->shouldBeCalled()->willReturn($response);
        $response->hasHeader('Content-Encoding')->shouldBeCalled()->willReturn(false);

        $stream->isReadable()->shouldBeCalled()->willReturn(true);
        $stream->isWritable()->shouldBeCalled()->willReturn(false);
        $stream->eof()->shouldBeCalled()->willReturn(false);

        $this->handleRequest($request, $next, function () {});
    }

    function it_decodes_in_exception_with_a_response(RequestInterface $request, ResponseInterface $response, StreamInterface $stream)
    {
        $exception = new HttpException('', $request->getWrappedObject(), $response->getWrappedObject());
        $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress'])->shouldBeCalled()->willReturn($request);
        $next = function () use($exception) {
            return new RejectedPromise($exception);
        };

        $response->getStatusCode()->shouldBeCalled()->willReturn(404);
        $response->hasHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(['chunked']);
        $response->getBody()->shouldBeCalled()->willReturn($stream);
        $response->withBody(Argument::type('Http\Encoding\DechunkStream'))->shouldBeCalled()->willReturn($response);
        $response->withHeader('Transfer-Encoding', [])->shouldBeCalled()->willReturn($response);
        $response->hasHeader('Content-Encoding')->shouldBeCalled()->willReturn(false);

        $stream->isReadable()->shouldBeCalled()->willReturn(true);
        $stream->isWritable()->shouldBeCalled()->willReturn(false);
        $stream->eof()->shouldBeCalled()->willReturn(false);

        $this->handleRequest($request, $next, function () {});
    }

    function it_decodes_gzip(RequestInterface $request, ResponseInterface $response, StreamInterface $stream)
    {
        $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress'])->shouldBeCalled()->willReturn($request);
        $next = function () use($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(false);
        $response->hasHeader('Content-Encoding')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Content-Encoding')->shouldBeCalled()->willReturn(['gzip']);
        $response->getBody()->shouldBeCalled()->willReturn($stream);
        $response->withBody(Argument::type('Http\Encoding\GzipDecodeStream'))->shouldBeCalled()->willReturn($response);
        $response->withHeader('Content-Encoding', [])->shouldBeCalled()->willReturn($response);

        $stream->isReadable()->shouldBeCalled()->willReturn(true);
        $stream->isWritable()->shouldBeCalled()->willReturn(false);
        $stream->eof()->shouldBeCalled()->willReturn(false);

        $this->handleRequest($request, $next, function () {});
    }

    function it_decodes_deflate(RequestInterface $request, ResponseInterface $response, StreamInterface $stream)
    {
        $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress'])->shouldBeCalled()->willReturn($request);
        $next = function () use($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(false);
        $response->hasHeader('Content-Encoding')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Content-Encoding')->shouldBeCalled()->willReturn(['deflate']);
        $response->getBody()->shouldBeCalled()->willReturn($stream);
        $response->withBody(Argument::type('Http\Encoding\InflateStream'))->shouldBeCalled()->willReturn($response);
        $response->withHeader('Content-Encoding', [])->shouldBeCalled()->willReturn($response);

        $stream->isReadable()->shouldBeCalled()->willReturn(true);
        $stream->isWritable()->shouldBeCalled()->willReturn(false);
        $stream->eof()->shouldBeCalled()->willReturn(false);

        $this->handleRequest($request, $next, function () {});
    }

    function it_decodes_inflate(RequestInterface $request, ResponseInterface $response, StreamInterface $stream)
    {
        $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress'])->shouldBeCalled()->willReturn($request);
        $next = function () use($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(false);
        $response->hasHeader('Content-Encoding')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Content-Encoding')->shouldBeCalled()->willReturn(['compress']);
        $response->getBody()->shouldBeCalled()->willReturn($stream);
        $response->withBody(Argument::type('Http\Encoding\DecompressStream'))->shouldBeCalled()->willReturn($response);
        $response->withHeader('Content-Encoding', [])->shouldBeCalled()->willReturn($response);

        $stream->isReadable()->shouldBeCalled()->willReturn(true);
        $stream->isWritable()->shouldBeCalled()->willReturn(false);
        $stream->eof()->shouldBeCalled()->willReturn(false);

        $this->handleRequest($request, $next, function () {});
    }

    function it_does_not_decode_with_content_encoding(RequestInterface $request, ResponseInterface $response)
    {
        $this->beConstructedWith(false);

        $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress'])->shouldNotBeCalled();
        $next = function () use($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Transfer-Encoding')->shouldBeCalled()->willReturn(false);
        $response->hasHeader('Content-Encoding')->shouldNotBeCalled();

        $this->handleRequest($request, $next, function () {});
    }
}
