<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Http\Client\Promise;

class RejectedPromise implements Promise
{
    /** @var Exception */
    private $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        try {
            return new FulfilledPromise($onRejected($this->exception));
        } catch (Exception $e) {
            return new RejectedPromise($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return Promise::REJECTED;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        throw new \LogicException("Promise is rejected, no response available");
    }

    /**
     * {@inheritdoc}
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * {@inheritdoc}
     */
    public function wait()
    {
    }
}
