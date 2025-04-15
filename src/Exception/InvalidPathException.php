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

use Exception;
use Throwable;

class InvalidPathException extends Exception
{
    public function __construct(string $path, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(sprintf('Path `%s` must be an array or an object', $path), $code, $previous);
    }
}
