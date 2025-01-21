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

class PathNotFoundException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct('Path `'.$path.'` not found');
    }
}
