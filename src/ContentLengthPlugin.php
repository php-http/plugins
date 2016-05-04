<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\ContentLengthPlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\ContentLengthPlugin instead.', E_USER_DEPRECATED);

use Http\Message\Encoding\ChunkStream;
use Psr\Http\Message\RequestInterface;

/**
 * Allow to set the correct content length header on the request or to transfer it as a chunk if not possible.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\ContentLengthPlugin} instead.
 */
class ContentLengthPlugin implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        if (!$request->hasHeader('Content-Length')) {
            $stream = $request->getBody();

            // Cannot determine the size so we use a chunk stream
            if (null === $stream->getSize()) {
                $stream = new ChunkStream($stream);
                $request = $request->withBody($stream);
                $request = $request->withAddedHeader('Transfer-Encoding', 'chunked');
            } else {
                $request = $request->withHeader('Content-Length', $stream->getSize());
            }
        }

        return $next($request);
    }
}
