<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

class Wrapper
{
    /** @var array|int[]|string[] */
    private array $keys;

    /**
     * @param mixed[]|object $element
     */
    public function __construct(private array|object $element)
    {
        $type = gettype($this->element);

        assert($type === 'array' || $type === 'object');

        $this->keys = match ($type) {
            'array' => $this->getArrayKeys(),
            'object' => $this->getObjectKeys(),
        };
    }

    public function has(string|int $key): bool
    {
        return in_array($key, $this->keys);
    }

    /**
     * @return array|int[]|string[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @return array|int[]|string[]
     */
    private function getArrayKeys(): array
    {
        assert(is_array($this->element));
        return array_keys($this->element);
    }

    /**
     * @return string[]
     */
    private function getObjectKeys(): array
    {
        assert($this->element instanceof stdClass);
        $reflection = new ReflectionClass($this->element);
        $properties = array_map(fn (ReflectionProperty $reflectionProperty) => $reflectionProperty->getName(), $reflection->getProperties(ReflectionProperty::IS_PUBLIC));
        $methods = array_map(fn (ReflectionMethod $reflectionMethod) => $reflectionMethod->getName() . '()', $reflection->getMethods(ReflectionMethod::IS_PUBLIC));
        return $properties + $methods;
    }
}
