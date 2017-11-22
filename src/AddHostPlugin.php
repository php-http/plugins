<?php

namespace Http\Client\Plugin;

@trigger_error('The '.__NAMESPACE__.'\AddHostPlugin class is deprecated since version 1.1 and will be removed in 2.0. Use Http\Client\Common\Plugin\AddHostPlugin instead.', E_USER_DEPRECATED);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Add schema and host to a request. Can be set to overwrite the schema and host if desired.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @deprecated since since version 1.1, and will be removed in 2.0. Use {@link \Http\Client\Common\Plugin\AddHostPlugin} instead.
 */
class AddHostPlugin implements Plugin
{
    /**
     * @var UriInterface
     */
    private $host;

    /**
     * @var bool
     */
    private $replace;

    /**
     * @param UriInterface $host
     * @param array        $config {
     *
     *     @var bool $replace True will replace all hosts, false will only add host when none is specified.
     * }
     */
    public function __construct(UriInterface $host, array $config = [])
    {
        if ('' === $host->getHost()) {
            throw new \LogicException('Host can not be empty');
        }

        $this->host = $host;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($config);

        $this->replace = $options['replace'];
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        if ($this->replace || '' === $request->getUri()->getHost()) {
            $uri = $request->getUri()->withHost($this->host->getHost());
            $uri = $uri->withScheme($this->host->getScheme());

            $request = $request->withUri($uri);
        }

        return $next($request);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'replace' => false,
        ]);
        $resolver->setAllowedTypes('replace', 'bool');
    }
}
