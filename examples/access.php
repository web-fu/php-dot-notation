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
echo $dot->get('foo.bar'); // test
echo PHP_EOL;

// Setting a value in an array
$dot->set('foo.bar', 'baz');
echo $array['foo']['bar']; // baz
echo PHP_EOL;

// Accessing an object
$class = new class {
    public string $property = 'test';

    public object $object;

    public function __construct()
    {
        $this->object = new class {
            public string $innerProperty = 'inner-test';

            public function method(): string
            {
                return 'inner-foo';
            }
        };
    }

    public function method(): string
    {
        return 'foo';
    }
};

$dot = new Dot($class);
echo $dot->get('property'); // test
echo PHP_EOL;
echo $dot->get('method()'); // foo
echo PHP_EOL;
echo $dot->get('object.innerProperty'); // inner-test
echo PHP_EOL;
echo $dot->get('object.method()'); // inner-foo
echo PHP_EOL;

// Setting a value in an object
$dot->set('property', 'baz');
echo $class->property; // baz
echo PHP_EOL;

$dot->set('object.innerProperty', 'inner-baz');
echo $class->object->innerProperty; // inner-baz
echo PHP_EOL;

// Creating a new path for an array
$array = [];
$dot   = new Dot($array);
$dot->create('foo.baz', 'test');
echo $array['foo']['baz']; // test
echo PHP_EOL;

// Creating a new path for an object
$class = new stdClass();
$dot   = new Dot($class);
$dot->create('foo.baz', 'test');
echo $class->foo['baz']; // test
echo PHP_EOL;
