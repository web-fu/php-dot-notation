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
use WebFu\DotNotation\Dot;
use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\DotNotation\Tests\TestData\ClassWithComplexProperties;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\Dot
 *
 * @group integration
 */
class DotTest extends TestCase
{
    /**
     * @covers ::get
     *
     * @dataProvider getDataProvider
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
    public function getDataProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'class.array' => [
            'element' => new class {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => [0, 1, 2],
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
            'expected' => (object) ['test' => 'test'],
        ];
        yield 'class.method' => [
            'element' => new class {
                public function method(): int
                {
                    return 1;
                }
            },
            'path'     => 'method()',
            'expected' => 1,
        ];
        yield 'class.array.property' => [
            'element' => new class {
                /**
                 * @var object[]
                 */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class {
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
                    new class {
                        public string $string = 'test';
                    },
                ]],
            'path'     => 'objectList.0.string',
            'expected' => 'test',
        ];
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
     * @covers ::get
     *
     * @dataProvider missingChildPathProvider
     */
    public function testMissingChildPath(mixed $value, string $type): void
    {
        $element = ['foo' => $value];
        $dot     = new Dot($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Path `foo.bar` not found');

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
        $this->expectExceptionMessage('Path `notExists` not found');

        $dot->get('notExists');
    }

    /**
     * @covers ::set
     *
     * @dataProvider setDataProvider
     *
     * @param mixed[]|object $element
     */
    public function testSet(array|object $element, string $path, mixed $expected): void
    {
        $dot    = new Dot($element);
        $actual = $dot->set($path, $expected);
        $this->assertEquals($expected, $actual->get($path));
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string, expected: mixed}>
     */
    public function setDataProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => 'new',
        ];
        yield 'class.array' => [
            'element' => new class {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => [3, 4, 5],
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
            'expected' => (object) ['new' => 'new'],
        ];
        yield 'array.scalar' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'scalar',
            'expected' => 'new',
        ];
        yield 'array.array' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'list',
            'expected' => [3, 4, 5],
        ];
        yield 'array.class' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'object',
            'expected' => (object) ['new' => 'new'],
        ];
    }

    /**
     * @covers ::set
     */
    public function testSetExtraCases(): void
    {
        // class -> array -> class -> scalar
        $element = new class {
            /**
             * @var object[]
             */
            public array $objectList;

            public function __construct()
            {
                $this->objectList = [
                    new class {
                        public string $string = 'test';
                    },
                ];
            }
        };

        $dot = new Dot($element);
        $dot->set('objectList.0.string', 'test2');

        /* @phpstan-ignore-next-line */
        $this->assertEquals('test2', $element->objectList[0]->string);

        // class -> class -> scalar
        $element = new ClassWithComplexProperties();

        $dot = new Dot($element);
        $dot->set('simple.public', 'new');

        $this->assertEquals('new', $element->simple->public);
    }

    /**
     * @covers ::has
     *
     * @dataProvider hasDataProvider
     *
     * @param mixed[]|object $element
     */
    public function testHas(array|object $element, string $path, bool $expected): void
    {
        $dot    = new Dot($element);
        $actual = $dot->has($path);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string, expected: bool}>
     */
    public function hasDataProvider(): iterable
    {
        yield 'class.scalar.exists' => [
            'element' => new class {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => true,
        ];
        yield 'class.scalar.not-exists' => [
            'element' => new class {
                public string $scalar = 'scalar';
            },
            'path'     => 'notExists',
            'expected' => false,
        ];
        yield 'class.array.exists' => [
            'element' => new class {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => true,
        ];
        yield 'class.array.not-exists' => [
            'element' => new class {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'notExists',
            'expected' => false,
        ];
        yield 'class.class.exists' => [
            'element' => new class {
                public object $object;

                public function __construct()
                {
                    $this->object       = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path'     => 'object',
            'expected' => true,
        ];
        yield 'class.class.not-exists' => [
            'element' => new class {
                public object $object;

                public function __construct()
                {
                    $this->object       = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path'     => 'notExists',
            'expected' => false,
        ];
        yield 'class.method.exists' => [
            'element' => new class {
                public function method(): void
                {
                }
            },
            'path'     => 'method()',
            'expected' => true,
        ];
        yield 'class.method.not-exists' => [
            'element' => new class {
                public function method(): void
                {
                }
            },
            'path'     => 'notExists()',
            'expected' => false,
        ];
        yield 'class.array.property.exists' => [
            'element' => new class {
                /**
                 * @var object[]
                 */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class {
                            public string $string = 'test';
                        },
                    ];
                }
            },
            'path'     => 'objectList.0.string',
            'expected' => true,
        ];
        yield 'class.array.property.not-exists' => [
            'element' => new class {
                /**
                 * @var object[]
                 */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class {
                            public string $string = 'test';
                        },
                    ];
                }
            },
            'path'     => 'objectList.0.notExists',
            'expected' => false,
        ];
        yield 'array.scalar.exists' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'scalar',
            'expected' => true,
        ];
        yield 'array.scalar.not-exists' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'notExists',
            'expected' => false,
        ];
        yield 'array.array.exists' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'list',
            'expected' => true,
        ];
        yield 'array.array.not-exists' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'notExists',
            'expected' => false,
        ];
        yield 'array.class.exists' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'object',
            'expected' => true,
        ];
        yield 'array.class.not-exists' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'notExists',
            'expected' => false,
        ];
        yield 'array.class.property.exists' => [
            'element' => [
                'objectList' => [
                    new class {
                        public string $string = 'test';
                    },
                ]],
            'path'     => 'objectList.0.string',
            'expected' => true,
        ];
        yield 'array.class.property.not-exists' => [
            'element' => [
                'objectList' => [
                    new class {
                        public string $string = 'test';
                    },
                ]],
            'path'     => 'objectList.0.notExists',
            'expected' => false,
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
