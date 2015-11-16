<?php

namespace spec\Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;

class PluginClientSpec extends ObjectBehavior
{
    function let(HttpClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\PluginClient');
    }

    function it_is_an_http_client()
    {
        $this->shouldImplement('Http\Client\HttpClient');
    }

    function it_is_an_http_async_client()
    {
        $this->shouldImplement('Http\Client\HttpAsyncClient');
    }

    function it_sends_request_with_underlying_client(HttpClient $client, RequestInterface $request, ResponseInterface $response)
    {
        $client->sendRequest($request)->willReturn($response);

        $this->sendRequest($request)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }

    function it_sends_async_request_with_underlying_client(HttpAsyncClient $asyncClient, RequestInterface $request, Promise $promise)
    {
        $asyncClient->sendAsyncRequest($request)->willReturn($promise);

        $this->beConstructedWith($asyncClient);
        $this->sendAsyncRequest($request)->shouldReturn($promise);
    }
}
