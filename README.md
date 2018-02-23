# Container

Minimal **[Psr-11 container](http://www.php-fig.org/psr/psr-11/)** implementation handling **[service provider interop](https://github.com/container-interop/service-provider)**.

**Require** php >= 7.0

**Installation** `composer require ellipse/container`

**Run tests** `./vendor/bin/kahlan`

* [Getting started](#getting-started)

## Getting started

The `Ellipse\Container` class constructor takes an array of `Interop\Container\ServiceProviderInterface` implementations. This is the only way of registering service providers and service definitions into the container.

An `Ellipse\Container\Exceptions\ServiceProviderTypeException` is thrown when any element of the array passed to the `Container` class constructor is not an implementation of `ServiceProviderInterface`.

```php
<?php

namespace App;

use Interop\Container\ServiceProviderInterface;

class ServiceProviderA implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'id' => function ($container) {

                return 'abc';

            },
        ]
    }

    public function getExtensions()
    {
        return [
            //
        ]
    }
}
```

```php
<?php

namespace App;

use Interop\Container\ServiceProviderInterface;

class ServiceProviderB implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            //
        ]
    }

    public function getExtensions()
    {
        return [
            'id' => function ($container, string $previous) {

                return $previous . 'def';

            },
        ]
    }
}
```

```php
<?php

namespace App;

use Ellipse\Container;

// Get a container with a list of service providers.
$container = new Container([
    new ServiceProviderA,
    new ServiceProviderB,
]);

// Return true.
$container->has('id');

// Return 'abcdef'.
$container->get('id');
```
