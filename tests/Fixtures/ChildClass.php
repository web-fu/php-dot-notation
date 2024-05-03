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

namespace WebFu\DotNotation\Tests\Fixtures;

class ChildClass extends ParentClass
{
    use TestTrait;

    public string $public;
    protected string $protected;
    private string $private;

    public function public(): void
    {
    }

    protected function protected(): void
    {
    }

    private function private(): void
    {
    }
}
