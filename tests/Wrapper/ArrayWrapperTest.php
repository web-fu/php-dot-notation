<?php

declare(strict_types=1);

namespace WebFu\Tests\Wrapper;

use PHPUnit\Framework\TestCase;
use WebFu\Wrapper\ArrayWrapper;

class ArrayWrapperTest extends TestCase
{
    /**
     * @dataProvider hasDataProvider
     */
    public function testHas(array $element, string|int $key, bool $expected): void
    {
        $wrapper = new ArrayWrapper($element);
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
        $wrapper = new ArrayWrapper($element);
        $this->assertSame(false, $wrapper->has('bar'));
        $element['bar'] = 'new';
        $this->assertSame(true, $wrapper->has('bar'));
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(array|object $element, string|int $key, mixed $expected): void
    {
        $wrapper = new ArrayWrapper($element);
        $this->assertSame($expected, $wrapper->get($key));
    }

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
}