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

namespace WebFu\Tests\Fixture;

trait TestTrait
{
    public string $publicTrait;
    protected string $protectedTrait;
    private string $privateTrait;

    public function publicTrait(): void
    {
    }

    protected function protectedTrait(): void
    {
    }

    private function privateTrait(): void
    {
    }
}
