<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

class CompositeServiceFactory
{
    /**
     * The service factories.
     *
     * @var array
     */
    private $factories;

    /**
     * Set up a composite service factory with the given service factories.
     *
     * @param array $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * Produce a service by reducing the execution of all the service factories.
     *
     * @param \Psr\Container\ContainerInterface
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $factory = array_reduce($this->factories, [$this, 'extended']);

        return $factory($container);
    }

    /**
     * Return the previous service factory extended with the current service
     * factory.
     *
     * @param callable $previous
     * @param callable $current
     * @return callable
     */
    private function extended(callable $previous = null, callable $current): callable
    {
        return is_null($previous) ? $current : new Extension($current, $previous);
    }
}
