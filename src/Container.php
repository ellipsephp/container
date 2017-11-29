<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Container\ContainerInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container\ServiceFactory;
use Ellipse\Container\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    /**
     * List of registered factories.
     *
     * @var array
     */
    private $factories = [];

    /**
     * Set up a container with a list of service providers to register.
     *
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        array_map([$this, 'registerFactories'], $providers);
        array_map([$this, 'registerExtensions'], $providers);
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return isset($this->factories[$id]);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        if ($this->has($id)) {

            return $this->factories[$id]($this);

        }

        throw new NotFoundException($id);
    }

    /**
     * Register the factories of the given service provider.
     *
     * @param \Interop\Container\ServiceProviderInterface $provider
     * @return void
     */
    private function registerFactories(ServiceProviderInterface $provider): void
    {
        $factories = $provider->getFactories();

        foreach ($factories as $id => $factory) {

            $this->factories[$id] = new ServiceFactory($factory);

        }
    }

    /**
     * Register the extensions of the given service provider.
     *
     * @param \Interop\Container\ServiceProviderInterface $provider
     * @return void
     */
    private function registerExtensions(ServiceProviderInterface $provider): void
    {
        $extensions = $provider->getExtensions();

        foreach ($extensions as $id => $extension) {

            $previous = $this->factories[$id] ?? null;

            $this->factories[$id] = new ServiceFactory($extension, $previous);

        }
    }
}
