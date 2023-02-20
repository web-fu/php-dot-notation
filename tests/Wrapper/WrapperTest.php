<?php

declare(strict_types=1);

namespace WebFu\Tests\Wrapper;

use WebFu\Wrapper\Wrapper;
use PHPUnit\Framework\TestCase;

class WrapperTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testHas(array|object $element, string|int $key, bool $expected): void
    {
        $wrapper = new Wrapper($element);
        $actual = $wrapper->has($key);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return iterable<mixed[]>
     */
    public function dataProvider(): iterable
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
        yield 'class.property.exists' => [
            'element' => new class () {
                public string $property;
            },
            'key' => 'property',
            'expected' => true,
        ];
        yield 'class.property.not-exists' => [
            'element' => new class () {
            },
            'key' => 'property',
            'expected' => false,
        ];
        yield 'class.property.is-not-visible' => [
            'element' => new class () {
                private string $property;
            },
            'key' => 'property',
            'expected' => false,
        ];
        yield 'class.method.exists' => [
            'element' => new class () {
                public function method(): void
                {
                }
            },
            'key' => 'method()',
            'expected' => true,
        ];
        yield 'class.method.not-exists' => [
            'element' => new class () {
            },
            'key' => 'method()',
            'expected' => false,
        ];
        yield 'class.method.is-not-visible' => [
            'element' => new class () {
                private function method(): void
                {
                }
            },
            'key' => 'method()',
            'expected' => false,
        ];
    }

    public function testHasAfterChange(): void
    {
        $element = ['foo' => 'string'];
        $wrapper = new Wrapper($element);
        $this->assertSame(false, $wrapper->has('bar'));
        $element['bar'] = 'new';
        $this->assertSame(true, $wrapper->has('bar'));
    }
}
