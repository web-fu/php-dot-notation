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
            throw new PathNotFoundException($track.' path not found');
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

    /**
     * @deprecated to be removed in the next major version
     */
    public static function isValidPath(string $path, string $separator = '.'): bool
    {
        if ('' === $path) {
            return true;
        }

        $separatorEscaped = preg_quote($separator);

        preg_match('/(([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\(\))?)|([-+]?\d+))('.$separatorEscaped.'(([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\(\))?)|([-+]?\d+)))*/', $path, $matches);

        return count($matches) && $matches[0] === $path;
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
}
