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

namespace WebFu\DotNotation\Tests\TestData;

class ClassWithMagicMethods
{
    private array $internal = [];

    public function __get($name)
    {
        return $this->internal[$name];
    }

    public function __set($name, $value): void
    {
        $this->internal[$name] = $value;
    }
}
