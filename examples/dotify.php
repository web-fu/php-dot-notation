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

// Turning an object or an array into the dotified version of it
$array = [
    'foo' => [
        'bar' => 'test',
    ],
];
$dotified = Dot::dotify($array);

echo $dotified['foo.bar'].PHP_EOL; // test

$array = Dot::undotify($dotified);

var_dump($array);
