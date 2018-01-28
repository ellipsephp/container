<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use TypeError;

use Psr\Container\ContainerExceptionInterface;

use Interop\Container\ServiceProviderInterface;

class ServiceProviderTypeException extends TypeError implements ContainerExceptionInterface
{
    public function __construct(array $providers, TypeError $previous)
    {
        $template = "Trying to use a value of type %s as service provider - object implementing %s expected";

        $value = current(array_filter($providers, function ($provider) {

            return ! $provider instanceof ServiceProviderInterface;

        }));

        $type = is_object($value) ? get_class($value) : gettype($value);

        $msg = sprintf($template, $type, ServiceProviderInterface::class);

        parent::__construct($msg, 0, $previous);
    }
}
