<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Utils\Promise\FulfilledPromise;
use Http\Client\Plugin\RedirectPlugin;
use Http\Client\Promise;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class RedirectPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf('Http\Client\Plugin\RedirectPlugin');
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_redirect_on_302(
        UriInterface $uri,
        UriInterface $uriRedirect,
        RequestInterface $request,
        ResponseInterface $responseRedirect,
        RequestInterface $modifiedRequest,
        ResponseInterface $finalResponse,
        Promise $promise
    ) {
        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('/redirect');

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);

        $uri->__toString()->shouldBeCalled()->willReturn('/original');
        $uri->withPath('/redirect')->shouldBeCalled()->willReturn($uriRedirect);

        $uriRedirect->withFragment('')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withQuery('')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->__toString()->shouldBeCalled()->willReturn('/redirect');

        $modifiedRequest->getUri()->willReturn($uriRedirect);
        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('GET');

        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $first = function (RequestInterface $receivedRequest) use($modifiedRequest, $promise) {
            if (Argument::is($modifiedRequest->getWrappedObject())->scoreArgument($receivedRequest)) {
                return $promise->getWrappedObject();
            }
        };

        $promise->wait()->shouldBeCalled();
        $promise->getState()->shouldBeCalled()->willReturn(Promise::FULFILLED);
        $promise->getResponse()->shouldBeCalled()->willReturn($finalResponse);

        $finalPromise = $this->handleRequest($request, $next, $first);
        $finalPromise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\FulfilledPromise');
        $finalPromise->getResponse()->shouldReturn($finalResponse);
    }

    function it_use_storage_on_301(UriInterface $uri, UriInterface $uriRedirect, RequestInterface $request, RequestInterface $modifiedRequest)
    {
        $this->beAnInstanceOf('spec\Http\Client\Plugin\RedirectPluginStub');
        $this->beConstructedWith($uriRedirect, '/original', '301');

        $next = function () {
            throw new \Exception("Must not be called");
        };

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');

        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);
        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('GET');

        $this->handleRequest($request, $next, function () {});
    }

    function it_stores_a_301(
        UriInterface $uri,
        UriInterface $uriRedirect,
        RequestInterface $request,
        ResponseInterface $responseRedirect,
        RequestInterface $modifiedRequest,
        ResponseInterface $finalResponse,
        Promise $promise
    ) {

        $this->beAnInstanceOf('spec\Http\Client\Plugin\RedirectPluginStub');
        $this->beConstructedWith($uriRedirect, '', '301');

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/301-url');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('301');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('/redirect');

        $uri->withPath('/redirect')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withFragment('')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withQuery('')->shouldBeCalled()->willReturn($uriRedirect);

        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);
        $modifiedRequest->getUri()->willReturn($uriRedirect);

        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('GET');
        $uriRedirect->__toString()->shouldBeCalled()->willReturn('/redirect');

        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $first = function (RequestInterface $receivedRequest) use($modifiedRequest, $promise) {
            if (Argument::is($modifiedRequest->getWrappedObject())->scoreArgument($receivedRequest)) {
                return $promise->getWrappedObject();
            }
        };

        $promise->wait()->shouldBeCalled();
        $promise->getState()->shouldBeCalled()->willReturn(Promise::FULFILLED);
        $promise->getResponse()->shouldBeCalled()->willReturn($finalResponse);

        $this->handleRequest($request, $next, $first);
        $this->hasStorage('/301-url')->shouldReturn(true);
    }

    function it_replace_full_url(
        UriInterface $uri,
        UriInterface $uriRedirect,
        RequestInterface $request,
        ResponseInterface $responseRedirect,
        RequestInterface $modifiedRequest,
        ResponseInterface $finalResponse,
        Promise $promise
    ) {
        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('https://server.com:8000/redirect?query#fragment');

        $uri->withScheme('https')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withHost('server.com')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withPort('8000')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withPath('/redirect')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withQuery('query')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withFragment('fragment')->shouldBeCalled()->willReturn($uriRedirect);

        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);
        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('GET');
        $modifiedRequest->getUri()->willReturn($uriRedirect);
        $uriRedirect->__toString()->shouldBeCalled()->willReturn('/redirect');

        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $first = function (RequestInterface $receivedRequest) use($modifiedRequest, $promise) {
            if (Argument::is($modifiedRequest->getWrappedObject())->scoreArgument($receivedRequest)) {
                return $promise->getWrappedObject();
            }
        };

        $promise->wait()->shouldBeCalled();
        $promise->getState()->shouldBeCalled()->willReturn(Promise::FULFILLED);
        $promise->getResponse()->shouldBeCalled()->willReturn($finalResponse);

        $this->handleRequest($request, $next, $first);
    }

    function it_throws_http_exception_on_no_location(UriInterface $uri, RequestInterface $request, ResponseInterface $responseRedirect)
    {
        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(false);

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\RejectedPromise');
        $promise->getException()->shouldReturnAnInstanceOf('Http\Client\Exception\HttpException');
    }

    function it_throws_http_exception_on_invalid_location(UriInterface $uri, RequestInterface $request, ResponseInterface $responseRedirect)
    {
        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('scheme:///invalid');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\RejectedPromise');
        $promise->getException()->shouldReturnAnInstanceOf('Http\Client\Exception\HttpException');
    }

    function it_throw_multi_redirect_exception_on_300(RequestInterface $request, ResponseInterface $responseRedirect)
    {
        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $this->beConstructedWith(true, false);
        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('300');

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\RejectedPromise');
        $promise->getException()->shouldReturnAnInstanceOf('Http\Client\Plugin\Exception\MultipleRedirectionException');
    }

    function it_throw_multi_redirect_exception_on_300_if_no_location(RequestInterface $request, ResponseInterface $responseRedirect)
    {
        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('300');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(false);

        $promise = $this->handleRequest($request, $next, function () {});
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\RejectedPromise');
        $promise->getException()->shouldReturnAnInstanceOf('Http\Client\Plugin\Exception\MultipleRedirectionException');
    }

    function it_switch_method_for_302(
        UriInterface $uri,
        UriInterface $uriRedirect,
        RequestInterface $request,
        ResponseInterface $responseRedirect,
        RequestInterface $modifiedRequest,
        ResponseInterface $finalResponse,
        Promise $promise
    ) {

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('/redirect');

        $uri->withPath('/redirect')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withFragment('')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withQuery('')->shouldBeCalled()->willReturn($uriRedirect);

        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);
        $modifiedRequest->getUri()->willReturn($uriRedirect);

        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('POST');
        $modifiedRequest->withMethod('GET')->shouldBeCalled()->willReturn($modifiedRequest);
        $uriRedirect->__toString()->shouldBeCalled()->willReturn('/redirect');

        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $first = function (RequestInterface $receivedRequest) use($modifiedRequest, $promise) {
            if (Argument::is($modifiedRequest->getWrappedObject())->scoreArgument($receivedRequest)) {
                return $promise->getWrappedObject();
            }
        };

        $promise->wait()->shouldBeCalled();
        $promise->getState()->shouldBeCalled()->willReturn(Promise::FULFILLED);
        $promise->getResponse()->shouldBeCalled()->willReturn($finalResponse);

        $this->handleRequest($request, $next, $first);
    }

    function it_clears_headers(
        UriInterface $uri,
        UriInterface $uriRedirect,
        RequestInterface $request,
        ResponseInterface $responseRedirect,
        RequestInterface $modifiedRequest,
        ResponseInterface $finalResponse,
        Promise $promise
    ) {
        $this->beConstructedWith(['Accept']);

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('/redirect');

        $uri->withPath('/redirect')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withFragment('')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withQuery('')->shouldBeCalled()->willReturn($uriRedirect);

        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);

        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('GET');
        $modifiedRequest->getHeaders()->shouldBeCalled()->willReturn(['Accept' => 'value', 'Cookie' => 'value']);
        $modifiedRequest->withoutHeader('Cookie')->shouldBeCalled()->willReturn($modifiedRequest);
        $uriRedirect->__toString()->shouldBeCalled()->willReturn('/redirect');
        $modifiedRequest->getUri()->willReturn($uriRedirect);

        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $first = function (RequestInterface $receivedRequest) use($modifiedRequest, $promise) {
            if (Argument::is($modifiedRequest->getWrappedObject())->scoreArgument($receivedRequest)) {
                return $promise->getWrappedObject();
            }
        };

        $promise->wait()->shouldBeCalled();
        $promise->getState()->shouldBeCalled()->willReturn(Promise::FULFILLED);
        $promise->getResponse()->shouldBeCalled()->willReturn($finalResponse);

        $this->handleRequest($request, $next, $first);
    }

    function it_throws_circular_redirection_exception(UriInterface $uri, UriInterface $uriRedirect, RequestInterface $request, ResponseInterface $responseRedirect, RequestInterface $modifiedRequest)
    {
        $first = function() {};

        $this->beAnInstanceOf('spec\Http\Client\Plugin\RedirectPluginStubCircular');
        $this->beConstructedWith(spl_object_hash((object)$first));

        $request->getUri()->shouldBeCalled()->willReturn($uri);
        $uri->__toString()->shouldBeCalled()->willReturn('/original');

        $responseRedirect->getStatusCode()->shouldBeCalled()->willReturn('302');
        $responseRedirect->hasHeader('Location')->shouldBeCalled()->willReturn(true);
        $responseRedirect->getHeaderLine('Location')->shouldBeCalled()->willReturn('/redirect');

        $uri->withPath('/redirect')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withFragment('')->shouldBeCalled()->willReturn($uriRedirect);
        $uriRedirect->withQuery('')->shouldBeCalled()->willReturn($uriRedirect);

        $request->withUri($uriRedirect)->shouldBeCalled()->willReturn($modifiedRequest);
        $modifiedRequest->getUri()->willReturn($uriRedirect);

        $modifiedRequest->getMethod()->shouldBeCalled()->willReturn('GET');
        $uriRedirect->__toString()->shouldBeCalled()->willReturn('/redirect');

        $next = function (RequestInterface $receivedRequest) use($request, $responseRedirect) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($responseRedirect->getWrappedObject());
            }
        };

        $promise = $this->handleRequest($request, $next, $first);
        $promise->shouldReturnAnInstanceOf('Http\Client\Utils\Promise\RejectedPromise');
        $promise->getException()->shouldReturnAnInstanceOf('Http\Client\Plugin\Exception\CircularRedirectionException');
    }
}

class RedirectPluginStub extends RedirectPlugin
{
    public function __construct(UriInterface $uri, $storedUrl, $status, $preserveHeader = true, $useDefaultForMultiple = true)
    {
        parent::__construct($preserveHeader, $useDefaultForMultiple);

        $this->redirectStorage[$storedUrl] = [
            'uri' => $uri,
            'status' => $status
        ];
    }

    public function hasStorage($url)
    {
        return isset($this->redirectStorage[$url]);
    }
}

class RedirectPluginStubCircular extends RedirectPlugin
{
    public function __construct($chainHash)
    {
        $this->circularDetection = [
            $chainHash => [
                '/redirect'
            ]
        ];
    }
}
