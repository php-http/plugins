<?php

namespace Http\Client\Plugin\Exception;

@trigger_error('The '.__NAMESPACE__.'\ClientErrorException class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Exception\ClientErrorException instead.', E_USER_DEPRECATED);

use Http\Client\Exception\HttpException;

/**
 * Thrown when there is a client error (4xx).
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Exception\ClientErrorException} instead.
 */
class ClientErrorException extends HttpException
{
}
