<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

interface WrapperInterface
{
    public function has(string|int $key): bool;
    /** @return string[] */
    public function getKeys(): array;
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): self;
}
