<?php

use function Eloquent\Phony\stub;
use function Eloquent\Phony\mock;

use Psr\Container\ContainerInterface;

use Ellipse\Container\CachedServiceFactory;

describe('CachedServiceFactory', function () {

    beforeEach(function () {

        $this->delegate = stub();

        $this->factory = new CachedServiceFactory($this->delegate);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->container = mock(ContainerInterface::class)->get();

            $this->service1 = new class {};
            $this->service2 = new class {};

            $this->delegate->with($this->container)->returns($this->service1, $this->service2);

        });

        context('when called for the first time', function () {

            it('should proxy the delegate', function () {

                $test = ($this->factory)($this->container);

                expect($test)->toBe($this->service1);

            });

        });

        context('when called multiple times', function () {

            it('should return the same value', function () {

                $test1 = ($this->factory)($this->container);
                $test2 = ($this->factory)($this->container);

                expect($test1)->toBe($test2);

            });

        });

    });

});
