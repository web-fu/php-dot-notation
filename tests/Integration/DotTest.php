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

namespace WebFu\DotNotation\Tests\Integration;

use PHPUnit\Framework\TestCase;
use stdClass;
use WebFu\DotNotation\Dot;
use WebFu\DotNotation\Exception\InvalidPathException;
use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\Dot
 */
class DotTest extends TestCase
{
    /**
     * @covers ::get
     *
     * @dataProvider getProvider
     *
     * @param mixed[]|object $element
     */
    public function testGet(array|object $element, string $path, mixed $expected): void
    {
        $dot = new Dot($element);
        $this->assertEquals($expected, $dot->get($path));
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string, expected: mixed}>
     */
    public function getProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class() {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'class.array' => [
            'element' => new class() {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => [0, 1, 2],
        ];
        yield 'class.class' => [
            'element' => new class() {
                public object $object;

                public function __construct()
                {
                    $this->object       = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path'     => 'object',
            'expected' => (object) ['test' => 'test'],
        ];
        yield 'class.method' => [
            'element' => new class() {
                public function method(): int
                {
                    return 1;
                }
            },
            'path'     => 'method()',
            'expected' => 1,
        ];
        yield 'class.array.property' => [
            'element' => new class() {
                /**
                 * @var object[]
                 */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class() {
                            public string $string = 'test';
                        },
                    ];
                }
            },
            'path'     => 'objectList.0.string',
            'expected' => 'test',
        ];
        yield 'array.scalar' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'array.array' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'list',
            'expected' => [0, 1, 2],
        ];
        yield 'array.class' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'object',
            'expected' => (object) ['test' => 'test'],
        ];
        yield 'array.class.property' => [
            'element' => [
                'objectList' => [
                    new class() {
                        public string $string = 'test';
                    },
                ]],
            'path'     => 'objectList.0.string',
            'expected' => 'test',
        ];
    }

    public function testGetReflectionType(): void
    {
        $element = ['foo' => 1];
        $dot     = new Dot($element);
        $type    = $dot->getReflectionType('foo');

        $expected = new ReflectionType(['int']);

        $this->assertEquals($expected, $type);
    }

    /**
     * @covers ::get
     */
    public function testGetWithCustomSeparator(): void
    {
        $element = ['foo' => ['bar' => 1]];
        $dot     = new Dot($element, '|');
        $this->assertEquals(1, $dot->get('foo|bar'));
    }

    /**
     * @covers ::set
     *
     * @dataProvider setProvider
     *
     * @param mixed[]|object $element
     */
    public function testSet(array|object $element, string $path, mixed $value): void
    {
        $dot    = new Dot($element);
        $actual = $dot->set($path, $value);
        $this->assertEquals($value, $actual->get($path));
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string, value: mixed}>
     */
    public function setProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class() {
                public string $scalar = 'scalar';
            },
            'path'  => 'scalar',
            'value' => 'new',
        ];
        yield 'class.array' => [
            'element' => new class() {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'  => 'list',
            'value' => [3, 4, 5],
        ];
        yield 'class.class' => [
            'element' => new class() {
                public object $object;

                public function __construct()
                {
                    $this->object       = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path'  => 'object',
            'value' => (object) ['new' => 'new'],
        ];
        yield 'class.array.property' => [
            'element' => new class() {
                /**
                 * @var object[]
                 */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class() {
                            public string $string = 'test';
                        },
                    ];
                }
            },
            'path'  => 'objectList.0.string',
            'value' => 'new',
        ];
        yield 'array.scalar' => [
            'element' => ['scalar' => 'scalar'],
            'path'    => 'scalar',
            'value'   => 'new',
        ];
        yield 'array.array' => [
            'element' => ['list' => [0, 1, 2]],
            'path'    => 'list',
            'value'   => [3, 4, 5],
        ];
        yield 'array.class' => [
            'element' => ['object' => (object) ['test' => 'test']],
            'path'    => 'object',
            'value'   => (object) ['new' => 'new'],
        ];
        yield 'array.class.property' => [
            'element' => [
                'objectList' => [
                    new class() {
                        public string $string = 'test';
                    },
                ]],
            'path'  => 'objectList.0.string',
            'value' => 'new',
        ];
    }

    /**
     * @covers ::get
     *
     * @dataProvider missingChildPathProvider
     */
    public function testMissingChildPath(mixed $value, string $type): void
    {
        $element = ['foo' => $value];
        $dot     = new Dot($element);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Element of type '.$type.' has no child element');

        $dot->get('foo.bar');
    }

    /**
     * @return iterable<array{value: mixed, type: string}>
     */
    public function missingChildPathProvider(): iterable
    {
        yield 'bool' => [
            'value' => true,
            'type'  => 'bool',
        ];
        yield 'int' => [
            'value' => 1,
            'type'  => 'int',
        ];
        yield 'float' => [
            'value' => 0.5,
            'type'  => 'float',
        ];
        yield 'string' => [
            'value' => 'baz',
            'type'  => 'string',
        ];
    }

    /**
     * @covers ::get
     */
    public function testGetPathNotFound(): void
    {
        $element = ['exists' => 1];
        $dot     = new Dot($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('notExists path not found');

        $dot->get('notExists');
    }

    /**
     * @covers ::dotify
     *
     * @dataProvider elementProvider
     *
     * @param mixed[]|object $element
     */
    public function testDotify(array|object $element): void
    {
        $arrayDotified = Dot::dotify($element);

        $this->assertEquals([
            'foo'      => 'bar',
            'baz.qux'  => 'quux',
            'baz.quuz' => 'corge',
        ], $arrayDotified);
    }

    /**
     * @return iterable<array{element: mixed[]|object}>
     */
    public function elementProvider(): iterable
    {
        yield 'array' => [
            'element' => [
                'foo' => 'bar',
                'baz' => [
                    'qux'  => 'quux',
                    'quuz' => 'corge',
                ],
            ],
        ];
        yield 'object' => [
            'element' => (object) [
                'foo' => 'bar',
                'baz' => (object) [
                    'qux'  => 'quux',
                    'quuz' => 'corge',
                ],
            ],
        ];
        yield 'array_and_object' => [
            'element' => [
                'foo' => 'bar',
                'baz' => (object) [
                    'qux'  => 'quux',
                    'quuz' => 'corge',
                ],
            ],
        ];
    }

    public function testUndotify(): void
    {
        $arrayDotified = [
            'foo'      => 'bar',
            'baz.qux'  => 'quux',
            'baz.quuz' => 'corge',
        ];

        $array = Dot::undotify($arrayDotified);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => [
                'qux'  => 'quux',
                'quuz' => 'corge',
            ],
        ], $array);
    }
}
