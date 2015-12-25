<?php

namespace spec\Http\Client\Plugin;

use Http\Client\Plugin\Plugin;
use Psr\Http\Message\RequestInterface;

class LoopPlugin implements Plugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $first($request);
    }
}
