<?php

use function Eloquent\Phony\stub;
use function Eloquent\Phony\mock;

use Psr\Container\ContainerInterface;

use Ellipse\Container\Extension;

describe('Extension', function () {

    beforeEach(function () {

        $this->factory = stub();
        $this->previous = stub();

        $this->extension = new Extension($this->factory, $this->previous);

    });

    describe('->__invoke()', function () {

        beforeEach(function () {

            $this->container = mock(ContainerInterface::class)->get();

        });

        it('should get the previous value and proxy the service factory', function () {

            $instance1 = new class {};
            $instance2 = new class {};

            $this->previous->with($this->container)->returns($instance1);

            $this->factory->with($this->container, $instance1)->returns($instance2);

            $test = ($this->extension)($this->container);

            expect($test)->toEqual($instance2);

        });

    });

});
