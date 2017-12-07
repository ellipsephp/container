<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Container\ContainerInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container\Extension;
use Ellipse\Container\CachedServiceFactory;
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
     * Set up a container with the given array of service providers.
     *
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        $this->factories = $this->cache(
            $this->reduceExtensions(
                $providers,
                $this->reduceFactories($providers)
            )
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
    private function reduceFactories(array $providers): array
    {
        return array_reduce($providers, function ($factories, ServiceProviderInterface $provider) {

            return array_merge($factories, $provider->getFactories());

        }, []);
    }

    /**
     * Return the given service factory map with all the extensions from the
     * given service providers.
     *
     * @param array $providers
     * @param array $factories
     * @return array
     */
    private function reduceExtensions(array $providers, array $factories): array
    {
        return array_reduce($providers, function ($factories, ServiceProviderInterface $provider) {

            $extensions = $provider->getExtensions();

            foreach ($extensions as $id => $extension) {

                $previous = $factories[$id] ?? null;

                $factories[$id] = is_null($previous)
                    ? $extension
                    : new Extension($extension, $previous);

            }

            return $factories;

        }, $factories);
    }

    /**
     * Return the given service factory map with all its service factories
     * wrapped inside a cached service factory.
     *
     * @param array $factories
     * @return array
     */
    private function cache(array $factories): array
    {
        $cache = function (callable $factory) { return new CachedServiceFactory($factory); };

        return array_map($cache, $factories);
    }
}
