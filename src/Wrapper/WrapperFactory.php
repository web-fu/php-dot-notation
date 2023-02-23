<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

class WrapperFactory
{
    /**
     * @param mixed[]|object $element
     */
    public static function create(array|object &$element): WrapperInterface
    {
        if (is_array($element)) {
            return new ArrayWrapper($element);
        }
        return new ClassWrapper($element);
    }
}
