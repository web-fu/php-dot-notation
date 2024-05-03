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

namespace WebFu\DotNotation\Tests\Integration;

use PHPUnit\Framework\TestCase;
use stdClass;
use WebFu\DotNotation\Dot;
use WebFu\DotNotation\Exception\InvalidPathException;
use WebFu\DotNotation\Exception\PathNotFoundException;

/**
 * @coversNothing
 */
class DotTest extends TestCase
{
    /**
     * @dataProvider getProvider
     *
     * @param mixed[]|object $element
     */
    public function testGet(array|object $element, string $path, mixed $expected): void
    {
        $dot = new Dot($element);
        $this->assertEquals($expected, $dot->get($path));
    }

    /**
     * @return iterable<array{element: mixed[]|object, path: string, expected: mixed}>
     */
    public function getProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class() {
                public string $scalar = 'scalar';
            },
            'path'     => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'class.array' => [
            'element' => new class() {
                /**
                 * @var int[]
                 */
                public array $list = [0, 1, 2];
            },
            'path'     => 'list',
            'expected' => [0, 1, 2],
        ];
        yield 'class.class' => [
            'element' => new class() {
                public object $object;

                public function __construct()
                {
                    $this->object       = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path'     => 'object',
            'expected' => (object) ['test' => 'test'],
        ];
        yield 'class.method' => [
            'element' => new class() {
                public function method(): int
                {
                    return 1;
                }
            },
            'path'     => 'method()',
            'expected' => 1,
        ];
        yield 'class.complex' => [
            'element' => new class() {
                /**
                 * @var object[]
                 */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class() {
                            public string $string = 'test';
                        },
                    ];
                }
            },
            'path'     => 'objectList.0.string',
            'expected' => 'test',
        ];
        yield 'array.scalar' => [
            'element'  => ['scalar' => 'scalar'],
            'path'     => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'array.array' => [
            'element'  => ['list' => [0, 1, 2]],
            'path'     => 'list',
            'expected' => [0, 1, 2],
        ];
        yield 'array.class' => [
            'element'  => ['object' => (object) ['test' => 'test']],
            'path'     => 'object',
            'expected' => (object) ['test' => 'test'],
        ];
        yield 'array.complex' => [
            'element' => ['objectList' => [
                new class() {
                    public string $string = 'test';
                },
            ]],
            'path'     => 'objectList.0.string',
            'expected' => 'test',
        ];
    }

    public function testGetWithCustomSeparator(): void
    {
        $element = ['foo' => ['bar' => 1]];
        $dot     = new Dot($element, '|');
        $this->assertEquals(1, $dot->get('foo|bar'));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testValidatePath(string $path): void
    {
        $element = [];
        $dot     = new Dot($element);

        $this->expectNotToPerformAssertions();

        $dot->validatePath($path);
    }

    /**
     * @return iterable<string[]>
     */
    public function pathProvider(): iterable
    {
        yield 'numeric_index_path' => ['0'];
        yield 'literal_index_path' => ['foo'];
        yield 'method_path' => ['foo()'];
        yield 'numeric.numeric' => ['0.0'];
        yield 'numeric.literal' => ['0.bar'];
        yield 'numeric.method' => ['0.bar()'];
        yield 'literal.numeric' => ['foo.0'];
        yield 'literal.literal' => ['foo.bar'];
        yield 'literal.method' => ['foo.bar()'];
        yield 'method.numeric' => ['foo().0'];
        yield 'method.literal' => ['foo().bar'];
        yield 'method.method' => ['foo().bar()'];
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testValidatePathInvalid(string $wrongPath): void
    {
        $element = [];
        $dot     = new Dot($element);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage($wrongPath.' is not a valid path');

        $dot->validatePath($wrongPath);
    }

    /**
     * @return iterable<string[]>
     */
    public function invalidPathProvider(): iterable
    {
        yield 'starting_with_number' => ['0abc'];
        yield 'illegal_character' => ['\$abc'];
        yield 'unclosed_parenthesis' => ['abc('];
        yield 'ending_with_dot' => ['abc.'];
    }

    /**
     * @dataProvider missingChildPathProvider
     */
    public function testMissingChildPath(mixed $value, string $type): void
    {
        $element = ['foo' => $value];
        $dot     = new Dot($element);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Element of type '.$type.' has no child element');

        $dot->get('foo.bar');
    }

    /**
     * @return iterable<array{value: mixed, type: string}>
     */
    public function missingChildPathProvider(): iterable
    {
        yield 'bool' => [
            'value' => true,
            'type'  => 'bool',
        ];
        yield 'int' => [
            'value' => 1,
            'type'  => 'int',
        ];
        yield 'float' => [
            'value' => 0.5,
            'type'  => 'float',
        ];
        yield 'string' => [
            'value' => 'baz',
            'type'  => 'string',
        ];
    }

    public function testGetPathNotFound(): void
    {
        $element = ['exists' => 1];
        $dot     = new Dot($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('notExists path not found');

        $dot->get('notExists');
    }
}
