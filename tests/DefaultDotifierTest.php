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
use WebFu\DotNotation\DefaultDotifier;
use WebFu\DotNotation\Exception\NotDotifiableValueException;
use WebFu\DotNotation\Exception\NotUndotifiableValueException;
use WebFu\DotNotation\Exception\UnsupportedOperationException;
use WebFu\DotNotation\Tests\TestData\ClassWithJsonSerialize;
use WebFu\DotNotation\Tests\TestData\IterableClass;
use WebFu\DotNotation\Tests\TestData\LocationDto;
use WebFu\DotNotation\Tests\TestData\PersonDto;
use WebFu\DotNotation\Tests\TestData\SimpleClass;

/**
 * @coversDefaultClass \WebFu\DotNotation\DefaultDotifier
 */
class DefaultDotifierTest extends TestCase
{
    /**
     * @covers ::dotify
     *
     * @dataProvider dotifyProvider
     *
     * @param mixed[]|object $element
     * @param mixed[]        $expected
     */
    public function testDotify(array|object $element, array $expected): void
    {
        $dotifier = new DefaultDotifier();
        $actual   = $dotifier->dotify($element);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return iterable<array{element: mixed[]|object}>
     */
    public function dotifyProvider(): iterable
    {
        $personDto                     = new PersonDto();
        $personDto->firstName          = 'John';
        $personDto->lastName           = 'Doe';
        $personDto->birthdate          = '2021-01-01';
        $personDto->location           = new LocationDto();
        $personDto->location->city     = 'New York';
        $personDto->location->province = 'NY';
        $personDto->location->country  = 'USA';

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
        yield 'SimpleClass' => [
            'element'  => new SimpleClass(),
            'expected' => [
            ],
        ];
        yield 'ClassWithJsonSerialize' => [
            'element'  => new ClassWithJsonSerialize(),
            'expected' => [
                'foo' => 'bar',
            ],
        ];
        yield 'dto' => [
            'element'  => $personDto,
            'expected' => [
                'firstName'         => 'John',
                'lastName'          => 'Doe',
                'birthdate'         => '2021-01-01',
                'location.city'     => 'New York',
                'location.province' => 'NY',
                'location.country'  => 'USA',
            ],
        ];
    }

    /**
     * @covers ::dotify
     *
     * @dataProvider dotifyFailsProvider
     */
    public function testDotifyFailsIfNotDotifiable(mixed $element, string $message): void
    {
        $this->expectException(NotDotifiableValueException::class);
        $this->expectExceptionMessage($message);

        $dotifier = new DefaultDotifier();
        /* @phpstan-ignore-next-line */
        $dotifier->dotify($element);
    }

    /**
     * @return iterable<array{element: mixed, message: string}>
     */
    public function dotifyFailsProvider(): iterable
    {
        yield 'string' => [
            'element' => 'not dotifiable',
            'message' => 'Value of type string cannot be dotified',
        ];
        yield 'int' => [
            'element' => 123,
            'message' => 'Value of type integer cannot be dotified',
        ];
        yield 'float' => [
            'element' => 123.45,
            'message' => 'Value of type double cannot be dotified',
        ];
        yield 'null' => [
            'element' => null,
            'message' => 'Value of type NULL cannot be dotified',
        ];
    }

    /**
     * @covers ::undotify
     *
     * @dataProvider undotifyProvider
     */
    public function testUndotify(mixed $element, string $type, mixed $expected): void
    {
        $undotifier = new DefaultDotifier();
        $actual     = $undotifier->undotify($element, $type);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return iterable<array{element: mixed, type: string, expected: mixed}>
     */
    public function undotifyProvider(): iterable
    {
        $expectedObject         = new SimpleClass();
        $expectedObject->public = 'test';

        $iterable = function (): iterable {
            yield 'foo' => 'bar';
            yield 'baz.qux' => 'quux';
            yield 'baz.quuz' => 'corge';
        };

        yield 'array' => [
            'element' => [
                'foo'      => 'bar',
                'baz.qux'  => 'quux',
                'baz.quuz' => 'corge',
            ],
            'type'     => 'array',
            'expected' => [
                'foo' => 'bar',
                'baz' => [
                    'qux'  => 'quux',
                    'quuz' => 'corge',
                ],
            ],
        ];
        yield 'array_with_numeric_index' => [
            'element' => [
                'foo'        => 'bar',
                'baz.0.qux'  => 'quux',
                'baz.0.quuz' => 'corge',
                'baz.1.qux'  => 'abc',
                'baz.1.quuz' => 'def',
            ],
            'type'     => 'array',
            'expected' => [
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
            ],
        ];
        yield 'SimpleClass' => [
            'element' => [
                'public' => 'test',
            ],
            'type'     => SimpleClass::class,
            'expected' => $expectedObject,
        ];
        yield 'iterable' => [
            'element'  => $iterable(),
            'type'     => 'array',
            'expected' => [
                'foo' => 'bar',
                'baz' => [
                    'qux'  => 'quux',
                    'quuz' => 'corge',
                ],
            ],
        ];
    }

    /**
     * @covers ::undotify
     *
     * @dataProvider undotifyFailsProvider
     */
    public function testUndotifyFailsIfNotUndotifiable(mixed $element, string $message): void
    {
        $this->expectException(NotUndotifiableValueException::class);
        $this->expectExceptionMessage($message);

        $dotifier = new DefaultDotifier();
        $dotifier->undotify($element);
    }

    /**
     * @return iterable<array{element: mixed, message: string}>
     */
    public function undotifyFailsProvider(): iterable
    {
        yield 'string' => [
            'element' => 'not undotifiable',
            'message' => 'Value of type string cannot be undotified',
        ];
        yield 'int' => [
            'element' => 123,
            'message' => 'Value of type integer cannot be undotified',
        ];
        yield 'float' => [
            'element' => 123.45,
            'message' => 'Value of type double cannot be undotified',
        ];
        yield 'null' => [
            'element' => null,
            'message' => 'Value of type NULL cannot be undotified',
        ];
    }

    public function testUndotifyWithInvalidType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('`InvalidType` is not a valid class name');

        $dotifier = new DefaultDotifier();
        $dotifier->undotify([], 'InvalidType');
    }

    /**
     * @covers ::supportsDotification
     *
     * @dataProvider dotificationProvider
     */
    public function testSupportsDotification(mixed $element, bool $expected): void
    {
        $dotifier = new DefaultDotifier();

        $this->assertEquals($expected, $dotifier->supportsDotification($element));
    }

    /**
     * @return iterable<array{element: mixed, expected: bool}>
     */
    public function dotificationProvider(): iterable
    {
        yield 'array' => [
            'element'  => [],
            'expected' => true,
        ];
        yield 'object' => [
            'element'  => (object) [],
            'expected' => true,
        ];
        yield 'string' => [
            'element'  => 'string',
            'expected' => false,
        ];
        yield 'int' => [
            'element'  => 123,
            'expected' => false,
        ];
    }

    /**
     * @covers ::supportsUndotification
     *
     * @dataProvider undotificationProvider
     */
    public function testSupportsUndotification(mixed $element, bool $expected): void
    {
        $dotifier = new DefaultDotifier();

        $this->assertEquals($expected, $dotifier->supportsUndotification($element));
    }

    /**
     * @return iterable<array{element: mixed, expected: bool}>
     */
    public function undotificationProvider(): iterable
    {
        $iterable = function (): iterable {
            yield 'foo' => 'bar';
            yield 'baz.qux' => 'quux';
            yield 'baz.quuz' => 'corge';
        };

        yield 'array' => [
            'element'  => [],
            'expected' => true,
        ];
        yield 'iterable-object' => [
            'element'  => new IterableClass(),
            'expected' => true,
        ];
        yield 'iterable-closure' => [
            'element'  => $iterable(),
            'expected' => true,
        ];
        yield 'string' => [
            'element'  => 'string',
            'expected' => false,
        ];
        yield 'int' => [
            'element'  => 123,
            'expected' => false,
        ];
    }
}
