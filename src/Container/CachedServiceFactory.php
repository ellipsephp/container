<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

class CachedServiceFactory
{
    /**
     * The service factory.
     *
     * @var callable
     */
    private $factory;

    /**
     * The cached value.
     *
     * @var mixed
     */
    private $value;

    /**
     * Set a cached service factory with the given service factory.
     *
     * @param callable $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Return the cached value or proxy the service factory.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return $mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $this->value) {

            return $this->value = ($this->factory)($container);

        }

        return $this->value;
    }
}
