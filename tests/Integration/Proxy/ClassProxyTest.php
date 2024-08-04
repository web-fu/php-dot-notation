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

namespace WebFu\DotNotation\Tests\Integration\Proxy;

use PHPUnit\Framework\TestCase;
use WebFu\DotNotation\Exception\InvalidPathException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\DotNotation\Proxy\ClassProxy;
use WebFu\DotNotation\Tests\Fixtures\ChildClass;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\Proxy\ClassProxy
 */
class ClassProxyTest extends TestCase
{
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
            'element' => new class() {
                public string $property;
            },
            'key'      => 'property',
            'expected' => true,
        ];
        yield 'class.property.not-exists' => [
            'element' => new class() {
            },
            'key'      => 'property',
            'expected' => false,
        ];
        yield 'class.property.is-not-visible' => [
            'element' => new class() {
                /**
                 * @phpstan-ignore-next-line
                 */
                private string $property;
            },
            'key'      => 'property',
            'expected' => false,
        ];
        yield 'class.method.exists' => [
            'element' => new class() {
                public function method(): void
                {
                }
            },
            'key'      => 'method()',
            'expected' => true,
        ];
        yield 'class.method.not-exists' => [
            'element' => new class() {
            },
            'key'      => 'method()',
            'expected' => false,
        ];
        yield 'class.method.is-not-visible' => [
            'element' => new class() {
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
            'element' => new class() {
                public string $property = 'foo';
            },
            'key'      => 'property',
            'expected' => 'foo',
        ];
        yield 'class.method' => [
            'element' => new class() {
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
    public function testGetFailIfKeyDoNotExists(): void
    {
        $element = new class() {
        };

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Key \'property\' not found');

        $proxy = new ClassProxy($element);
        $proxy->get('property');
    }

    /**
     * @covers ::set
     */
    public function testSet(): void
    {
        $element = new class() {
            public string $property = 'foo';
        };

        $proxy = new ClassProxy($element);
        $proxy->set('property', 'bar');

        $this->assertSame('bar', $element->property);
    }

    public function testSetFailIfKeyDoNotExists(): void
    {
        $element = new class() {
        };

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Key \'property\' not found');

        $proxy = new ClassProxy($element);
        $proxy->set('property', 'bar');
    }

    /**
     * @covers ::set
     */
    public function testSetFailIfKeyIsMethod(): void
    {
        $element = new class() {
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
     * @covers ::getReflectionType
     */
    public function testGetReflectionType(): void
    {
        $element = new class() {
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
    public function testGetReflectionTypeFailIfKeyDoNotExists(): void
    {
        $element = new class() {
        };

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Key \'property\' not found');

        $proxy = new ClassProxy($element);
        $proxy->getReflectionType('property');
    }
}
