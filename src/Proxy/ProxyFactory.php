<?php

declare(strict_types=1);

namespace WebFu\Proxy;

class ProxyFactory
{
    /**
     * @param mixed[]|object $element
     */
    public static function create(array|object &$element): ProxyInterface
    {
        if (is_array($element)) {
            return new ArrayProxy($element);
        }
        return new ClassProxy($element);
    }
}
