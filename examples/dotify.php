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

use WebFu\DotNotation\DefaultDotifier;

require __DIR__.'/../vendor/autoload.php';

// Turning an object or an array into the dotified version of it
$array = [
    'foo' => [
        'bar' => 'test',
    ],
];
$dotified = (new DefaultDotifier())->dotify($array);

echo $dotified['foo.bar']; // test
echo PHP_EOL;

// Turning a dotified array into a normal array
$normal = (new DefaultDotifier())->undotify($dotified);

echo $normal['foo']['bar']; // test
echo PHP_EOL;

// Turning a dotified array into an object
class SimpleClass
{
    public string $public;
}

class ComplexClass
{
    public SimpleClass $simple;
}

$array = [
    'simple.public' => 'test',
];
$object = (new DefaultDotifier())->undotify($array, ComplexClass::class);
echo $object->simple->public;
echo PHP_EOL;
