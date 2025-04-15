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

namespace WebFu\DotNotation\Exception;

use Throwable;
use UnexpectedValueException;

class NotUndotifiableValueException extends UnexpectedValueException
{
    public function __construct(mixed $value, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(sprintf('Value of type %s cannot be undotified', gettype($value)), $code, $previous);
    }
}
