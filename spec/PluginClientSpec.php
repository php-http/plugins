<?php

namespace spec\Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Promise;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PluginClientSpec extends ObjectBehavior
{
    function it_is_initializable(HttpClient $client)
    {
        $this->beAnInstanceOf('Http\Client\Plugin\PluginClient', [$client]);
        $this->shouldImplement('Http\Client\HttpClient');
        $this->shouldImplement('Http\Client\HttpAsyncClient');
    }

    function it_sends_request_with_underlying_client(HttpClient $client, RequestInterface $request, ResponseInterface $response)
    {
        $client->sendRequest($request)->shouldBeCalled()->willReturn($response);

        $this->beConstructedWith($client);
        $this->sendRequest($request)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }

    function it_sends_async_request_with_underlying_client(HttpAsyncClient $client, RequestInterface $request, Promise $promise)
    {
        $client->sendAsyncRequest($request)->shouldBeCalled()->willReturn($promise);

        $this->beConstructedWith($client);
        $this->sendAsyncRequest($request)->shouldReturn($promise);
    }
}
