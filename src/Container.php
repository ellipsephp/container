<?php declare(strict_types=1);

namespace Ellipse;

use Psr\Container\ContainerInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    /**
     * List of registered definitions.
     *
     * @var array
     */
    private $definitions = [];

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
        return array_key_exists($id, $this->definitions);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $definition = $this->definitions[$id] ?? null;

        if (! is_null($definition)) {

            $service = $definition['service'] ?? null;

            if (is_null($service)) {

                $service = $this->makeService($definition);

                $this->definitions[$id]['service'] = $service;

            }

            return $service;

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

            $this->definitions[$id]['factory'] = $factory;

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

            $this->definitions[$id]['extensions'][] = $extension;

        }
    }

    /**
     * Build a service from a definition.
     *
     * @param array $definition
     * @return mixed
     */
    private function makeService(array $definition)
    {
        $factory = $definition['factory'] ?? null;
        $extensions = $definition['extensions'] ?? [];

        $service = is_null($factory) ? null : $factory($this);

        return array_reduce($extensions, function ($service, $extension) {

            return $extension($this, $service);

        }, $service);
    }
}
