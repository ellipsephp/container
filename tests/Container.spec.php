<?php

use function Eloquent\Phony\mock;
use function Eloquent\Phony\stub;

use Psr\Container\ContainerInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container;
use Ellipse\Container\Exceptions\NotFoundException;

describe('Container', function () {

    it('should implement ContainerInterface', function () {

        $container = new Container();

        expect($container)->toBeAnInstanceOf(ContainerInterface::class);

    });

    describe('->has()', function () {

        beforeEach(function () {

            $this->provider = mock(ServiceProviderInterface::class);

            $this->provider->getFactories->returns([]);
            $this->provider->getExtensions->returns([]);

        });

        it('should return true when the id is defined', function () {

            $this->provider->getFactories->returns(['id' => function () {}]);

            $container = new Container([$this->provider->get()]);

            $test = $container->has('id');

            expect($test)->toBe(true);

        });

        it('should return false when the id is defined', function () {

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
            $this->factory1 = stub(function () {});
            $this->factory2 = stub(function () {});
            $this->extension1 = stub(function () {});
            $this->extension2 = stub(function () {});
            $this->extension3 = stub(function () {});

            $this->provider1->getFactories->returns([]);
            $this->provider1->getExtensions->returns([]);
            $this->provider2->getFactories->returns([]);
            $this->provider2->getExtensions->returns([]);
            $this->provider3->getFactories->returns([]);
            $this->provider3->getExtensions->returns([]);

        });

        it('should run the factory associated with the given id', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1]);

            $container = new Container([$this->provider1->get()]);

            $this->factory1->with($container)->returns('service');

            $test = $container->get('id');

            expect($test)->toEqual('service');
            $this->factory1->called();

        });

        it('should run the last factory associated with the given id', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1]);
            $this->provider2->getFactories->returns(['id' => $this->factory2]);

            $container = new Container([
                $this->provider1->get(),
                $this->provider2->get(),
            ]);

            $this->factory2->with($container)->returns('service');

            $test = $container->get('id');

            expect($test)->toEqual('service');
            $this->factory2->called();

        });

        it('should run the extensions associated with the given id with null as parameter when no factory is defined', function () {

            $this->provider1->getExtensions->returns(['id' => $this->extension1]);
            $this->provider2->getExtensions->returns(['id' => $this->extension2]);

            $container = new Container([
                $this->provider1->get(),
                $this->provider2->get(),
            ]);

            $this->extension1->with($container, null)->returns('service1');
            $this->extension2->with($container, 'service1')->returns('service2');

            $test = $container->get('id');

            expect($test)->toEqual('service2');
            $this->extension1->called();
            $this->extension2->called();

        });

        it('should run the extensions associated with the given id with the service built by the factory as parameter', function () {

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
            $this->factory1->called();
            $this->extension1->called();
            $this->extension2->called();
            $this->extension3->called();

        });

        it('should not care about the order of the service providers', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1]);
            $this->provider1->getExtensions->returns(['id' => $this->extension1]);
            $this->provider2->getExtensions->returns(['id' => $this->extension2]);
            $this->provider3->getExtensions->returns(['id' => $this->extension3]);

            $container = new Container([
                $this->provider2->get(),
                $this->provider1->get(),
                $this->provider3->get(),
            ]);

            $this->factory1->with($container)->returns('service1');
            $this->extension2->with($container, 'service1')->returns('service2');
            $this->extension1->with($container, 'service2')->returns('service3');
            $this->extension3->with($container, 'service3')->returns('service4');

            $test = $container->get('id');

            expect($test)->toEqual('service4');
            $this->factory1->called();
            $this->extension1->called();
            $this->extension2->called();
            $this->extension3->called();

        });

        it('should fail when the id is not defined', function () {

            $container = new Container([$this->provider1->get()]);

            $test = function () use ($container) { $container->get('id'); };

            expect($test)->toThrow(new NotFoundException('id'));

        });

    });

});
