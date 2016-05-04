<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\Plugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin instead.', E_USER_DEPRECATED);

/**
 * A plugin is a middleware to transform the request and/or the response.
 *
 * The plugin can:
 *  - break the chain and return a response
 *  - dispatch the request to the next middleware
 *  - restart the request
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin} instead.
 */
interface Plugin extends \Http\Client\Common\Plugin
{
}
