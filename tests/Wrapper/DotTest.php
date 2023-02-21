<?php

declare(strict_types=1);

namespace WebFu\Tests\Wrapper;

use PHPUnit\Framework\TestCase;
use WebFu\Dot\Dot;
use stdClass;
use WebFu\Dot\InvalidPathException;
use WebFu\Dot\PathNotFoundException;

class DotTest extends TestCase
{
    /**
     * @dataProvider getProvider
     */
    public function testGet(array|object $element, string $path, mixed $expected): void
    {
        $dot = new Dot($element);
        $this->assertEquals($expected, $dot->get($path));
    }

    public function getProvider(): iterable
    {
        yield 'class.scalar' => [
            'element' => new class () {
                public string $scalar = 'scalar';
            },
            'path' => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'class.array' => [
            'element' => new class () {
                /** @var int[] $list */
                public array $list = [0, 1, 2];
            },
            'path' => 'list',
            'expected' => [0, 1, 2],
        ];
        yield 'class.class' => [
            'element' => new class () {
                public object $object;

                public function __construct()
                {
                    $this->object = new stdClass();
                    $this->object->test = 'test';
                }
            },
            'path' => 'object',
            'expected' => (object)['test' => 'test'],
        ];
        yield 'class.complex' => [
            'element' => new class () {
                /** @var object[] $objectList */
                public array $objectList;

                public function __construct()
                {
                    $this->objectList = [
                        new class () {
                            public string $string = 'test';
                        },
                    ];
                }
            },
            'path' => 'objectList.0.string',
            'expected' => 'test',
        ];
        yield 'array.scalar' => [
            'element' => ['scalar' => 'scalar'],
            'path' => 'scalar',
            'expected' => 'scalar',
        ];
        yield 'array.array' => [
            'element' => ['list' => [0, 1, 2]],
            'path' => 'list',
            'expected' => [0, 1, 2],
        ];
        yield 'array.class' => [
            'element' => ['object' => (object)['test' => 'test']],
            'path' => 'object',
            'expected' => (object)['test' => 'test'],
        ];
        yield 'array.complex' => [
            'element' => ['objectList' => [
                new class () {
                    public string $string = 'test';
                },
            ]],
            'path' => 'objectList.0.string',
            'expected' => 'test',
        ];
    }

    public function testGetWithCustomSeparator(): void
    {
        $element = ['foo' => ['bar' => 1]];
        $dot = new Dot($element, '|');
        $this->assertEquals(1, $dot->get('foo|bar'));
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testGetInvalidPath(string $wrongPath): void
    {
        $element = [];
        $dot = new Dot($element);

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage($wrongPath . ' is not a valid path');

        $dot->get($wrongPath);
    }

    public function invalidPathProvider(): iterable
    {
        yield 'starting_with_number' => ['0abc'];
        yield 'illegal_character' => ['\$abc'];
        yield 'unclosed_parenthesis' => ['abc('];
        yield 'ending_with_dot' => ['abc.'];
    }

    public function testGetPathNotFound(): void
    {
        $element = ['exists' => 1];
        $dot = new Dot($element);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('notExists path not found');

        $dot->get('notExists');
    }
}