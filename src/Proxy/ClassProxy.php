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

use WebFu\DotNotation\Exception\InvalidPathException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\Reflection\ReflectionClass;
use WebFu\Reflection\ReflectionMethod;
use WebFu\Reflection\ReflectionProperty;
use WebFu\Reflection\ReflectionType;

class ClassProxy implements ProxyInterface
{
    /**
     * @var array<ReflectionProperty|ReflectionMethod>
     */
    private array $keys = [];

    public function __construct(private object $element)
    {
        $reflection = new ReflectionClass($this->element);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $this->keys[$property->getName()] = $property;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $this->keys[$method->getName().'()'] = $method;
        }
    }

    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->keys);
    }

    /**
     * @return array<int|string>
     */
    public function getKeys(): array
    {
        return array_keys($this->keys);
    }

    public function get(int|string $key): mixed
    {
        if (!$this->has($key)) {
            throw new InvalidPathException('Key \''.$key.'\' not found');
        }

        $key = (string) $key;

        if (str_ends_with($key, '()')) {
            $method = str_replace('()', '', $key);

            return $this->element->{$method}();
        }

        return $this->element->{$key};
    }

    public function set(int|string $key, mixed $value): ProxyInterface
    {
        if (!$this->has($key)) {
            throw new InvalidPathException('Key \''.$key.'\' not found');
        }

        $key = (string) $key;

        if (str_ends_with($key, '()')) {
            throw new UnsupportedOperationException('Cannot set a class method');
        }

        $this->element->{$key} = $value;

        return $this;
    }

    public function getReflectionType(int|string $key): ReflectionType|null
    {
        if (!$this->has($key)) {
            throw new InvalidPathException('Key \''.$key.'\' not found');
        }

        $key = (string) $key;

        $reflection = new ReflectionClass($this->element);

        if (str_ends_with($key, '()')) {
            $method = str_replace('()', '', $key);

            $reflectionMethod = $reflection->getMethod($method);

            assert($reflectionMethod instanceof ReflectionMethod);

            return $reflectionMethod->getReturnType();
        }

        $reflectionProperty = $reflection->getProperty($key);

        assert($reflectionProperty instanceof ReflectionProperty);

        return $reflectionProperty->getType();
    }
}
