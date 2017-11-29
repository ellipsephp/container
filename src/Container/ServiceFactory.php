<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

class ServiceFactory
{
    /**
     * The service factory.
     *
     * @var callable
     */
    private $factory;

    /**
     * The previously defined value.
     *
     * @var \Ellipse\Container\ServiceFactory $previous
     */
    private $previous;

    /**
     * The cached service.
     *
     * @var mixed
     */
    private $cached;

    /**
     * Set a service factory with the given callable factory and the given
     * previous service factory.
     *
     * @param callable                          $factory
     * @param \Ellipse\Container\ServiceFactory $previous
     */
    public function __construct(callable $factory, ServiceFactory $previous = null)
    {
        $this->factory = $factory;
        $this->previous = $previous;
    }

    /**
     * Proxy the callable factory by givien it the previous value when present.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return $mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $this->cached) {

            $this->cached = is_null($this->previous)
                ? ($this->factory)($container)
                : ($this->factory)($container, ($this->previous)($container));

        }

        return $this->cached;
    }
}
