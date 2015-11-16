<?php

namespace Http\Client\Plugin\Exception;

use Http\Client\Exception\HttpException;

/**
 * Thrown when circular redirection is detected.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class CircularRedirectionException extends HttpException
{
}
