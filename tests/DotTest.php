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
use WebFu\DotNotation\Exception\InvalidPathException;
use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\DotNotation\Exception\PathNotInitialisedException;
use WebFu\DotNotation\Tests\TestData\ChildClass;
use WebFu\DotNotation\Tests\TestData\ClassWithComplexProperties;
use WebFu\DotNotation\Tests\TestData\SimpleClass;
use WebFu\Reflection\ReflectionClass;
use WebFu\Reflection\ReflectionProperty;

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
     * @dataProvider invalidPathProvider
     */
    public function testGetPathNotFound(string $path): void
    {
        $element = new ChildClass();
        $dot     = new Dot($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Path `'.$path.'` not found');

        $dot->get($path);
    }

    /**
     * @return iterable<array{path: string}>
     */
    public function invalidPathProvider(): iterable
    {
        yield 'not_exits' => [
            'path' => 'notExists',
        ];
        yield 'private' => [
            'path' => 'private',
        ];
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

        $this->assertEquals('test2', $element->objectList[0]->string);

        // class -> class -> scalar
        $element = new ClassWithComplexProperties();

        $dot = new Dot($element);
        $dot->set('simple', new SimpleClass());
        $dot->set('simple.public', 'new');

        $this->assertEquals('new', $element->simple->public);
    }

    /**
     * @covers ::set
     */
    public function testSetFailsIfPathIsNotInitialised(): void
    {
        $element = new ClassWithComplexProperties();

        $this->expectException(PathNotInitialisedException::class);
        $this->expectExceptionMessage('Path `simple` is not initialised');

        $dot = new Dot($element);
        $dot->set('simple.public', 'new');
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

    /**
     * @covers ::has
     */
    public function testHasNot(): void
    {
        $element = new class {
            public string $scalar = 'scalar';
        };

        $dot = new Dot($element);

        $this->assertFalse($dot->has('scalar.invalid'));
    }

    /**
     * @covers ::isInitialised
     *
     * @dataProvider initializedCaseProvider
     *
     * @param mixed[]|object $element
     */
    public function testIsInitialised(object|array $element, string $path, bool $expected): void
    {
        $dot = new Dot($element);
        $this->assertEquals($expected, $dot->isInitialised($path));
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string, expected: bool}>
     */
    public function initializedCaseProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => true,
        ];
        yield 'class.array' => [
            'element' => new class {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => true,
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
            'expected' => true,
        ];
        yield 'class.method' => [
            'element' => new class {
                public function method(): void
                {
                }
            },
            'path'     => 'method()',
            'expected' => true,
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
            'expected' => true,
        ];
        yield 'array.scalar' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'scalar',
            'expected' => true,
        ];
        yield 'array.array' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'list',
            'expected' => true,
        ];
        yield 'array.class' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'object',
            'expected' => true,
        ];
    }

    /**
     * @covers ::isInitialised
     */
    public function testIsInitializedFalse(): void
    {
        $element = new ClassWithComplexProperties();
        $dot     = new Dot($element);

        $this->assertFalse($dot->isInitialised('simple'));
    }

    /**
     * @param mixed[]|object $element
     *
     * @dataProvider elementWithoutPathProvider
     */
    public function testIsInitializedFailsIfPathNotFound(array|object $element): void
    {
        $dot = new Dot($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Path `foo.bar` not found');

        $dot->isInitialised('foo.bar');
    }

    /**
     * @return iterable<array{element: mixed[]|object}>
     */
    public function elementWithoutPathProvider(): iterable
    {
        yield 'array' => [
            'element' => ['foo' => 1],
        ];
        yield 'object' => [
            'element' => (object) ['foo' => 1],
        ];
    }

    /**
     * @covers ::create
     *
     * @param mixed[]|object $element
     *
     * @dataProvider elementAndPathProvider
     */
    public function testCreate(array|object $element, string $path): void
    {
        $dot = new Dot($element);
        $dot->create($path, 'string');

        $this->assertEquals('string', $dot->get($path));
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string}>
     */
    public function elementAndPathProvider(): iterable
    {
        yield 'array' => [
            'element' => [],
            'path'    => 'foo',
        ];
        yield 'object' => [
            'element' => new stdClass(),
            'path'    => 'foo',
        ];
        yield 'array.array' => [
            'element' => [],
            'path'    => 'foo.bar',
        ];
        yield 'object.object' => [
            'element' => new stdClass(),
            'path'    => 'foo.bar',
        ];
    }

    /**
     * @covers ::unset
     */
    public function testUnset(): void
    {
        $element = ['foo' => 1];
        $dot     = new Dot($element);
        $dot->unset('foo');

        $this->assertArrayNotHasKey('foo', $element);

        $test = new class {
            /**
             * @var string[]
             */
            public array $array = [
                'foo' => 'bar',
            ];
        };

        $dot = new Dot($test);
        $dot->unset('array.foo');

        $this->assertArrayNotHasKey('foo', $test->array);
    }

    /**
     * @covers ::unset
     */
    public function testUnsetDoesNotChangeIfNothingToUnset(): void
    {
        $element = ['foo' => 1];
        $dot     = new Dot($element);
        $dot->unset('bar');

        $this->assertEquals(['foo' => 1], $element);
    }

    /**
     * @covers ::unset
     */
    public function testUnsetDoesNotChangeIfNotInitialized(): void
    {
        $element = new SimpleClass();
        $dot     = new Dot($element);
        $dot->unset('public');

        $reflection         = new ReflectionClass($element);
        $reflectionProperty = $reflection->getProperty('public');

        $this->assertInstanceof(ReflectionProperty::class, $reflectionProperty);
        $this->assertFalse($reflectionProperty->isInitialized($element));
    }

    /**
     * @covers ::dot
     */
    public function testDot(): void
    {
        $element = ['foo' => ['bar' => 1]];
        $dot     = new Dot($element);
        $barDot  = $dot->dot('foo');

        $this->assertEquals(1, $barDot->get('bar'));
    }

    /**
     * @covers ::dot
     */
    public function testDotFailIfPathIsNotAnArrayOrObject(): void
    {
        $element = ['foo' => 1];
        $dot     = new Dot($element);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Path `foo` must be an array or an object in order to create a Dot instance');

        $dot->dot('foo');
    }

    /**
     * @covers ::dotify
     *
     * @dataProvider elementProvider
     *
     * @param mixed[]|object $element
     * @param mixed[]        $expected
     */
    public function testDotify(array|object $element, array $expected): void
    {
        $actual = Dot::dotify($element);

        $this->assertEquals($expected, $actual);
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
            'expected' => [
                'foo'      => 'bar',
                'baz.qux'  => 'quux',
                'baz.quuz' => 'corge',
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
            'expected' => [
                'foo'      => 'bar',
                'baz.qux'  => 'quux',
                'baz.quuz' => 'corge',
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
            'expected' => [
                'foo'      => 'bar',
                'baz.qux'  => 'quux',
                'baz.quuz' => 'corge',
            ],
        ];
        yield 'simpleClass' => [
            'element' => new SimpleClass(),
            'expected' => [

            ],
        ];
    }

    /**
     * @covers ::undotify
     */
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

        $arrayWithNumericIndex = [
            'foo'        => 'bar',
            'baz.0.qux'  => 'quux',
            'baz.0.quuz' => 'corge',
            'baz.1.qux'  => 'abc',
            'baz.1.quuz' => 'def',
        ];

        $array = Dot::undotify($arrayWithNumericIndex);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => [
                [
                    'qux'  => 'quux',
                    'quuz' => 'corge',
                ],
                [
                    'qux'  => 'abc',
                    'quuz' => 'def',
                ],
            ],
        ], $array);
    }

    public function testGarbageCollector(): void
    {
        $element = ['foo' => ['bar' => 1]];

        $dot     = new Dot($element);

        $this->expectNotToPerformAssertions();

        $class =new stdClass();
        $func = fn () => $class;

        $element = $func();
    }
}
