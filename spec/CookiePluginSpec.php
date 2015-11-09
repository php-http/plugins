<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Promise;
use Http\Client\Utils\Promise\FulfilledPromise;
use Http\Cookie\Cookie;
use Http\Cookie\CookieJar;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class CookiePluginSpec extends ObjectBehavior
{
    function it_is_initializable(CookieJar $cookieJar)
    {
        $this->beAnInstanceOf('Http\Client\Plugin\CookiePlugin', [$cookieJar]);
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_loads_cookie(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('+1day'), 'test.com');
        $this->beConstructedWith($cookieJar);

        $cookieJar->getCookies()->shouldBeCalled()->willReturn([$cookie]);
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->getHost()->shouldBeCalled()->willReturn('test.com');
        $uri->getPath()->shouldBeCalled()->willReturn('/');

        $request->withAddedHeader('Cookie', 'name=value')->shouldBeCalled()->willReturn($request);

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_does_not_load_cookie_if_expired(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('-1day'), 'test.com');
        $this->beConstructedWith($cookieJar);

        $cookieJar->getCookies()->shouldBeCalled()->willReturn([$cookie]);
        $request->withAddedHeader('Cookie', 'name=value')->shouldNotBeCalled();

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_does_not_load_cookie_if_domain_does_not_match(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('+1day'), 'test2.com');
        $this->beConstructedWith($cookieJar);

        $cookieJar->getCookies()->shouldBeCalled()->willReturn([$cookie]);
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->getHost()->shouldBeCalled()->willReturn('test.com');

        $request->withAddedHeader('Cookie', 'name=value')->shouldNotBeCalled();

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_does_not_load_cookie_if_path_does_not_match(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('+1day'), 'test.com', '/sub');
        $this->beConstructedWith($cookieJar);

        $cookieJar->getCookies()->shouldBeCalled()->willReturn([$cookie]);
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->getHost()->shouldBeCalled()->willReturn('test.com');
        $uri->getPath()->shouldBeCalled()->willReturn('/');

        $request->withAddedHeader('Cookie', 'name=value')->shouldNotBeCalled();

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_does_not_load_cookie_when_cookie_is_secure(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('+1day'), 'test.com', null, true);
        $this->beConstructedWith($cookieJar);

        $cookieJar->getCookies()->shouldBeCalled()->willReturn([$cookie]);
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->getHost()->shouldBeCalled()->willReturn('test.com');
        $uri->getPath()->shouldBeCalled()->willReturn('/');
        $uri->getScheme()->shouldBeCalled()->willReturn('http');

        $request->withAddedHeader('Cookie', 'name=value')->shouldNotBeCalled();

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_loads_cookie_when_cookie_is_secure(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('+1day'), 'test.com', null, true);
        $this->beConstructedWith($cookieJar);

        $cookieJar->getCookies()->shouldBeCalled()->willReturn([$cookie]);
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->getHost()->shouldBeCalled()->willReturn('test.com');
        $uri->getPath()->shouldBeCalled()->willReturn('/');
        $uri->getScheme()->shouldBeCalled()->willReturn('https');

        $request->withAddedHeader('Cookie', 'name=value')->shouldBeCalled()->willReturn($request);

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_saves_cookie(CookieJar $cookieJar, RequestInterface $request, ResponseInterface $response, UriInterface $uri)
    {
        $this->beConstructedWith($cookieJar);
        $cookieJar->getCookies()->shouldBeCalled()->willReturn([]);

        $next = function () use ($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Set-Cookie')->shouldBeCalled()->willReturn(true);
        $response->getHeader('Set-Cookie')->shouldBeCalled()->willReturn([
            'cookie=value',
        ]);

        $cookie = new Cookie('cookie', 'value', 0, 'test.com');
        $cookieJar->addCookie($cookie)->shouldBeCalled();

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->getHost()->shouldBeCalled()->willReturn('test.com');
        $uri->getPath()->shouldBeCalled()->willReturn('/');

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Promise');
        $response = $promise->getResponse();
        $response->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }
}
