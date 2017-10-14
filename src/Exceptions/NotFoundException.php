<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct($id)
    {
        parent::__construct(sprintf('Identifier "%s" is not defined.', $id));
    }
}
