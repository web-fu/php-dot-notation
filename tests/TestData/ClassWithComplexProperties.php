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

class ClassWithComplexProperties
{
    public SimpleClass $simple;
    public SimpleClass|OtherSimpleClass $union;
    public array $array;

    public function setUnion(SimpleClass|OtherSimpleClass $union): self
    {
        $this->union = $union;

        return $this;
    }
}
