<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Http\Client\Plugin\Normalizer\Normalizer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Log request, response and exception for a HTTP Client
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class LoggerPlugin implements Plugin
{
    /**
     * @var LoggerInterface Logger to log request / response / exception for a http call
     */
    private $logger;

    /**
     * @var Normalizer Normalize request and response to string or array
     */
    private $normalizer;

    public function __construct(LoggerInterface $logger, Normalizer $normalizer)
    {
        $this->logger     = $logger;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $this->logger->info(sprintf('Emit request: "%s"', $this->normalizer->normalizeRequestToString($request)), ['request' => $request]);

        return $next($request)->then(function (ResponseInterface $response) use($request) {
            $this->logger->info(
                sprintf('Receive response: "%s" for request: "%s"', $this->normalizer->normalizeResponseToString($response), $this->normalizer->normalizeRequestToString($request)),
                [
                    'request' => $request,
                    'response' => $response,
                ]
            );

            return $response;
        }, function (Exception $exception) use($request) {
            if ($exception instanceof Exception\HttpException) {
                $this->logger->error(
                    sprintf('Error: "%s" with response: "%s" when emitting request: "%s"', $exception->getMessage(), $this->normalizer->normalizeResponseToString($exception->getResponse()), $this->normalizer->normalizeRequestToString($request)),
                    [
                        'request' => $request,
                        'response' => $exception->getResponse(),
                        'exception' => $exception
                    ]
                );
            } else {
                $this->logger->error(
                    sprintf('Error: "%s" when emitting request: "%s"', $exception->getMessage(), $this->normalizer->normalizeRequestToString($request)),
                    [
                        'request' => $request,
                        'exception' => $exception
                    ]
                );
            }

            throw $exception;
        });
    }
}
