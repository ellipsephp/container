<?php declare(strict_types=1);

namespace Ellipse\Container;

use Psr\Container\ContainerInterface;

class Extension
{
    /**
     * The service factory.
     *
     * @var callable
     */
    private $factory;

    /**
     * The previous service factory.
     *
     * @var callable $previous
     */
    private $previous;

    /**
     * Set up an extension with the given service factory and previous service
     * factory.
     *
     * @param callable $factory
     * @param callable $previous
     */
    public function __construct(callable $factory, callable $previous)
    {
        $this->factory = $factory;
        $this->previous = $previous;
    }

    /**
     * Get the previous value and proxy the service factory.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return $mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        return ($this->factory)($container, ($this->previous)($container));
    }
}
