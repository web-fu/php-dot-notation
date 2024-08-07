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

use WebFu\Reflection\ReflectionType;

interface ProxyInterface
{
    public function has(int|string $key): bool;

    /**
     * @return array<int|string>
     */
    public function getKeys(): array;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): self;

    public function getReflectionType(string $key): ReflectionType|null;
}
