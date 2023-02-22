<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ClassWrapper implements WrapperInterface
{
    private array $keys = [];

    public function __construct(private object $element)
    {
        $reflection = new ReflectionClass($this->element);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $this->keys[$property->getName()] = $property;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $this->keys[$method->getName() . '()'] = $method;
        }
    }

    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->keys);
    }

    public function getKeys(): array
    {
        return array_keys($this->keys);
    }

    public function get(int|string $key): mixed
    {
        if (str_ends_with($key, '()')) {
            $method = $this->keys[$key];
            if ($method->getReturnType()->getName() === 'void') {
                throw new MissingReturnTypeException($key . ' has no return type');
            }

            $method = str_replace('()', '', $key);

            return $this->element->{$method}();
        }

        return $this->element->{$key};
    }

    public function set(int|string $key, mixed $value): WrapperInterface
    {
        if (str_ends_with($key, '()')) {
            throw new UnsupportedOperationException('Cannot set a class method');
        }

        $this->element->{$key} = $value;

        return $this;
    }
}
