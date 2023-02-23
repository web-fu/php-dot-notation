<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

class ArrayWrapper implements WrapperInterface
{
    /**
     * @param mixed[] $element
     */
    public function __construct(private array &$element)
    {
    }

    public function has(string|int $key): bool
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

    public function get(string|int $key): mixed
    {
        return $this->element[$key];
    }

    public function set(string|int $key, mixed $value): self
    {
        $this->element[$key] = $value;

        return $this;
    }
}
