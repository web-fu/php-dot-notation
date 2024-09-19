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
use stdClass;
use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\DotNotation\Proxy\ClassProxy;
use WebFu\DotNotation\Tests\TestData\ChildClass;
use WebFu\DotNotation\Tests\TestData\ClassWithAllowDynamicProperties;
use WebFu\DotNotation\Tests\TestData\ClassWithComplexProperties;
use WebFu\DotNotation\Tests\TestData\ClassWithMagicMethods;
use WebFu\DotNotation\Tests\TestData\SimpleClass;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\Proxy\ClassProxy
 *
 * @group unit
 */
class ClassProxyTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $element = new ChildClass();
        $proxy   = new ClassProxy($element);

        $this->assertEquals([
            'public',
            'publicParent',
            'publicTrait',
            'public()',
            'publicParent()',
            'publicTrait()',
        ], $proxy->getKeys());
    }

    /**
     * @covers ::has
     *
     * @dataProvider hasDataProvider
     */
    public function testHas(object $element, string $key, bool $expected): void
    {
        $proxy = new ClassProxy($element);
        $this->assertSame($expected, $proxy->has($key));
    }

    /**
     * @return iterable<mixed[]>
     */
    public function hasDataProvider(): iterable
    {
        yield 'class.property.exists' => [
            'element' => new class {
                public string $property;
            },
            'key'      => 'property',
            'expected' => true,
        ];
        yield 'class.property.not-exists' => [
            'element' => new class {
            },
            'key'      => 'property',
            'expected' => false,
        ];
        yield 'class.property.is-not-visible' => [
            'element' => new class {
                /**
                 * @phpstan-ignore-next-line
                 */
                private string $property;
            },
            'key'      => 'property',
            'expected' => false,
        ];
        yield 'class.method.exists' => [
            'element' => new class {
                public function method(): void
                {
                }
            },
            'key'      => 'method()',
            'expected' => true,
        ];
        yield 'class.method.not-exists' => [
            'element' => new class {
            },
            'key'      => 'method()',
            'expected' => false,
        ];
        yield 'class.method.is-not-visible' => [
            'element' => new class {
                /**
                 * @phpstan-ignore-next-line
                 */
                private function method(): void
                {
                }
            },
            'key'      => 'method()',
            'expected' => false,
        ];
    }

    /**
     * @covers ::getKeys
     */
    public function testGetKeys(): void
    {
        $class = new ChildClass();
        $proxy = new ClassProxy($class);

        $this->assertSame([
            'public',
            'publicParent',
            'publicTrait',
            'public()',
            'publicParent()',
            'publicTrait()',
        ], $proxy->getKeys());
    }

    /**
     * @covers ::get
     *
     * @dataProvider getDataProvider
     */
    public function testGet(object $element, string $key, mixed $expected): void
    {
        $proxy = new ClassProxy($element);
        $this->assertSame($expected, $proxy->get($key));
    }

    /**
     * @return iterable<array{element: object, key: string, expected: mixed}>
     */
    public function getDataProvider(): iterable
    {
        yield 'class.property' => [
            'element' => new class {
                public string $property = 'foo';
            },
            'key'      => 'property',
            'expected' => 'foo',
        ];
        yield 'class.method' => [
            'element' => new class {
                public function method(): string
                {
                    return 'foo';
                }
            },
            'key'      => 'method()',
            'expected' => 'foo',
        ];
    }

    /**
     * @covers ::get
     */
    public function testGetFailsIfKeyDoNotExists(): void
    {
        $element = new class {
        };

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Key `property` not found');

        $proxy = new ClassProxy($element);
        $proxy->get('property');
    }

    /**
     * @covers ::get
     */
    public function testGetFailsIfKeyIsPrivate(): void
    {
        $element = new class {
            private string $property = 'foo';
        };

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Key `property` not found');

        $proxy = new ClassProxy($element);
        $proxy->get('property');
    }

    /**
     * @covers ::set
     */
    public function testSet(): void
    {
        $element = new class {
            public string $property = 'foo';
        };

        $proxy = new ClassProxy($element);
        $proxy->set('property', 'bar');

        $this->assertSame('bar', $element->property);
    }

    /**
     * @covers ::set
     */
    public function testSetFailsIfKeyDoNotExists(): void
    {
        $element = new class {
        };

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Key `property` not found');

        $proxy = new ClassProxy($element);
        $proxy->set('property', 'bar');
    }

    /**
     * @covers ::set
     */
    public function testSetFailsIfKeyIsMethod(): void
    {
        $element = new class {
            public function method(): void
            {
            }
        };

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot set a class method');

        $proxy = new ClassProxy($element);
        $proxy->set('method()', 'bar');
    }

    /**
     * @covers ::isInitialised
     */
    public function testIsInitialised(): void
    {
        $element = new class {
            public string|null $foo;

            public function method(): void
            {
            }
        };

        $proxy = new ClassProxy($element);

        $this->assertFalse($proxy->isInitialised('foo'));

        $element->foo = 'bar';

        $this->assertTrue($proxy->isInitialised('foo'));
        $this->assertTrue($proxy->isInitialised('method()'));
    }

    /**
     * @covers ::isInitialised
     */
    public function testIsInitialisedFailsIfNoKey(): void
    {
        $element = new class {
        };

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Key `foo` not found');

        $proxy = new ClassProxy($element);
        $proxy->isInitialised('foo');
    }

    /**
     * @covers ::init
     */
    public function testInit(): void
    {
        $element = new class {
            public SimpleClass $property;
            /**
             * @var mixed[]
             */
            public array $array;
        };

        $proxy = new ClassProxy($element);
        $proxy->init('property');

        $this->assertInstanceOf(SimpleClass::class, $element->property);

        $proxy->init('array');
        $this->assertSame([], $element->array);

        $element = new ClassWithComplexProperties();
        $proxy   = new ClassProxy($element);

        $proxy->init('union', SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $element->union);
    }

    /**
     * @covers ::init
     */
    public function testInitChangesNothingIfAlreadyInitialised(): void
    {
        $element         = new SimpleClass();
        $element->public = 'foo';

        $proxy = new ClassProxy($element);

        $proxy->init('public');

        $expected         = new SimpleClass();
        $expected->public = 'foo';

        $this->assertEquals($expected, $element);
    }

    /**
     * @covers ::init
     */
    public function testInitFailsIfNoKeyFound(): void
    {
        $element = new class {
        };

        $proxy = new ClassProxy($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Key `foo` not found');

        $proxy->init('foo');
    }

    /**
     * @covers ::init
     */
    public function testInitFailsIfTryToInitialiseMethod(): void
    {
        $element = new class {
            public function method(): void
            {
            }
        };

        $proxy = new ClassProxy($element);

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot init a class method');

        $proxy->init('method()');
    }

    /**
     * @covers ::init
     */
    public function testInitFailsIfUnionPropertyNotDeclared(): void
    {
        $element = new ClassWithComplexProperties();

        $proxy = new ClassProxy($element);

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('In case of union type you must specify the type');

        $proxy->init('union');
    }

    /**
     * @covers ::create
     *
     * @dataProvider classWithDynamicPropertiesProvider
     */
    public function testCreate(object $element): void
    {
        $proxy = new ClassProxy($element);
        $proxy->create('foo', SimpleClass::class);

        $this->assertInstanceOf(SimpleClass::class, $element->foo);
    }

    /**
     * @return iterable<array{element: object}>
     */
    public function classWithDynamicPropertiesProvider(): iterable
    {
        yield 'stdClass' => [
            'element' => new stdClass(),
        ];
        yield 'classAllowDynamicProperties' => [
            'element' => new ClassWithAllowDynamicProperties(),
        ];
        yield 'classWithMagicMethods' => [
            'element' => new ClassWithMagicMethods(),
        ];
    }

    /**
     * @covers ::create
     */
    public function testCreateChangesNothingIfPropertyAlreadyExists(): void
    {
        $element              = new stdClass();
        $element->foo         = new SimpleClass();
        $element->foo->public = 'test';

        $proxy = new ClassProxy($element);

        $proxy->create('foo', SimpleClass::class);

        $expected              = new stdClass();
        $expected->foo         = new SimpleClass();
        $expected->foo->public = 'test';

        $this->assertEquals($expected, $element);
    }

    /**
     * @covers ::create
     */
    public function testCreateFailsIfNoDynamicPropertiesAllowed(): void
    {
        $element = new SimpleClass();

        $proxy = new ClassProxy($element);

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot create a new property');

        $proxy->create('foo', SimpleClass::class);
    }

    /**
     * @covers ::unset
     */
    public function testUnset(): void
    {
        $element = new class {
            public string $property = 'foo';
        };

        $proxy = new ClassProxy($element);
        $proxy->unset('property');

        $this->assertFalse(isset($element->property));
    }

    /**
     * @covers ::unset
     */
    public function testUnsetChangesNothingIfNothingToUnset(): void
    {
        $element = new SimpleClass();

        $proxy = new ClassProxy($element);
        $proxy->unset('propertyNotExists');

        $expected = new SimpleClass();

        $this->assertEquals($expected, $element);
    }

    /**
     * @covers ::unset
     */
    public function testUnsetFailsIfKeyIsMethod(): void
    {
        $element = new class {
            public function method(): void
            {
            }
        };

        $proxy = new ClassProxy($element);

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot unset a class method');

        $proxy->unset('method()');
    }

    /**
     * @covers ::getReflectionType
     */
    public function testGetReflectionType(): void
    {
        $element = new class {
            public string $property = 'foo';

            public function method(): string
            {
                return 'foo';
            }
        };

        $proxy = new ClassProxy($element);

        $expected = new ReflectionType(['string']);
        $this->assertEquals($expected, $proxy->getReflectionType('property'));
        $this->assertEquals($expected, $proxy->getReflectionType('method()'));
    }

    /**
     * @covers ::getReflectionType
     */
    public function testGetReflectionTypeFailsIfKeyDoNotExists(): void
    {
        $element = new class {
        };

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Key `property` not found');

        $proxy = new ClassProxy($element);
        $proxy->getReflectionType('property');
    }
}
