<?php

declare(strict_types=1);

namespace WebFu\Dot;

use WebFu\Wrapper\Wrapper;

final class Dot
{
    private Wrapper $wrapper;

    /**
     * @param mixed[]|object $element
     * @param non-empty-string $separator
     */
    public function __construct(private array|object $element, private string $separator = '.')
    {
        $this->wrapper = new Wrapper($this->element);
    }

    public function get(string $path): mixed
    {
        $this->validatePath($path);

        $pathTracks = explode($this->separator, $path);
        $track = array_shift($pathTracks);

        if (!$this->wrapper->has($track)) {
            throw new PathNotFoundException($track . ' path not found');
        }

        $value = match (gettype($this->element)) {
            'array' => $this->element[$track],
            'object' => $this->element->{$track}
        };

        if (!count($pathTracks)) {
            return $value;
        }

        $next = new self($value);

        return $next->get(implode($this->separator, $pathTracks));
    }

    public function validatePath(string $path): void
    {
        $separatorEscaped = preg_quote($this->separator);

        preg_match('/(([a-zA-Z_][a-zA-Z_0-9]*(\(\))?)|([-+]?\d+))('.$separatorEscaped.'(([a-zA-Z_][a-zA-Z_0-9]*(\(\))?)|([-+]?\d+)))*/', $path, $matches);
        if (!count($matches) || $matches[0] !== $path) {
            throw new InvalidPathException($path . ' is not a valid path');
        }
    }
}
