<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\StopwatchPlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\StopwatchPlugin instead.', E_USER_DEPRECATED);

use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Measure the duration of a request call with the stopwatch component.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\StopwatchPlugin} instead.
 */
class StopwatchPlugin implements Plugin
{
    const CATEGORY = 'php_http.request';

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @param Stopwatch $stopwatch
     */
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

        return $next($request)->then(function (ResponseInterface $response) use ($eventName) {
            $this->stopwatch->stop($eventName);

            return $response;
        }, function (Exception $exception) use ($eventName) {
            $this->stopwatch->stop($eventName);

            throw $exception;
        });
    }

    /**
     * Generates the event name.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    private function getStopwatchEventName(RequestInterface $request)
    {
        return sprintf('%s %s', $request->getMethod(), $request->getRequestTarget());
    }
}
