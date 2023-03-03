<?php

declare(strict_types=1);

namespace WebFu\Tests\Proxy;

use PHPUnit\Framework\TestCase;
use WebFu\Proxy\ArrayProxy;

class ArrayProxyTest extends TestCase
{
    /**
     * @dataProvider hasDataProvider
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
            'element' => [1],
            'key' => 0,
            'expected' => true,
        ];
        yield 'array.key.string' => [
            'element' => ['foo' => 1],
            'key' => 'foo',
            'expected' => true,
        ];
        yield 'array.key.not-exist' => [
            'element' => [],
            'key' => 0,
            'expected' => false,
        ];
    }

    public function testHasAfterChange(): void
    {
        $element = ['foo' => 'string'];
        $wrapper = new ArrayProxy($element);
        $this->assertSame(false, $wrapper->has('bar'));
        $element['bar'] = 'new';
        $this->assertSame(true, $wrapper->has('bar'));
    }

    /**
     * @dataProvider getKeysProvider
     * @param mixed[] $element
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
            'element' => [1, 2, 3],
            'expected' => [0, 1, 2],
        ];
        yield 'numeric.keys.starting_with' => [
            'element' => [3 => 1, 2, 3],
            'expected' => [3, 4, 5],
        ];
        yield 'numeric.keys.sparse' => [
            'element' => [3 => 1, -12 => 2, 5 => 3],
            'expected' => [3, -12, 5],
        ];
        yield 'literal.keys' => [
            'element' => ['foo' => 1, 'bar' => true],
            'expected' => ['foo', 'bar'],
        ];
        yield 'mixed.keys' => [
            'element' => ['foo' => 1, 'bar'],
            'expected' => ['foo', 0],
        ];
    }

    /**
     * @dataProvider getDataProvider
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
            'element' => [1],
            'key' => 0,
            'expected' => 1,
        ];
        yield 'array.key.string' => [
            'element' => ['foo' => 1],
            'key' => 'foo',
            'expected' => 1,
        ];
    }

    public function testSet(): void
    {
        $element = ['foo' => 1];
        $wrapper = new ArrayProxy($element);
        $wrapper->set('foo', 2);
        $this->assertSame(2, $element['foo']);
    }
}
