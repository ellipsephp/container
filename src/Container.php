<?php declare(strict_types=1);

namespace Ellipse;

use TypeError;

use Ellipse\Container\AbstractContainer;
use Ellipse\Container\Exceptions\ServiceProviderTypeException;

class Container extends AbstractContainer
{
    /**
     * Set up a container with the given array of service providers.
     *
     * @param \Interop\Container\ServiceProviderInterface[] $providers
     * @throws \Ellipse\Container\Exceptions\ServiceProviderTypeException
     */
    public function __construct(array $providers = [])
    {
        try {

            $providers = array_values($providers);

            parent::__construct(...$providers);

        }

        catch (TypeError $e) {

            throw new ServiceProviderTypeException($providers, $e);

        }
    }
}
