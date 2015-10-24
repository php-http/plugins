<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Http\Client\Promise;
use Psr\Http\Message\ResponseInterface;

/**
 * A promise already fulfilled
 */
class FulfilledPromise implements Promise
{
    /** @var ResponseInterface */
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        try {
            return new FulfilledPromise($onFulfilled($this->response));
        } catch (Exception $e) {
            return new RejectedPromise($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return Promise::FULFILLED;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function getException()
    {
        throw new \LogicException("Fulfilled promise, response not available");
    }

    /**
     * {@inheritdoc}
     */
    public function wait()
    {
    }
}
 