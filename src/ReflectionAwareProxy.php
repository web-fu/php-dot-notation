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

namespace WebFu\DotNotation;

use WebFu\Proxy\Exception\KeyNotFoundException;
use WebFu\Proxy\Proxy;
use WebFu\Reflection\ReflectionClass;
use WebFu\Reflection\ReflectionType;

class ReflectionAwareProxy extends Proxy
{
    public function getReflectionType(int|string $key): ReflectionType|null
    {
        if (!$this->has($key)) {
            throw new KeyNotFoundException($key);
        }

        if (is_array($this->element)) {
            $type = gettype($this->element[$key]);

            return new ReflectionType([$type]);
        }

        $reflection = new ReflectionClass($this->element);

        if ($reflection->hasProperty($key)) {
            return $reflection->getProperty($key)->getType();
        }

        $method = substr($key, 0, -2);

        if ($reflection->hasMethod($method)) {
            return $reflection->getMethod($method)->getReturnType();
        }

        return null;
    }
}
