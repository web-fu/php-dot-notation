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
use WebFu\DotNotation\Proxy\ArrayProxy;
use WebFu\Reflection\ReflectionType;

/**
 * @coversDefaultClass \WebFu\DotNotation\Proxy\ArrayProxy
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
        $this->expectException(InvalidPathException::class);

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
        $this->expectException(InvalidPathException::class);

        $wrapper = new ArrayProxy($element);
        $wrapper->set('foo', 2);
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
        $this->expectException(InvalidPathException::class);

        $proxy = new ArrayProxy($element);
        $proxy->getReflectionType('foo');
    }
}
