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

class ParentClass
{
    public string $publicParent;
    protected string $protectedParent;
    private string $privateParent;

    public function publicParent(): void
    {
    }

    protected function protectedParent(): void
    {
    }

    private function privateParent(): void
    {
    }
}
