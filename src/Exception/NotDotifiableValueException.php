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

use UnexpectedValueException;

class NotDotifiableValueException extends UnexpectedValueException
{
    public function __construct($value)
    {
        parent::__construct(sprintf('Value of type %s cannot be dotified', gettype($value)));
    }
}
