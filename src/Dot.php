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
use WebFu\DotNotation\Exception\PathNotInitialisedException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\Proxy\Proxy;

class Dot
{
    private Proxy $proxy;

    /**
     * @param mixed[]|object   $element
     * @param non-empty-string $separator
     */
    public function __construct(array|object &$element, private string $separator = '.')
    {
        $this->proxy = new Proxy($element);
    }

    /**
     * Return a value from a path. It fails if the path does not exist.
     *
     * @param string $path
     *
     * @throws PathNotFoundException
     *
     * @return mixed
     */
    public function get(string $path): mixed
    {
        if (!$this->has($path)) {
            throw new PathNotFoundException($path);
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

    /**
     * Set a value in a path. It fails if the path does not exist.
     *
     * @param string $path
     * @param mixed  $value
     *
     * @throws UnsupportedOperationException
     * @throws PathNotInitialisedException
     *
     * @return $this
     */
    public function set(string $path, mixed $value): self
    {
        $pathTracks = explode($this->separator, $path);

        $last = end($pathTracks);

        if (str_ends_with($last, '()')) {
            throw new UnsupportedOperationException('Cannot set a class method');
        }

        if (1 === count($pathTracks)) {
            $this->proxy->set($pathTracks[0], $value);

            return $this;
        }

        $track = array_shift($pathTracks);
        if (!$this->proxy->isInitialised($track)) {
            throw new PathNotInitialisedException($track);
        }

        $newElement = $this->proxy->get($track);

        assert(is_array($newElement) || is_object($newElement));

        $next = new self($newElement);

        $newPath = implode($this->separator, $pathTracks);

        $next->set($newPath, $value);
        $this->proxy->set($track, $newElement);

        return $this;
    }

    /**
     * Check if a path exists.
     *
     * @param string $path
     *
     * @return bool
     */
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

    /**
     * Check if a path is initialised. It fails if the path does not exist.
     *
     * @param string $path
     *
     * @throws PathNotFoundException
     *
     * @return bool
     */
    public function isInitialised(string $path): bool
    {
        if (!$this->has($path)) {
            throw new PathNotFoundException($path);
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

    /**
     * Create a path and set a value.
     *
     * @param string $path
     * @param mixed  $value
     *
     * @throws UnsupportedOperationException
     *
     * @return $this
     */
    public function create(string $path, mixed $value): self
    {
        $pathTracks = explode($this->separator, $path);

        $track = array_shift($pathTracks);

        if (!count($pathTracks)) {
            $this->proxy->create($track, $value);

            return $this;
        }

        if (!$this->proxy->has($track)) {
            $this->proxy->create($track, []);
        }

        $nextElement = $this->proxy->get($track);

        $nextDot = new self($nextElement);

        $nextPath = implode($this->separator, $pathTracks);
        $nextDot->create($nextPath, $value);

        $this->proxy->set($track, $nextElement);

        return $this;
    }

    /**
     * Unset a path.
     *
     * @param string $path
     *
     * @return $this
     */
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

    /**
     * Get a Dot proxy for a path. It fails if the path does not exist.
     *
     * @param string $path
     *
     * @throws InvalidPathException|PathNotFoundException
     *
     * @return Dot
     */
    public function dot(string $path): self
    {
        $result = $this->get($path);

        if (is_array($result) || is_object($result)) {
            return new self($result);
        }

        throw new InvalidPathException($path);
    }

    /**
     * Return list of all paths.
     *
     * @return array<string>
     */
    public function getPaths(): array
    {
        $paths = [];
        $keys  = $this->proxy->getKeys();

        foreach ($keys as $key) {
            if (!$this->isInitialised((string) $key)) {
                continue;
            }
            $value = $this->proxy->get($key);

            if (is_array($value) || is_object($value)) {
                $next      = new self($value);
                $nextPaths = $next->getPaths();
                foreach ($nextPaths as $nextPath) {
                    $paths[] = $key.$this->separator.$nextPath;
                }
            } else {
                $paths[] = (string) $key;
            }

            unset($value);
        }

        return $paths;
    }
}
