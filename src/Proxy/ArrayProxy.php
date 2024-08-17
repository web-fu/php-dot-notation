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

use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\Reflection\ReflectionType;

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
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        return $this->element[$key];
    }

    public function set(int|string $key, mixed $value): self
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        $this->element[$key] = $value;

        return $this;
    }

    public function isInitialised(int|string $key): bool
    {
        return isset($this->element[$key]);
    }

    public function init(int|string $key, string|null $type = null): self
    {
        if ($this->isInitialised($key)) {
            return $this;
        }

        $this->element[$key] = ValueInitializer::init($type);

        return $this;
    }

    public function unset(int|string $key): self
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        unset($this->element[$key]);

        return $this;
    }

    public function getReflectionType(int|string $key): ReflectionType|null
    {
        if (!$this->has($key)) {
            throw new PathNotFoundException('Key `'.$key.'` not found');
        }

        $value = $this->element[$key];
        $type  = get_debug_type($value);

        return new ReflectionType([$type]);
    }
}
