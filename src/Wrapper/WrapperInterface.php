<?php

declare(strict_types=1);

namespace WebFu\Wrapper;

interface WrapperInterface
{
    public function has(string|int $key): bool;
    public function getKeys(): array;
    public function get(string|int $key): mixed;
    public function set(string|int $key, mixed $value): self;
}
