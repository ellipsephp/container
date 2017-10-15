<?php

use Psr\Container\NotFoundExceptionInterface;

use Ellipse\Container\Exceptions\NotFoundException;

describe('NotFoundException', function () {

    it('should implement NotFoundExceptionInterface', function () {

        $test = new NotFoundException('id');

        expect($test)->toBeAnInstanceOf(NotFoundExceptionInterface::class);

    });

});
