<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Http\Client\Plugin\Journal\Journal;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Record http call.
 */
class HistoryPlugin implements Plugin
{
    /**
     * @var Journal Journal use to store request / responses / exception.
     */
    private $journal;

    /**
     * @param Journal $journal Journal use to store request / responses / exception.
     */
    public function __construct(Journal $journal)
    {
        $this->journal = $journal;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $journal = $this->journal;

        return $next($request)->then(function (ResponseInterface $response) use ($request, $journal) {
            $journal->addSuccess($request, $response);

            return $response;
        }, function (Exception $exception) use ($request, $journal) {
            $journal->addFailure($request, $exception);

            throw $exception;
        });
    }
}
