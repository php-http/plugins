<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Exception\TransferException;
use Http\Client\Plugin\Journal\Journal;
use Http\Client\Tools\Promise\FulfilledPromise;
use Http\Client\Tools\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HistoryPluginSpec extends ObjectBehavior
{
    function let(Journal $journal)
    {
        $this->beConstructedWith($journal);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf('Http\Client\Plugin\JournalPlugin');
    }

    function it_is_a_plugin()
    {
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_records_success(Journal $journal, RequestInterface $request, ResponseInterface $response)
    {
        $next = function (RequestInterface $receivedRequest) use($request, $response) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new FulfilledPromise($response->getWrappedObject());
            }
        };

        $journal->addSuccess($request, $response)->shouldBeCalled();

        $this->handleRequest($request, $next, function () {});
    }

    function it_records_failure(Journal $journal, RequestInterface $request)
    {
        $exception = new TransferException();
        $next = function (RequestInterface $receivedRequest) use($request, $exception) {
            if (Argument::is($request->getWrappedObject())->scoreArgument($receivedRequest)) {
                return new RejectedPromise($exception);
            }
        };

        $journal->addFailure($request, $exception)->shouldBeCalled();

        $this->handleRequest($request, $next, function () {});
    }
}
