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

use Closure;
use stdClass;
use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\Reflection\ReflectionClass;
use WebFu\Reflection\ReflectionMethod;
use WebFu\Reflection\ReflectionProperty;
use WebFu\Reflection\ReflectionType;

class ClassProxy implements ProxyInterface
{
    public function __construct(private object &$element)
    {
    }

    public function has(int|string $key): bool
    {
        return in_array($key, $this->getKeys(), true);
    }

    /**
     * @return array<int|string>
     */
    public function getKeys(): array
    {
        $keys = [];

        $reflection = new ReflectionClass($this->element);

        foreach ($reflection->getProperties() as $property) {
            $keys[$property->getName()] = 0;
        }

        foreach ($reflection->getMethods() as $method) {
            $keys[$method->getName().'()'] = 0;
        }

        $parent = $reflection->getParentClass();
        while ($parent instanceof ReflectionClass) {
            foreach ($parent->getProperties() as $property) {
                $keys[$property->getName()] = 0;
            }

            foreach ($parent->getMethods() as $method) {
                $keys[$method->getName().'()'] = 0;
            }

            $parent = $parent->getParentClass();
        }

        return \array_keys($keys);
    }

    public function get(int|string $key): mixed
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        $key = (string) $key;

        if (str_ends_with($key, '()')) {
            $method = str_replace('()', '', $key);

            return Closure::bind(
                static function (object $element) use ($method) {
                    return $element->{$method}();
                },
                null,
                $this->element,
            )($this->element);
        }

        if ($this->element instanceof stdClass) {
            return $this->element->{$key};
        }

        return Closure::bind(
            static function (object $element) use ($key) {
                return $element->{$key};
            },
            null,
            $this->element,
        )($this->element);
    }

    public function set(int|string $key, mixed $value): ProxyInterface
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        $key = (string) $key;

        if (str_ends_with($key, '()')) {
            throw new UnsupportedOperationException('Cannot set a class method');
        }

        $this->element->{$key} = $value;

        return $this;
    }

    public function isInitialised(string|int $key): bool
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        $key = (string) $key;

        $reflection = new ReflectionClass($this->element);

        if (str_ends_with($key, '()')) {
            $method = str_replace('()', '', $key);

            return $reflection->hasMethod($method);
        }

        /** @var ReflectionProperty $property */
        $property = $reflection->getProperty($key);

        return $property->isInitialized($this->element);
    }

    /**
     * {@inheritDoc}
     */
    public function init(int|string $key, string|null $type = null): ProxyInterface
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        $key = (string) $key;

        if (str_ends_with($key, '()')) {
            throw new UnsupportedOperationException('Cannot init a class method');
        }

        if ($this->isInitialised($key)) {
            return $this;
        }

        /** @var ReflectionType $reflectionType */
        $reflectionType = $this->getReflectionType($key);

        if (
            count($reflectionType->getTypeNames()) > 1
            && null === $type
        ) {
            throw new UnsupportedOperationException('In case of union type you must specify the type');
        }

        $type ??= $reflectionType->getTypeNames()[0];

        $this->element->{$key} = ValueInitializer::init($type);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function create(int|string $key, string|null $type = null): ProxyInterface
    {
        if ($this->has($key)) {
            return $this;
        }

        $reflection = new ReflectionClass($this->element);

        $checkStdClass  = 'stdClass' === $reflection->getName();
        $checkAttribute = [] !== $reflection->getAttributes('AllowDynamicProperties');
        $checkMethod    = $reflection->hasMethod('__set');

        if (
            !$checkStdClass
            && !$checkAttribute
            && !$checkMethod
        ) {
            throw new UnsupportedOperationException('Cannot create a new property');
        }

        $key = (string) $key;

        $this->element->{$key} = ValueInitializer::init($type);

        return $this;
    }

    public function unset(int|string $key): ProxyInterface
    {
        if (!$this->has($key)) {
            return $this;
        }

        $key = (string) $key;

        if (str_ends_with($key, '()')) {
            throw new UnsupportedOperationException('Cannot unset a class method');
        }

        unset($this->element->{$key});

        return $this;
    }

    public function getReflectionType(int|string $key): ReflectionType|null
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
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
