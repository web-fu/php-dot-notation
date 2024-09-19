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

namespace WebFu\DotNotation;

use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\DotNotation\Proxy\ProxyFactory;
use WebFu\DotNotation\Proxy\ProxyInterface;
use WebFu\Reflection\ReflectionType;

final class Dot
{
    private ProxyInterface $proxy;

    /**
     * @param mixed[]|object   $element
     * @param non-empty-string $separator
     */
    public function __construct(array|object &$element, private string $separator = '.')
    {
        $this->proxy = ProxyFactory::create($element);
    }

    public function get(string $path): mixed
    {
        if (!$this->has($path)) {
            throw new PathNotFoundException('Path `'.$path.'` not found');
        }

        $pathTracks = explode($this->separator, $path);
        $track      = array_shift($pathTracks);

        $value = $this->proxy->get($track);

        if (!count($pathTracks)) {
            return $value;
        }

        assert(is_array($value) || is_object($value));

        $next = new self($value);

        return $next->get(implode($this->separator, $pathTracks));
    }

    public function set(string $path, mixed $value): self
    {
        $pathTracks = explode($this->separator, $path);

        if (1 === count($pathTracks)) {
            $this->proxy->set($pathTracks[0], $value);

            return $this;
        }

        $track = array_shift($pathTracks);

        if (!$this->proxy->isInitialised($track)) {
            $this->proxy->init($track);
        }

        $newElement = $this->proxy->get($track);

        assert(is_array($newElement) || is_object($newElement));

        $next = new self($newElement);

        $newPath = implode($this->separator, $pathTracks);

        return $next->set($newPath, $value);
    }

    public function has(string $path): bool
    {
        $pathTracks = explode($this->separator, $path);
        $track      = array_shift($pathTracks);

        if (!$this->proxy->has($track)) {
            return false;
        }

        if (!count($pathTracks)) {
            return true;
        }

        $value = $this->proxy->get($track);

        if (
            !is_array($value)
            && !is_object($value)
        ) {
            return false;
        }

        $next = new self($value);

        return $next->has(implode($this->separator, $pathTracks));
    }

    public function isInitialised(string $path): bool
    {
        if (!$this->has($path)) {
            throw new PathNotFoundException('Path `'.$path.'` not found');
        }

        $pathTracks = explode($this->separator, $path);
        $track      = array_shift($pathTracks);

        if (!$this->proxy->isInitialised($track)) {
            return false;
        }

        $value = $this->proxy->get($track);

        if (!count($pathTracks)) {
            return true;
        }

        $next = new self($value);

        return $next->isInitialised(implode($this->separator, $pathTracks));
    }

    public function init(string $path, string|null $type = null): self
    {
        $pathTracks = explode($this->separator, $path);

        if (1 === count($pathTracks)) {
            $this->proxy->init($pathTracks[0], $type);

            return $this;
        }

        $track = array_shift($pathTracks);

        if (!$this->proxy->isInitialised($track)) {
            $this->proxy->init($track);
        }

        $newElement = $this->proxy->get($track);

        assert(is_array($newElement) || is_object($newElement));

        $next = new self($newElement);

        $newPath = implode($this->separator, $pathTracks);

        return $next->init($newPath, $type);
    }

    public function unset(string $path): self
    {
        $pathTracks = explode($this->separator, $path);

        if (1 === count($pathTracks)) {
            $this->proxy->unset($pathTracks[0]);

            return $this;
        }

        $track = array_shift($pathTracks);

        if (!$this->proxy->isInitialised($track)) {
            return $this;
        }

        $newElement = $this->proxy->get($track);

        assert(is_array($newElement) || is_object($newElement));

        $next = new self($newElement);

        $newPath = implode($this->separator, $pathTracks);
        $next->unset($newPath);

        $this->set($track, $newElement);

        return $this;
    }

    public function getReflectionType(string $path): ReflectionType|null
    {
        $pathTracks = explode($this->separator, $path);
        $track      = array_pop($pathTracks);

        $source = $this->proxy;

        if (count($pathTracks)) {
            $element = $this->get(implode($this->separator, $pathTracks));
            if (
                !is_array($element)
                && !is_object($element)
            ) {
                $type = get_debug_type($element);
                throw new PathNotFoundException('Element of type '.$type.' has no child element');
            }
            $source = new self($element);
        }

        return $source->getReflectionType($track);
    }

    /**
     * @param mixed[]|object   $element
     * @param non-empty-string $separator
     *
     * @return mixed[]
     */
    public static function dotify(array|object $element, string $prefix = '', string $separator = '.'): array
    {
        $dot    = new self($element, $separator);
        $keys   = $dot->proxy->getKeys();
        $result = [];
        foreach ($keys as $key) {
            $value = $dot->get((string) $key);
            if (is_array($value) || is_object($value)) {
                $result = array_merge($result, self::dotify($value, $prefix.$key.$separator));
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param mixed[]          $dotified
     * @param non-empty-string $separator
     *
     * @return mixed[]
     */
    public static function undotify(array $dotified, string $separator = '.'): array
    {
        $result = [];
        foreach ($dotified as $path => $value) {
            // extract keys
            $keys = explode($separator, $path);
            // reverse keys for assignments
            $keys = array_reverse($keys);

            // set initial value
            $lastVal = $value;
            foreach ($keys as $key) {
                // wrap value with key over each iteration
                $lastVal = [
                    $key => $lastVal,
                ];
            }

            // merge result
            $result = array_merge_recursive($result, $lastVal);
        }

        return $result;
    }
}
