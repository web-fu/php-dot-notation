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

interface UndotifierInterface
{
    /**
     * Denormalize a dot notation array to a normal array or object.
     *
     * @param mixed[]|object   $data      Data to restore
     * @param string           $type      Type of the data to restore
     * @param non-empty-string $separator Separator to use for dot notation
     * @param mixed[]          $context   Context options for the normalization
     *
     * @return mixed
     */
    public function undotify(mixed $data, string $type = 'array', string $separator = '.', array $context = []): mixed;

    /**
     * Check if the data can be undotified.
     *
     * @param mixed   $data    Data to check
     * @param string  $type    Type of the data to check
     * @param mixed[] $context Context options for the normalization
     *
     * @return bool
     */
    public function supportsUndotification(mixed $data, string $type = 'array', array $context = []): bool;
}
