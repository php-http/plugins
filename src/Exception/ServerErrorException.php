<?php

namespace Http\Client\Plugin\Exception;

@trigger_error('The '.__NAMESPACE__.'\ServerErrorException class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Exception\ServerErrorException instead.', E_USER_DEPRECATED);

use Http\Client\Exception\HttpException;

/**
 * Thrown when there is a server error (5xx).
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Exception\ServerErrorException} instead.
 */
class ServerErrorException extends HttpException
{
}
