<?php

namespace Http\Client\Plugin\Exception;

@trigger_error('The '.__NAMESPACE__.'\CircularRedirectionException class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Exception\CircularRedirectionException instead.', E_USER_DEPRECATED);

use Http\Client\Exception\HttpException;

/**
 * Thrown when circular redirection is detected.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Exception\CircularRedirectionException} instead.
 */
class CircularRedirectionException extends HttpException
{
}
