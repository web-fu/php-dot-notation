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

use WebFu\DotNotation\Exception\InvalidPathException;
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
    public function __construct(private array|object $element, private string $separator = '.')
    {
        $this->proxy = ProxyFactory::create($this->element);
    }

    public function get(string $path): mixed
    {
        $pathTracks = explode($this->separator, $path);
        $track      = array_shift($pathTracks);

        if (!$this->proxy->has($track)) {
            throw new PathNotFoundException($path.' path not found');
        }

        $value = $this->proxy->get($track);

        if (!count($pathTracks)) {
            return $value;
        }

        if (
            !is_array($value)
            && !is_object($value)
        ) {
            $type = get_debug_type($value);
            throw new InvalidPathException('Element of type '.$type.' has no child element');
        }

        $next = new self($value);

        return $next->get(implode($this->separator, $pathTracks));
    }

    public function set(string $path, mixed $value): self
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
                throw new InvalidPathException('Element of type '.$type.' has no child element');
            }
            $source = new self($element);
        }

        $source->set($track, $value);

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
                throw new InvalidPathException('Element of type '.$type.' has no child element');
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
