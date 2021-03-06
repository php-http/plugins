<?php

namespace spec\Http\Client\Plugin;

use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class HeaderRemovePluginSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('Http\Client\Plugin\HeaderRemovePlugin');
    }

    public function it_is_a_plugin()
    {
        $this->beConstructedWith([]);
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    public function it_removes_the_header(RequestInterface $request)
    {
        $this->beConstructedWith([
            'foo',
            'baz'
        ]);

        $request->hasHeader('foo')->shouldBeCalled()->willReturn(false);

        $request->hasHeader('baz')->shouldBeCalled()->willReturn(true);
        $request->withoutHeader('baz')->shouldBeCalled()->willReturn($request);

        $this->handleRequest($request, function () {}, function () {});
    }
}
