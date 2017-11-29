<?php

use function Eloquent\Phony\stub;
use function Eloquent\Phony\mock;

use Psr\Container\ContainerInterface;

use Ellipse\Container\ServiceFactory;

describe('ServiceFactory', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class)->get();

        $this->callable = stub();

    });

    describe('->__invoke()', function () {

        context('when no previous service factory is defined', function () {

            it('should proxy the callable factory without a second parameter', function () {

                $instance = mock(StdClass::class)->get();

                $this->callable->with($this->container)->returns($instance);

                $factory = new ServiceFactory($this->callable);

                $test = $factory($this->container);

                expect($test)->toEqual($instance);

            });

        });

        context('when a previous service factory is defined', function () {

            it('should proxy the callable factory with the result of the previous service factory as second parameter', function () {

                $instance1 = mock(StdClass::class)->get();
                $instance2 = mock(StdClass::class)->get();

                $previous = mock(ServiceFactory::class);

                $previous->__invoke->with($this->container)->returns($instance1);

                $this->callable->with($this->container, $instance1)->returns($instance2);

                $this->factory = new ServiceFactory($this->callable, $previous->get());

                $test = $this->factory($this->container);

                expect($test)->toEqual($instance2);

            });

        });

        context('when called multiple times', function () {

            it('should return the same value', function () {

                $callable = function () { return mock(StdClass::class)->get(); };

                $factory = new ServiceFactory($callable);

                $test1 = $factory($this->container);
                $test2 = $factory($this->container);

                expect($test1)->toBe($test2);

            });

        });

    });

});
