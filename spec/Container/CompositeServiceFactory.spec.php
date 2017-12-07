<?php

use function Eloquent\Phony\stub;
use function Eloquent\Phony\mock;

use Psr\Container\ContainerInterface;

use Ellipse\Container\CompositeServiceFactory;

describe('CompositeServiceFactory', function () {

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->container = mock(ContainerInterface::class)->get();

        });

        context('when there is only one service factory', function () {

            it('should proxy the service factory', function () {

                $delegate = stub();

                $factory = new CompositeServiceFactory([$delegate]);

                $delegate->with($this->container)->returns('service');

                $test = ($factory)($this->container);

                expect($test)->toEqual('service');

            });

        });

        context('when there is many service factories', function () {

            it('should proxy the last service factory with all the previous values as second parameters', function () {

                $delegate1 = stub();
                $delegate2 = stub();
                $delegate3 = stub();

                $factory = new CompositeServiceFactory([
                    $delegate1,
                    $delegate2,
                    $delegate3,
                ]);

                $delegate1->with($this->container)->returns('service1');
                $delegate2->with($this->container, 'service1')->returns('service2');
                $delegate3->with($this->container, 'service2')->returns('service3');

                $test = ($factory)($this->container);

                expect($test)->toEqual('service3');

            });

        });

    });

});
