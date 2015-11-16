<?php

namespace Http\Client\Plugin\Exception;

use Http\Client\Exception\HttpException;

/**
 * Redirect location cannot be chosen.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class MultipleRedirectionException extends HttpException
{
}
