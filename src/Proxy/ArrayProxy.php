<?php

declare(strict_types=1);

namespace WebFu\Proxy;

class ArrayProxy implements ProxyInterface
{
    /**
     * @param mixed[] $element
     */
    public function __construct(private array &$element)
    {
    }

    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->element);
    }

    /**
     * @return array<int|string>
     */
    public function getKeys(): array
    {
        return array_keys($this->element);
    }

    public function get(int|string $key): mixed
    {
        return $this->element[$key];
    }

    public function set(int|string $key, mixed $value): self
    {
        $this->element[$key] = $value;

        return $this;
    }
}
