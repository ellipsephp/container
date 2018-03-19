<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use TypeError;

use Psr\Container\ContainerExceptionInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Exceptions\TypeErrorMessage;

class ServiceProviderTypeException extends TypeError implements ContainerExceptionInterface
{
    public function __construct(array $providers)
    {
        $value = current(array_filter($providers, function ($provider) {

            return ! $provider instanceof ServiceProviderInterface;

        }));

        $msg = new TypeErrorMessage('service provider', $value, ServiceProviderInterface::class);

        parent::__construct((string) $msg);
    }
}
