<?php

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Container;
use Interop\Container\ServiceProviderInterface;

interface ContainerCallable
{
    public function __invoke();
}

describe('Container', function () {

    afterEach(function () {

        Mockery::close();

    });

    it('should implement ContainerInterface', function () {

        $container = new Container();

        expect($container)->to->be->an->instanceof(ContainerInterface::class);

    });

    describe('->has()', function () {

        beforeEach(function () {

            $provider = Mockery::mock(ServiceProviderInterface::class);

            $provider->shouldReceive('getFactories')->once()
                ->andReturn(['id' => function () {}]);

            $provider->shouldReceive('getExtensions')->once()
                ->andReturn([]);

            $this->container = new Container([$provider]);

        });

        it('should return true when the id is defined', function () {

            $test = $this->container->has('id');

            expect($test)->to->be->true();

        });

        it('should return false when the id is defined', function () {

            $test = $this->container->has('notfound');

            expect($test)->to->be->false();

        });

    });

    describe('->get()', function () {

        it('should run the factory associated with the given id', function () {

            $provider = Mockery::mock(ServiceProviderInterface::class);
            $factory = Mockery::mock(ContainerCallable::class);

            $provider->shouldReceive('getFactories')->once()
                ->andReturn(['id' => $factory]);

            $provider->shouldReceive('getExtensions')->once()
                ->andReturn([]);

            $container = new Container([$provider]);

            $factory->shouldReceive('__invoke')->once()
                ->with($container)
                ->andReturn('service');

            $test = $container->get('id');

            expect($test)->to->be->equal('service');

        });

        it('should run the last factory associated with the given id', function () {

            $provider1 = Mockery::mock(ServiceProviderInterface::class);
            $provider2 = Mockery::mock(ServiceProviderInterface::class);
            $factory1 = Mockery::mock(ContainerCallable::class);
            $factory2 = Mockery::mock(ContainerCallable::class);

            $provider1->shouldReceive('getFactories')->once()
                ->andReturn(['id' => $factory1]);

            $provider1->shouldReceive('getExtensions')->once()
                ->andReturn([]);

            $provider2->shouldReceive('getFactories')->once()
                ->andReturn(['id' => $factory2]);

            $provider2->shouldReceive('getExtensions')->once()
                ->andReturn([]);

            $container = new Container([$provider1, $provider2]);

            $factory1->shouldNotReceive('__invoke');

            $factory2->shouldReceive('__invoke')->once()
                ->with($container)
                ->andReturn('service');

            $test = $container->get('id');

            expect($test)->to->be->equal('service');

        });

        it('should run the extensions associated with the given id with null as parameter when no factory is defined', function () {

            $provider1 = Mockery::mock(ServiceProviderInterface::class);
            $provider2 = Mockery::mock(ServiceProviderInterface::class);
            $extension1 = Mockery::mock(ContainerCallable::class);
            $extension2 = Mockery::mock(ContainerCallable::class);

            $provider1->shouldReceive('getFactories')->once()
                ->andReturn([]);

            $provider1->shouldReceive('getExtensions')->once()
                ->andReturn(['id' => $extension1]);

            $provider2->shouldReceive('getFactories')->once()
                ->andReturn([]);

            $provider2->shouldReceive('getExtensions')->once()
                ->andReturn(['id' => $extension2]);

            $container = new Container([$provider1, $provider2]);

            $extension1->shouldReceive('__invoke')->once()
                ->with($container, null)
                ->andReturn('service1');

            $extension2->shouldReceive('__invoke')->once()
                ->with($container, 'service1')
                ->andReturn('service2');

            $test = $container->get('id');

            expect($test)->to->be->equal('service2');

        });

        it('should run the extensions associated with the given id with the service built by the factory as parameter', function () {

            $provider1 = Mockery::mock(ServiceProviderInterface::class);
            $provider2 = Mockery::mock(ServiceProviderInterface::class);
            $provider3 = Mockery::mock(ServiceProviderInterface::class);
            $factory = Mockery::mock(ContainerCallable::class);
            $extension1 = Mockery::mock(ContainerCallable::class);
            $extension2 = Mockery::mock(ContainerCallable::class);

            $provider1->shouldReceive('getFactories')->once()
                ->andReturn(['id' => $factory]);

            $provider1->shouldReceive('getExtensions')->once()
                ->andReturn([]);

            $provider2->shouldReceive('getFactories')->once()
                ->andReturn([]);

            $provider2->shouldReceive('getExtensions')->once()
                ->andReturn(['id' => $extension1]);

            $provider3->shouldReceive('getFactories')->once()
                ->andReturn([]);

            $provider3->shouldReceive('getExtensions')->once()
                ->andReturn(['id' => $extension2]);

            $container = new Container([$provider1, $provider2, $provider3]);

            $factory->shouldReceive('__invoke')->once()
                ->with($container)
                ->andReturn('service1');

            $extension1->shouldReceive('__invoke')->once()
                ->with($container, 'service1')
                ->andReturn('service2');

            $extension2->shouldReceive('__invoke')->once()
                ->with($container, 'service2')
                ->andReturn('service3');

            $test = $container->get('id');

            expect($test)->to->be->equal('service3');

        });

        it('should not care about the order of the service providers', function () {

            $provider1 = Mockery::mock(ServiceProviderInterface::class);
            $provider2 = Mockery::mock(ServiceProviderInterface::class);
            $provider3 = Mockery::mock(ServiceProviderInterface::class);
            $factory = Mockery::mock(ContainerCallable::class);
            $extension1 = Mockery::mock(ContainerCallable::class);
            $extension2 = Mockery::mock(ContainerCallable::class);

            $provider1->shouldReceive('getFactories')->once()
                ->andReturn(['id' => $factory]);

            $provider1->shouldReceive('getExtensions')->once()
                ->andReturn([]);

            $provider2->shouldReceive('getFactories')->once()
                ->andReturn([]);

            $provider2->shouldReceive('getExtensions')->once()
                ->andReturn(['id' => $extension1]);

            $provider3->shouldReceive('getFactories')->once()
                ->andReturn([]);

            $provider3->shouldReceive('getExtensions')->once()
                ->andReturn(['id' => $extension2]);

            $container = new Container([$provider2, $provider1, $provider3]);

            $factory->shouldReceive('__invoke')->once()
                ->with($container)
                ->andReturn('service1');

            $extension1->shouldReceive('__invoke')->once()
                ->with($container, 'service1')
                ->andReturn('service2');

            $extension2->shouldReceive('__invoke')->once()
                ->with($container, 'service2')
                ->andReturn('service3');

            $test = $container->get('id');

            expect($test)->to->be->equal('service3');

        });

        it('should fail when the id is not defined', function () {

            $container = new Container([
                new class () {
                    public function getFactories() { return []; }
                    public function getExtensions() { return []; }
                },
            ]);

            expect([$container, 'get'])->with('id')->to->throw(NotFoundExceptionInterface::class);

        });

    });

});
