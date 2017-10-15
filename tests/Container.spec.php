<?php

use function Eloquent\Phony\mock;

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
            $this->factory1 = mock(['__invoke' => function () {}]);
            $this->factory2 = mock(['__invoke' => function () {}]);
            $this->extension1 = mock(['__invoke' => function () {}]);
            $this->extension2 = mock(['__invoke' => function () {}]);
            $this->extension3 = mock(['__invoke' => function () {}]);

            $this->provider1->getFactories->returns([]);
            $this->provider1->getExtensions->returns([]);
            $this->provider2->getFactories->returns([]);
            $this->provider2->getExtensions->returns([]);
            $this->provider3->getFactories->returns([]);
            $this->provider3->getExtensions->returns([]);

        });

        it('should run the factory associated with the given id', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1->get()]);

            $container = new Container([$this->provider1->get()]);

            $this->factory1->__invoke->with($container)->returns('service');

            $test = $container->get('id');

            expect($test)->toEqual('service');
            $this->factory1->__invoke->called();

        });

        it('should run the last factory associated with the given id', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1->get()]);
            $this->provider2->getFactories->returns(['id' => $this->factory2->get()]);

            $container = new Container([
                $this->provider1->get(),
                $this->provider2->get(),
            ]);

            $this->factory2->__invoke->with($container)->returns('service');

            $test = $container->get('id');

            expect($test)->toEqual('service');
            $this->factory2->__invoke->called();

        });

        it('should run the extensions associated with the given id with null as parameter when no factory is defined', function () {

            $this->provider1->getExtensions->returns(['id' => $this->extension1->get()]);
            $this->provider2->getExtensions->returns(['id' => $this->extension2->get()]);

            $container = new Container([
                $this->provider1->get(),
                $this->provider2->get(),
            ]);

            $this->extension1->__invoke->with($container, null)->returns('service1');
            $this->extension2->__invoke->with($container, 'service1')->returns('service2');

            $test = $container->get('id');

            expect($test)->toEqual('service2');
            $this->extension1->__invoke->called();
            $this->extension2->__invoke->called();

        });

        it('should run the extensions associated with the given id with the service built by the factory as parameter', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1->get()]);
            $this->provider1->getExtensions->returns(['id' => $this->extension1->get()]);
            $this->provider2->getExtensions->returns(['id' => $this->extension2->get()]);
            $this->provider3->getExtensions->returns(['id' => $this->extension3->get()]);

            $container = new Container([
                $this->provider1->get(),
                $this->provider2->get(),
                $this->provider3->get(),
            ]);

            $this->factory1->__invoke->with($container)->returns('service1');
            $this->extension1->__invoke->with($container, 'service1')->returns('service2');
            $this->extension2->__invoke->with($container, 'service2')->returns('service3');
            $this->extension3->__invoke->with($container, 'service3')->returns('service4');

            $test = $container->get('id');

            expect($test)->toEqual('service4');
            $this->factory1->__invoke->called();
            $this->extension1->__invoke->called();
            $this->extension2->__invoke->called();
            $this->extension3->__invoke->called();

        });

        it('should not care about the order of the service providers', function () {

            $this->provider1->getFactories->returns(['id' => $this->factory1->get()]);
            $this->provider1->getExtensions->returns(['id' => $this->extension1->get()]);
            $this->provider2->getExtensions->returns(['id' => $this->extension2->get()]);
            $this->provider3->getExtensions->returns(['id' => $this->extension3->get()]);

            $container = new Container([
                $this->provider2->get(),
                $this->provider1->get(),
                $this->provider3->get(),
            ]);

            $this->factory1->__invoke->with($container)->returns('service1');
            $this->extension2->__invoke->with($container, 'service1')->returns('service2');
            $this->extension1->__invoke->with($container, 'service2')->returns('service3');
            $this->extension3->__invoke->with($container, 'service3')->returns('service4');

            $test = $container->get('id');

            expect($test)->toEqual('service4');
            $this->factory1->__invoke->called();
            $this->extension1->__invoke->called();
            $this->extension2->__invoke->called();
            $this->extension3->__invoke->called();

        });

        it('should fail when the id is not defined', function () {

            $container = new Container([$this->provider1->get()]);

            $test = function () use ($container) { $container->get('id'); };

            expect($test)->toThrow(new NotFoundException('id'));

        });

    });

});
