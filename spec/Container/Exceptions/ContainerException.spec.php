<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Ellipse\Container\Exceptions\ContainerException;
use Ellipse\Container\Exceptions\NotFoundException;

describe('ContainerException', function () {

    beforeEach(function () {

        $this->previous = mock([Throwable::class, ContainerExceptionInterface::class])->get();

        $this->exception = new ContainerException('id', $this->previous);

    });

    it('should implement ContainerExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

    describe('->getPrevious()', function () {

        it('should return the previous exception', function () {

            $test = $this->exception->getPrevious();

            expect($test)->toBe($this->previous);

        });

    });

});
