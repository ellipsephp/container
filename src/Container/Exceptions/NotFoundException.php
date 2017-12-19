<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
    public function __construct($id)
    {
        $template = "Identifier '%s' is not registered in the container";

        $msg = sprintf($template, $id);

        parent::__construct($msg);
    }
}
