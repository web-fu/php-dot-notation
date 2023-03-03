<?php

declare(strict_types=1);

namespace WebFu\Tests\Proxy;

use PHPUnit\Framework\TestCase;
use WebFu\Tests\Fixture\ChildClass;
use WebFu\Proxy\ClassProxy;
use WebFu\Proxy\UnsupportedOperationException;

class ClassProxyTest extends TestCase
{
    /**
     * @dataProvider hasDataProvider
     */
    public function testHas(object $element, string $key, bool $expected): void
    {
        $wrapper = new ClassProxy($element);
        $this->assertSame($expected, $wrapper->has($key));
    }

    /**
     * @return iterable<mixed[]>
     */
    public function hasDataProvider(): iterable
    {
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
                /** @phpstan-ignore-next-line  */
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
                /** @phpstan-ignore-next-line  */
                private function method(): void
                {
                }
            },
            'key' => 'method()',
            'expected' => false,
        ];
    }

    public function testGetKeys(): void
    {
        $class = new ChildClass();
        $wrapper = new ClassProxy($class);

        $this->assertSame([
            'public',
            'publicParent',
            'publicTrait',
            'public()',
            'publicParent()',
            'publicTrait()',
        ], $wrapper->getKeys());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(object $element, string $key, mixed $expected): void
    {
        $wrapper = new ClassProxy($element);
        $this->assertSame($expected, $wrapper->get($key));
    }

    /**
     * @return iterable<array{element: object, key: string, expected: mixed}>
     */
    public function getDataProvider(): iterable
    {
        yield 'class.property' => [
            'element' => new class () {
                public string $property = 'foo';
            },
            'key' => 'property',
            'expected' => 'foo',
        ];
        yield 'class.method' => [
            'element' => new class () {
                public function method(): string
                {
                    return 'foo';
                }
            },
            'key' => 'method()',
            'expected' => 'foo',
        ];
    }

    public function testSet(): void
    {
        $element = new class () {
            public string $property = 'foo';
        };

        $wrapper = new ClassProxy($element);
        $wrapper->set('property', 'bar');

        $this->assertSame('bar', $element->property);
    }

    public function testSetFailIfKeyIsMethod(): void
    {
        $element = new class () {
            public function method(): void
            {
            }
        };

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot set a class method');

        $wrapper = new ClassProxy($element);
        $wrapper->set('method()', 'bar');
    }
}
