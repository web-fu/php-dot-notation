<?php

declare(strict_types=1);

namespace WebFu\Proxy;

class Proxy
{
    /**
     * @template T of object|array<array-key, mixed>
     *
     * @param T $element
     *
     * @param-out T $element
     */
    public function __construct(array|object &$element)
    {
    }
}
