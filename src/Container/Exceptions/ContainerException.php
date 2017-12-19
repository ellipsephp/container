<?php declare(strict_types=1);

namespace Ellipse\Container\Exceptions;

use RuntimeException;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct($id, ContainerExceptionInterface $previous)
    {
        $template = "Failed to get '%s' from the container";

        $msg = sprintf($template, $id);

        parent::__construct($msg, 0, $previous);
    }
}
