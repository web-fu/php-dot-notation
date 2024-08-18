<?php

declare(strict_types=1);

/**
 * This file is part of web-fu/php-dot-notation
 *
 * @copyright Web-Fu <info@web-fu.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WebFu\DotNotation\Proxy;

use WebFu\DotNotation\Exception\UnsupportedOperationException;

class ValueInitializer
{
    public static function init(string|null $type = null): mixed
    {
        if (class_exists($type ?? '')) {
            return new $type();
        }

        return match ($type) {
            null    => null,
            'array' => [],
            default => throw new UnsupportedOperationException('Cannot init type `'.$type.'`'),
        };
    }
}
