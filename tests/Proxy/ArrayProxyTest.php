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
use WebFu\DotNotation\Exception\PathNotFoundException;
use WebFu\DotNotation\Proxy\ArrayProxy;
use WebFu\DotNotation\Tests\TestData\SimpleClass;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\Proxy\ArrayProxy
 *
 * @group unit
 */
class ArrayProxyTest extends TestCase
{
    /**
     * @covers ::has
     *
     * @dataProvider hasDataProvider
     *
     * @param mixed[] $element
     */
    public function testHas(array $element, int|string $key, bool $expected): void
    {
        $wrapper = new ArrayProxy($element);
        $this->assertSame($expected, $wrapper->has($key));
    }

    /**
     * @return iterable<mixed[]>
     */
    public function hasDataProvider(): iterable
    {
        yield 'array.key.int' => [
            'element'  => [1],
            'key'      => 0,
            'expected' => true,
        ];
        yield 'array.key.string' => [
            'element'  => ['foo' => 1],
            'key'      => 'foo',
            'expected' => true,
        ];
        yield 'array.key.not-exist' => [
            'element'  => [],
            'key'      => 0,
            'expected' => false,
        ];
    }

    /**
     * @covers ::has
     */
    public function testHasAfterChange(): void
    {
        $element = ['foo' => 'string'];
        $wrapper = new ArrayProxy($element);
        $this->assertFalse($wrapper->has('bar'));
        $element['bar'] = 'new';
        $this->assertTrue($wrapper->has('bar'));
    }

    /**
     * @covers ::getKeys
     *
     * @dataProvider getKeysProvider
     *
     * @param mixed[]           $element
     * @param array<int|string> $expected
     */
    public function testGetKeys(array $element, array $expected): void
    {
        $wrapper = new ArrayProxy($element);
        $this->assertSame($expected, $wrapper->getKeys());
    }

    /**
     * @return iterable<array{element: mixed[], expected: array<int|string>}>
     */
    public function getKeysProvider(): iterable
    {
        yield 'numeric.keys' => [
            'element'  => [1, 2, 3],
            'expected' => [0, 1, 2],
        ];
        yield 'numeric.keys.starting_with' => [
            'element'  => [3 => 1, 2, 3],
            'expected' => [3, 4, 5],
        ];
        yield 'numeric.keys.sparse' => [
            'element'  => [3 => 1, -12 => 2, 5 => 3],
            'expected' => [3, -12, 5],
        ];
        yield 'literal.keys' => [
            'element'  => ['foo' => 1, 'bar' => true],
            'expected' => ['foo', 'bar'],
        ];
        yield 'mixed.keys' => [
            'element'  => ['foo' => 1, 'bar'],
            'expected' => ['foo', 0],
        ];
    }

    /**
     * @covers ::get
     *
     * @dataProvider getDataProvider
     *
     * @param mixed[] $element
     */
    public function testGet(array $element, int|string $key, mixed $expected): void
    {
        $wrapper = new ArrayProxy($element);
        $this->assertSame($expected, $wrapper->get($key));
    }

    /**
     * @return iterable<array{element: mixed[], key: int|string, expected: mixed}>
     */
    public function getDataProvider(): iterable
    {
        yield 'array.key.int' => [
            'element'  => [1],
            'key'      => 0,
            'expected' => 1,
        ];
        yield 'array.key.string' => [
            'element'  => ['foo' => 1],
            'key'      => 'foo',
            'expected' => 1,
        ];
    }

    /**
     * @covers ::get
     */
    public function testGetFailIfKeyDoNotExists(): void
    {
        $element = [];

        $this->expectExceptionMessage('Key `foo` not found');
        $this->expectException(PathNotFoundException::class);

        $wrapper = new ArrayProxy($element);
        $wrapper->get('foo');
    }

    /**
     * @covers ::set
     */
    public function testSet(): void
    {
        $element = ['foo' => 1];
        $wrapper = new ArrayProxy($element);
        $wrapper->set('foo', 2);
        $this->assertSame(2, $element['foo']);
    }

    /**
     * @covers ::set
     */
    public function testSetFailIfKeyDoNotExists(): void
    {
        $element = [];

        $this->expectExceptionMessage('Key `foo` not found');
        $this->expectException(PathNotFoundException::class);

        $wrapper = new ArrayProxy($element);
        $wrapper->set('foo', 2);
    }

    public function testIsInitialised(): void
    {
        $element = [
            'foo' => null,
        ];

        $proxy = new ArrayProxy($element);
        $this->assertFalse($proxy->isInitialised('foo'));

        $element['foo'] = new SimpleClass();

        $this->assertTrue($proxy->isInitialised('foo'));
    }

    /**
     * @covers ::init
     */
    public function testInit(): void
    {
        $element = [
            'foo' => null,
        ];

        $proxy = new ArrayProxy($element);
        $proxy->init('foo', SimpleClass::class);
        $this->assertInstanceOf(SimpleClass::class, $element['foo']);

        $element = [];

        $proxy = new ArrayProxy($element);
        $proxy->init('foo');
        $this->assertNull($element['foo']);
    }

    /**
     * @covers ::unset
     */
    public function testUnset(): void
    {
        $element = [
            'foo' => 1,
        ];

        $proxy = new ArrayProxy($element);
        $proxy->unset('foo');
        $this->assertArrayNotHasKey('foo', $element);
    }

    public function testUnsetChangeNothingIfNothingToUnset(): void
    {
        $element = ['bar' => 'baz'];

        $proxy = new ArrayProxy($element);
        $proxy->unset('foo');

        $this->assertEquals(['bar' => 'baz'], $element);
    }

    /**
     * @covers ::getReflectionType
     */
    public function testGetReflectionType(): void
    {
        $element = ['foo' => 'string'];

        $proxy = new ArrayProxy($element);

        $expected = new ReflectionType(['string']);
        $this->assertEquals($expected, $proxy->getReflectionType('foo'));
    }

    /**
     * @covers ::getReflectionType
     */
    public function testGetReflectionTypeFailIfKeyDoNotExists(): void
    {
        $element = [];

        $this->expectExceptionMessage('Key `foo` not found');
        $this->expectException(PathNotFoundException::class);

        $proxy = new ArrayProxy($element);
        $proxy->getReflectionType('foo');
    }
}
