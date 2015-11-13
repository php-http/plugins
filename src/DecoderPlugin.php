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

    /**
     * @param bool $useContentEncoding Whether this plugin decode stream with value in the Content-Encoding header (default to true).
     *
     * If set to false only the Transfer-Encoding header will be used.
     */
    public function __construct($useContentEncoding = true)
    {
        $this->useContentEncoding = $useContentEncoding;
    }

    /**
     * {@inheritDoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $request = $request->withHeader('TE', ['gzip', 'deflate', 'compress', 'chunked']);

        if ($this->useContentEncoding) {
            $request = $request->withHeader('Accept-Encoding', ['gzip', 'deflate', 'compress']);
        }

        return $next($request)->then(function (ResponseInterface $response) {
            return $this->decodeResponse($response);
        });
    }

    /**
     * Decode a response body given its Transfer-Encoding or Content-Encoding value
     *
     * @param ResponseInterface $response Response to decode
     *
     * @return ResponseInterface New response decoded
     */
    private function decodeResponse(ResponseInterface $response)
    {
        $response = $this->decodeOnEncodingHeader('Transfer-Encoding', $response);

        if ($this->useContentEncoding) {
            $response = $this->decodeOnEncodingHeader('Content-Encoding', $response);
        }

        return $response;
    }

    /**
     * Decode a response on a specific header (content encoding or transfer encoding mainly)
     *
     * @param string            $headerName Name of the header
     * @param ResponseInterface $response   Response
     *
     * @return ResponseInterface A new instance of the response decoded
     */
    private function decodeOnEncodingHeader($headerName, ResponseInterface $response)
    {
        if ($response->hasHeader($headerName)) {
            $encodings    = $response->getHeader($headerName);
            $newEncodings = [];

            while ($encoding = array_pop($encodings)) {
                $stream = $this->decorateStream($encoding, $response->getBody());

                if (false === $stream) {
                    array_unshift($newEncodings, $encoding);

                    continue;
                }

                $response = $response->withBody($stream);
            }

            $response = $response->withHeader($headerName, $newEncodings);
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
    private function decorateStream($encoding, StreamInterface $stream)
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
