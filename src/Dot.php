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
    private ProxyInterface $wrapper;

    /**
     * @param mixed[]|object   $element
     * @param non-empty-string $separator
     */
    public function __construct(private array|object $element, private string $separator = '.')
    {
        $this->wrapper = ProxyFactory::create($this->element);
    }

    public function get(string $path): mixed
    {
        if (!self::isValidPath($path, $this->separator)) {
            throw new InvalidPathException('Invalid path: '.$path);
        }

        $pathTracks = explode($this->separator, $path);
        $track      = array_shift($pathTracks);

        if (!$this->wrapper->has($track)) {
            throw new PathNotFoundException($track.' path not found');
        }

        $value = $this->wrapper->get($track);

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

    public static function isValidPath(string $path, string $separator = '.'): bool
    {
        $separatorEscaped = preg_quote($separator);

        preg_match('/(([a-zA-Z_][a-zA-Z_0-9]*(\(\))?)|([-+]?\d+))('.$separatorEscaped.'(([a-zA-Z_][a-zA-Z_0-9]*(\(\))?)|([-+]?\d+)))*/', $path, $matches);

        return count($matches) && $matches[0] === $path;
    }

    public static function dotify(array|object $element, string $separator = '.'): string
    {
        $dot = new self($element, $separator);
        $keys = $dot->wrapper->getKeys();
        $result = '';
        foreach ($keys as $key) {
            $value = $dot->wrapper->get($key);
            if (is_array($value) || is_object($value)) {
                $result .= $key.$separator.self::dotify($value, $separator);
            } else {
                $result .= $key.$separator.$value.PHP_EOL;
            }
        }
        return $result;
    }
}
