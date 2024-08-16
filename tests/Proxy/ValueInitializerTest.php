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

namespace WebFu\DotNotation\Tests\Proxy;

use PHPUnit\Framework\TestCase;
use WebFu\DotNotation\Proxy\ValueInitializer;
use WebFu\DotNotation\Tests\TestData\SimpleClass;

/**
 * @coversDefaultClass \WebFu\DotNotation\Proxy\ValueInitializer
 *
 * @group unit
 */
class ValueInitializerTest extends TestCase
{
    /**
     * @covers ::init
     */
    public function testInit(): void
    {
        $this->assertNull(ValueInitializer::init());
        $this->assertEquals([], ValueInitializer::init('array'));
        $this->assertInstanceOf(SimpleClass::class, ValueInitializer::init(SimpleClass::class));
    }
}
