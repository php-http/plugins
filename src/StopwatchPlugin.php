<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Know the duration of a request call with the stopwatch component
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class StopwatchPlugin implements Plugin
{
    const CATEGORY = 'php_http.request';

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $eventName = $this->getStopwatchEventName($request);
        $this->stopwatch->start($eventName, self::CATEGORY);

        return $next($request)->then(function (ResponseInterface $response) use($eventName) {
            $this->stopwatch->stop($eventName, self::CATEGORY);

            return $response;
        }, function (Exception $exception) use($eventName) {
            $this->stopwatch->stop($eventName, self::CATEGORY);

            throw $exception;
        });
    }

    /**
     * Generate the event name
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    private function getStopwatchEventName(RequestInterface $request)
    {
        return sprintf('%s %s', $request->getMethod(), $request->getUri()->__toString());
    }
}
