<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Http\Encoding\DechunkStream;
use Http\Encoding\DecompressStream;
use Http\Encoding\GzipDecodeStream;
use Http\Encoding\InflateStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Allow to decode response body with a chunk, deflate, compress or gzip encoding
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class DecoderPlugin implements Plugin
{
    /**
     * @var bool Whether this plugin decode stream with value in the Content-Encoding header (default to true).
     *
     * If set to false only the Transfer-Encoding header will be used.
     */
    private $useContentEncoding;

    public function __construct($useContentEncoding = true)
    {
        $this->useContentEncoding = $useContentEncoding;
    }

    /**
     * {@inheritDoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        if ($this->useContentEncoding) {
            $request = $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress']);
        }

        return $next($request)->then(function (ResponseInterface $response) {
            return $this->decodeResponse($response);
        }, function (Exception $exception) use($request) {
            if ($exception instanceof Exception\HttpException) {
                $response  = $this->decodeResponse($exception->getResponse());
                $exception = new Exception\HttpException($exception->getMessage(), $request, $response, $exception);
            }

            throw $exception;
        });
    }

    /**
     * Decode a response body given its Transfer-Encoding or Content-Encoding value
     *
     * @param ResponseInterface $response Response to decode
     *
     * @return ResponseInterface New response decoded
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        if ($response->hasHeader('Transfer-Encoding')) {
            $encodings    = $response->getHeader('Transfer-Encoding');
            $newEncodings = [];

            while ($encoding = array_pop($encodings)) {
                $stream = $this->decorateStream($encoding, $response->getBody());

                if (false === $stream) {
                    array_unshift($newEncodings, $encoding);

                    continue;
                }

                $response = $response->withBody($stream);
            }

            $response = $response->withHeader('Transfer-Encoding', $newEncodings);
        }

        if ($this->useContentEncoding && $response->hasHeader('Content-Encoding')) {
            $encodings    = $response->getHeader('Content-Encoding');
            $newEncodings = [];

            while ($encoding = array_pop($encodings)) {
                $stream = $this->decorateStream($encoding, $response->getBody());

                if (false === $stream) {
                    array_unshift($newEncodings, $encoding);

                    continue;
                }

                $response = $response->withBody($stream);
            }

            $response = $response->withHeader('Content-Encoding', $newEncodings);
        }

        return $response;
    }

    /**
     * Decorate a stream given an encoding
     *
     * @param string          $encoding
     * @param StreamInterface $stream
     *
     * @return StreamInterface|false A new stream interface or false if encoding is not supported
     */
    protected function decorateStream($encoding, StreamInterface $stream)
    {
        if (strtolower($encoding) == 'chunked') {
            return new DechunkStream($stream);
        }

        if (strtolower($encoding) == 'compress') {
            return new DecompressStream($stream);
        }

        if (strtolower($encoding) == 'deflate') {
            return new InflateStream($stream);
        }

        if (strtolower($encoding) == 'gzip') {
            return new GzipDecodeStream($stream);
        }

        return false;
    }
}
