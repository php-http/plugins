<?php

namespace spec\Http\Client\Plugin;

use Http\Promise\FulfilledPromise;
use Http\Cookie\Cookie;
use Http\Cookie\CookieJar;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CookiePluginSpec extends ObjectBehavior
{
    function let(CookieJar $cookieJar)
    {
        $this->beConstructedWith($cookieJar);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\CookiePlugin');
    }

    function it_is_a_plugin()
    {
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_loads_cookie(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('+1day'), 'test.com');

        $cookieJar->getCookies()->willReturn([$cookie]);
        $request->getUri()->willReturn($uri);
        $uri->getHost()->willReturn('test.com');
        $uri->getPath()->willReturn('/');

        $request->withAddedHeader('Cookie', 'name=value')->willReturn($request);

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_does_not_load_cookie_if_expired(CookieJar $cookieJar, RequestInterface $request, UriInterface $uri, Promise $promise)
    {
        $cookie = new Cookie('name', 'value', (new \DateTime())->modify('-1day'), 'test.com');

        $cookieJar->getCookies()->willReturn([$cookie]);
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

        $cookieJar->getCookies()->willReturn([$cookie]);
        $request->getUri()->willReturn($uri);
        $uri->getHost()->willReturn('test.com');

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

        $cookieJar->getCookies()->willReturn([$cookie]);
        $request->getUri()->willReturn($uri);
        $uri->getHost()->willReturn('test.com');
        $uri->getPath()->willReturn('/');

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

        $cookieJar->getCookies()->willReturn([$cookie]);
        $request->getUri()->willReturn($uri);
        $uri->getHost()->willReturn('test.com');
        $uri->getPath()->willReturn('/');
        $uri->getScheme()->willReturn('http');

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

        $cookieJar->getCookies()->willReturn([$cookie]);
        $request->getUri()->willReturn($uri);
        $uri->getHost()->willReturn('test.com');
        $uri->getPath()->willReturn('/');
        $uri->getScheme()->willReturn('https');

        $request->withAddedHeader('Cookie', 'name=value')->willReturn($request);

        $this->handleRequest($request, function (RequestInterface $requestReceived) use ($request, $promise) {
            if (Argument::is($requestReceived)->scoreArgument($request->getWrappedObject())) {
                return $promise->getWrappedObject();
            }
        }, function () {});
    }

    function it_saves_cookie(CookieJar $cookieJar, RequestInterface $request, ResponseInterface $response, UriInterface $uri)
    {
        $cookieJar->getCookies()->willReturn([]);

        $next = function () use ($response) {
            return new FulfilledPromise($response->getWrappedObject());
        };

        $response->hasHeader('Set-Cookie')->willReturn(true);
        $response->getHeader('Set-Cookie')->willReturn([
            'cookie=value',
        ]);

        $cookie = new Cookie('cookie', 'value', 0, 'test.com');
        $cookieJar->addCookie($cookie)->shouldBeCalled();

        $request->getUri()->willReturn($uri);
        $uri->getHost()->willReturn('test.com');
        $uri->getPath()->willReturn('/');

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldHaveType('Http\Promise\Promise');
        $promise->wait()->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }
}
