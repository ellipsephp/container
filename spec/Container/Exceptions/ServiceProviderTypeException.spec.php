<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerExceptionInterface;

use Interop\Container\ServiceProviderInterface;

use Ellipse\Container\Exceptions\ServiceProviderTypeException;

describe('ServiceProviderTypeException', function () {

    beforeEach(function () {

        $providers = [
            mock(ServiceProviderInterface::class)->get(),
            'provider',
            mock(ServiceProviderInterface::class)->get(),
            1,
        ];

        $this->previous = mock(TypeError::class)->get();

        $this->exception = new ServiceProviderTypeException($providers, $this->previous);

    });

    it('should implement ContainerExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(ContainerExceptionInterface::class);

    });

    describe('->getMessage()', function () {

        it('should contain the type of the first element of the providers array which is not an implementation of ServiceProviderInterface', function () {

            $test = $this->exception->getMessage();

            expect($test)->toContain('string');

        });

    });

    describe('->getPrevious()', function () {

        it('should return the previous exception', function () {

            $test = $this->exception->getPrevious();

            expect($test)->toBe($this->previous);

        });

    });

});
