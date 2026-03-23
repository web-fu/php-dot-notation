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

namespace WebFu\DotNotation\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use WebFu\DotNotation\ReflectionAwareProxy;
use WebFu\DotNotation\Tests\TestData\OtherSimpleClass;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\ReflectionAwareProxy
 */
class ReflectionAwareProxyTest extends TestCase
{
    /**
     * @covers ::getReflectionType
     *
     * @dataProvider getDataProvider
     *
     * @param object|array<array-key, mixed> $element
     */
    public function testGetReflectionType(array|object $element, string $path, mixed $expected): void
    {
        $proxy = new ReflectionAwareProxy($element);

        $this->assertEquals($expected, $proxy->getReflectionType($path));
    }

    /**
     * @return iterable<array{element: object|array<array-key, mixed>, path: string, expected: mixed}>
     */
    public function getDataProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => new ReflectionType(['string']),
        ];
        yield 'class.array' => [
            'element' => new class {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => new ReflectionType(['array'], ['int[]']),
        ];
        yield 'class.class' => [
            'element' => new class {
                public object $object;

                public function __construct()
                {
                    $this->object       = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path'     => 'object',
            'expected' => new ReflectionType(['object']),
        ];
        yield 'class.method' => [
            'element'  => new OtherSimpleClass(),
            'path'     => 'method()',
            'expected' => new ReflectionType(['int']),
        ];
        yield 'array.scalar' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'scalar',
            'expected' => new ReflectionType(['string']),
        ];
        yield 'array.array' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'list',
            'expected' => new ReflectionType(['array']),
        ];
        yield 'array.class' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'object',
            'expected' => new ReflectionType(['object']),
        ];
    }
}
