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

// Checking if a path is initialized

$test = new class {
    public string $propertyInitialized = 'test';
    public string $propertyNotInitialized;
};

$dot = new Dot($test);
var_dump($dot->isInitialised('propertyInitialized')); // true
echo PHP_EOL;

var_dump($dot->isInitialised('propertyNotInitialized')); // false

// Initializing a property or a value in an array
$test = new class {
    public array $array;
};

$dot = new Dot($test);
$dot->init('array');

var_dump($test->array); // []
echo PHP_EOL;

// Unsetting a value in an array or an object
$test = new class {
    public array $array = [
        'foo' => 'bar',
    ];
};

$dot = new Dot($test);
$dot->unset('array.foo');

var_dump(array_key_exists('foo', $test->array)); // false
