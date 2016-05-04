<?php

namespace Http\Client\Plugin\Exception;

@trigger_error('The '.__NAMESPACE__.'\MultipleRedirectionException class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Exception\MultipleRedirectionException instead.', E_USER_DEPRECATED);

use Http\Client\Exception\HttpException;

/**
 * Redirect location cannot be chosen.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Exception\MultipleRedirectionException} instead.
 */
class MultipleRedirectionException extends HttpException
{
}
