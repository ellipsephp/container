<?php

use function Eloquent\Phony\stub;
use function Eloquent\Phony\mock;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container;
use Ellipse\Container\Exceptions\ContainerException;
use Ellipse\Container\Exceptions\NotFoundException;
use Ellipse\Container\Exceptions\ServiceProviderTypeException;

describe('Container', function () {

    it('should implement ContainerInterface', function () {

        $container = new Container();

        expect($container)->toBeAnInstanceOf(ContainerInterface::class);

    });

    context('when all elements of the given array are implementations of ServiceProviderInterface', function () {

        context('when the array of providers is not an associative array', function () {

            it('should not fail', function () {

                $provider1 = mock(ServiceProviderInterface::class);
                $provider2 = mock(ServiceProviderInterface::class);

                $provider1->getFactories->returns([]);
                $provider1->getExtensions->returns([]);
                $provider2->getFactories->returns([]);
                $provider2->getExtensions->returns([]);

                $providers = [$provider1->get(), $provider2->get()];

                $test = function () use ($providers) { new Container($providers); };

                expect($test)->not->toThrow();

            });

        });

        context('when the array of providers is an associative array', function () {

            it('should not fail', function () {

                $provider1 = mock(ServiceProviderInterface::class);
                $provider2 = mock(ServiceProviderInterface::class);

                $provider1->getFactories->returns([]);
                $provider1->getExtensions->returns([]);
                $provider2->getFactories->returns([]);
                $provider2->getExtensions->returns([]);

                $providers = ['provider1' => $provider1->get(), 'provider2' => $provider2->get()];

                $test = function () use ($providers) { new Container($providers); };

                expect($test)->not->toThrow();

            });

        });

    });

    context('when any element of the given array is not an implementation of ServiceProviderInterface', function () {

        it('should throw a ServiceProviderTypeException', function () {

            $provider1 = mock(ServiceProviderInterface::class);
            $provider2 = mock(ServiceProviderInterface::class);

            $provider1->getFactories->returns([]);
            $provider1->getExtensions->returns([]);
            $provider2->getFactories->returns([]);
            $provider2->getExtensions->returns([]);

            $providers = [$provider1->get(), 'provider', $provider2->get()];

            $test = function () use ($providers) { new Container($providers); };

            $exception = new ServiceProviderTypeException($providers, new TypeError());

            expect($test)->toThrow($exception);

        });

    });

    describe('->has()', function () {

        beforeEach(function () {

            $this->provider = mock(ServiceProviderInterface::class);

            $this->provider->getFactories->returns([]);
            $this->provider->getExtensions->returns([]);

        });

        it('should return true when the given id is defined', function () {

            $this->provider->getFactories->returns(['id' => stub()]);

            $container = new Container([$this->provider->get()]);

            $test = $container->has('id');

            expect($test)->toBe(true);

        });

        it('should return false when the given id is defined', function () {

            $container = new Container([$this->provider->get()]);

            $test = $container->has('id');

            expect($test)->toBe(false);

        });

    });

    describe('->get()', function () {

        beforeEach(function () {

            $this->provider1 = mock(ServiceProviderInterface::class);
            $this->provider2 = mock(ServiceProviderInterface::class);
            $this->provider3 = mock(ServiceProviderInterface::class);

            $this->provider1->getFactories->returns([]);
            $this->provider1->getExtensions->returns([]);
            $this->provider2->getFactories->returns([]);
            $this->provider2->getExtensions->returns([]);
            $this->provider3->getFactories->returns([]);
            $this->provider3->getExtensions->returns([]);

            $this->factory1 = stub();
            $this->factory2 = stub();
            $this->extension1 = stub();
            $this->extension2 = stub();
            $this->extension3 = stub();

        });

        context('when the given id is associated to at least one service factory', function () {

            context('when the given id is not associated to an extension', function () {

                it('should proxy the last service factory associated with the given id', function () {

                    $this->provider1->getFactories->returns(['id' => $this->factory1]);
                    $this->provider2->getFactories->returns(['id' => $this->factory2]);

                    $container = new Container([
                        $this->provider1->get(),
                        $this->provider2->get(),
                    ]);

                    $this->factory2->with($container)->returns('service');

                    $test = $container->get('id');

                    expect($test)->toEqual('service');

                });

            });

            context('when the given id is also associated to extensions', function () {

                it('should proxy the last extension with all the previous values as second parameters', function () {

                    $this->provider1->getFactories->returns(['id' => $this->factory1]);
                    $this->provider1->getExtensions->returns(['id' => $this->extension1]);
                    $this->provider2->getExtensions->returns(['id' => $this->extension2]);
                    $this->provider3->getExtensions->returns(['id' => $this->extension3]);

                    $container = new Container([
                        $this->provider1->get(),
                        $this->provider2->get(),
                        $this->provider3->get(),
                    ]);

                    $this->factory1->with($container)->returns('service1');
                    $this->extension1->with($container, 'service1')->returns('service2');
                    $this->extension2->with($container, 'service2')->returns('service3');
                    $this->extension3->with($container, 'service3')->returns('service4');

                    $test = $container->get('id');

                    expect($test)->toEqual('service4');

                });

                it('should not care about the order the service factories and extensions are defined', function () {

                    $this->provider1->getFactories->returns(['id' => $this->factory1]);
                    $this->provider1->getExtensions->returns(['id' => $this->extension1]);
                    $this->provider2->getFactories->returns(['id' => $this->factory2]);
                    $this->provider2->getExtensions->returns(['id' => $this->extension2]);
                    $this->provider3->getExtensions->returns(['id' => $this->extension3]);

                    $container = new Container([
                        $this->provider1->get(),
                        $this->provider2->get(),
                        $this->provider3->get(),
                    ]);

                    $this->factory2->with($container)->returns('service1');
                    $this->extension1->with($container, 'service1')->returns('service2');
                    $this->extension2->with($container, 'service2')->returns('service3');
                    $this->extension3->with($container, 'service3')->returns('service4');

                    $test = $container->get('id');

                    expect($test)->toEqual('service4');

                });

            });

        });

        context('when the given id is not associated to a service factory', function () {

            context('when the given id is not associated to an extension', function () {

                it('should throw a NotFoundException', function () {

                    $container = new Container;

                    $test = function () use ($container) { $container->get('id'); };

                    $exception = new NotFoundException('id');

                    expect($test)->toThrow($exception);

                });

            });

            context('when the given id is associated to at least one extension', function () {

                it('should proxy all the extensions with the defined default value as second parameter of the first extension', function () {

                    $this->extension1 = function ($container, string $default = 'default') {

                        return $default;

                    };

                    $this->provider1->getExtensions->returns(['id' => $this->extension1]);
                    $this->provider2->getExtensions->returns(['id' => $this->extension2]);

                    $container = new Container([
                        $this->provider1->get(),
                        $this->provider2->get(),
                    ]);

                    $this->extension2->with($container, 'default')->returns('service');

                    $test = $container->get('id');

                    expect($test)->toEqual('service');

                });

            });

        });

        context('when the ->get() method is called many times with the same id', function () {

            it('should return the same value (===)', function () {

                $instance1 = new class {};
                $instance2 = new class {};

                $this->provider1->getFactories->returns(['id' => $this->factory1]);

                $container = new Container([
                    $this->provider1->get(),
                    $this->provider2->get(),
                ]);

                $this->factory1->with($container)->returns($instance1, $instance2);

                $test1 = $container->get('id');
                $test2 = $container->get('id');

                expect($test1)->toBe($test2);

            });

        });

        context('when the factory throw an ContainerExceptionInterface', function () {

            it('should be wrapped inside a ContainerException', function () {

                $exception = mock([Throwable::class, ContainerExceptionInterface::class])->get();

                $this->provider1->getFactories->returns(['id' => $this->factory1]);

                $container = new Container([
                    $this->provider1->get(),
                ]);

                $this->factory1->with($container)->throws($exception);

                $test = function () use ($container) { $container->get('id'); };

                $exception = new ContainerException('id', $exception);

                expect($test)->toThrow($exception);

            });

        });

    });

});
