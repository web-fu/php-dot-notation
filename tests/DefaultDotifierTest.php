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
use WebFu\DotNotation\Tests\TestData\ClassWithJsonSerialize;
use WebFu\DotNotation\Tests\TestData\LocationDto;
use WebFu\DotNotation\Tests\TestData\PersonDto;
use WebFu\DotNotation\Tests\TestData\SimpleClass;

/**
 * @coversNothing
 */
class DefaultDotifierTest extends TestCase
{
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
        $dotifier = new DefaultDotifier();
        $actual   = $dotifier->dotify($element);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return iterable<array{element: mixed[]|object}>
     */
    public function elementProvider(): iterable
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
     * @covers ::undotify
     */
    public function testUndotify(): void
    {
        $undotifier = new DefaultDotifier();

        $arrayDotified = [
            'foo'      => 'bar',
            'baz.qux'  => 'quux',
            'baz.quuz' => 'corge',
        ];

        $array = $undotifier->undotify($arrayDotified);

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

        $array = $undotifier->undotify($arrayWithNumericIndex);

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
}
