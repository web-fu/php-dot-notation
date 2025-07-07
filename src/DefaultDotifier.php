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

use JsonSerializable;
use WebFu\DotNotation\Exception\NotDotifiableValueException;
use WebFu\DotNotation\Exception\NotUndotifiableValueException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\Reflection\ReflectionClass;

class DefaultDotifier implements DotifierInterface, UndotifierInterface
{
    /**
     * @var array<class-string, ReflectionClass>
     */
    private static array $reflectionClassCache = [];

    /**
     * {@inheritDoc}
     */
    public function dotify(mixed $data, string $separator = '.', array $context = []): array
    {
        if (!$this->supportsDotification($data, $context)) {
            throw new NotDotifiableValueException($data);
        }

        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        $dot   = new Dot($data, $separator);
        $paths = $dot->getPaths();

        $result = [];
        foreach ($paths as $path) {
            if (!$dot->isInitialised($path)) {
                continue;
            }
            $value         = $dot->get($path);
            $result[$path] = $value;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function undotify(mixed $data, string $type = 'array', string $separator = '.', array $context = []): mixed
    {
        if (!$this->supportsUndotification($data, $type, $context)) {
            throw new NotUndotifiableValueException($data);
        }

        assert(is_iterable($data));

        $result = [];
        if ('array' !== $type) {
            if (!class_exists($type)) {
                throw new UnsupportedOperationException('`'.$type.'` is not a valid class name');
            }
            if (!isset(self::$reflectionClassCache[$type])) {
                self::$reflectionClassCache[$type] = new ReflectionClass($type);
            }
            $reflectionClass = self::$reflectionClassCache[$type];
            $result          = $reflectionClass->newInstance();
        }

        $dot = new Dot($result, $separator);

        foreach ($data as $path => $value) {
            $dot->create($path, $value);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDotification(mixed $data, array $context = []): bool
    {
        return is_array($data) || is_object($data);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsUndotification(mixed $data, string $type = 'array', array $context = []): bool
    {
        return is_iterable($data);
    }
}
