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

use WebFu\DotNotation\Dot;

require __DIR__.'/../vendor/autoload.php';

$array = [
    'foo' => [
        'bar' => 'test',
    ],
];

// Accessing an array
$dot = new Dot($array);
echo $dot->get('foo.bar').PHP_EOL; // test

// Setting a value in an array
$dot->set('foo.bar', 'baz');
echo $array['foo']['bar'].PHP_EOL; // baz

$class = new class {
    public string $property = 'test';

    public function method(): string
    {
        return 'foo';
    }
};

// Accessing an object
$dot = new Dot($class);
echo $dot->get('property').PHP_EOL; // test
echo $dot->get('method()').PHP_EOL; // foo

// Setting a value in an object
$dot->set('property', 'baz');
echo $class->property.PHP_EOL; // baz

// Accessing a value in object in an array in a object
$test = new class {
    /**
     * @var object[]
     */
    public array $objectList;

    public function __construct()
    {
        $this->objectList = [
            new class {
                public string $string = 'test';
            },
        ];
    }
};

$dot = new Dot($test);
echo $dot->get('objectList.0.string'); // test

$dot->set('objectList.0.string', 'test2');
echo $test->objectList[0]->string.PHP_EOL; // test2
