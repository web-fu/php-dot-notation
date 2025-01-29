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

use WebFu\DotNotation\Exception\NotDotifiableValueException;
use WebFu\DotNotation\Exception\NotUndotifiableValueException;

interface DotifierInterface
{
    /**
     * Normalize an array or object to a dot notation array.
     *
     * @param mixed[]|object   $data      Object or array to normalize
     * @param non-empty-string $separator Separator to use for dot notation
     * @param mixed[]          $context   Context options for the normalization
     *
     * @throws NotDotifiableValueException If the value cannot be dotified
     *
     * @return array<string, mixed>
     */
    public function dotify(mixed $data, string $separator = '.', array $context = []): array;

    /**
     * Check if the data can be dotified.
     *
     * @param mixed   $data    Data to check
     * @param mixed[] $context Context options for the normalization
     *
     * @throws NotUndotifiableValueException If the value cannot be undotified
     *
     * @return bool
     */
    public function supportsDotification(mixed $data, array $context = []): bool;
}
