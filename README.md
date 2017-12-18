# Container

Minimal **[Psr-11 container](http://www.php-fig.org/psr/psr-11/meta/)** implementation handling **[service provider interop](https://github.com/container-interop/service-provider)**.

**Require** php >= 7.1

**Installation** `composer require ellipse/container`

**Run tests** `./vendor/bin/kahlan`

* [Using the container](#using-the-container)

## Using the container

The container takes an array of service providers on instantiation. This is the only way of registering service providers and service definitions.

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
