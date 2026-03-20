<?php

namespace WebFu\DotNotation\Exception;

class PathUnionNotDefinedException extends \Exception
{
    public function __construct(string $path, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct(sprintf('Cannot create path `%s` because union is not defined', $path), $code, $previous);
    }
}