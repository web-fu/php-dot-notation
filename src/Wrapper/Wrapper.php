<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

class Wrapper
{
    /**
     * @param mixed[]|object $element
     */
    public function __construct(private array|object &$element)
    {
    }

    public function has(string|int $key): bool
    {
        return in_array($key, $this->getKeys());
    }

    /**
     * @return array|int[]|string[]
     */
    public function getKeys(): array
    {
        $type = gettype($this->element);

        assert($type === 'array' || $type === 'object');

        return match ($type) {
            'array' => $this->getArrayKeys(),
            'object' => $this->getObjectKeys(),
        };
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
        assert(is_object($this->element));

        $reflection = new ReflectionClass($this->element);
        $properties = array_map(fn (ReflectionProperty $reflectionProperty) => $reflectionProperty->getName(), $reflection->getProperties(ReflectionProperty::IS_PUBLIC));
        $methods = array_map(fn (ReflectionMethod $reflectionMethod) => $reflectionMethod->getName() . '()', $reflection->getMethods(ReflectionMethod::IS_PUBLIC));

        return $properties + $methods;
    }
}
