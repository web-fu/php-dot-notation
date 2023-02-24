<?php

declare(strict_types=1);

namespace WebFu\Proxy;

interface ProxyInterface
{
    public function has(int|string $key): bool;
    /** @return string[] */
    public function getKeys(): array;
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): self;
}
