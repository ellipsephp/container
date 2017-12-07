<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Container\ContainerInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container\CompositeServiceFactory;
use Ellipse\Container\CachedServiceFactory;
use Ellipse\Container\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    /**
     * Associative array of alias => service factory pairs.
     *
     * @var array
     */
    private $factories = [];

    /**
     * Set up a container with the given array of service providers.
     *
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        $this->factories = $this->merge(
            $this->serviceFactoryMap($providers),
            $this->serviceExtensionMap($providers)
        );
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
     * Return a service factory map from the given service providers.
     *
     * @param array $providers
     * @return array
     */
    private function serviceFactoryMap(array $providers): array
    {
        $wrap = function ($factory) { return [$factory]; };

        return array_reduce($providers, function ($factories, ServiceProviderInterface $provider) use ($wrap) {

            return array_merge($factories, array_map($wrap, $provider->getFactories()));

        }, []);
    }

    /**
     * Return a service extension map from the given service providers.
     *
     * @param array $providers
     * @return array
     */
    private function serviceExtensionMap(array $providers): array
    {
        $wrap = function ($factory) { return [$factory]; };

        return array_reduce($providers, function ($extensions, ServiceProviderInterface $provider) use ($wrap) {

            return array_merge_recursive($extensions, array_map($wrap, $provider->getExtensions()));

        }, []);
    }

    /**
     * Return the map resulting from merging the given service factory map and
     * service extension map.
     *
     * @param array $factories
     * @param array $extensions
     * @return array
     */
    private function merge(array $factories, array $extensions): array
    {
        return array_map([$this, 'factory'], array_merge_recursive($factories, $extensions));
    }

    /**
     * Return a cached composite service factory from the given array of service
     * factories.
     *
     * @param array $factories
     * @return \Ellipse\Container\CachedServiceFactory
     */
    private function factory(array $factories): CachedServiceFactory
    {
        return new CachedServiceFactory(
            new CompositeServiceFactory($factories)
        );
    }
}
