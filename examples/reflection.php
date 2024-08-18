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

$class = new class {
    public string $property = 'test';

    public function method(): string
    {
        return 'foo';
    }
};

$dot = new Dot($class);

$propertyReflectionType     = $dot->getReflectionType('property')->getTypeNames(); // ['string']
$methodReturnReflectionType = $dot->getReflectionType('method()')->getTypeNames(); // ['string']

$array = [
    'foo' => [
        'bar' => 'test',
    ],
];

$dot                 = new Dot($array);
$indexReflectionType = $dot->getReflectionType('foo.bar')->getTypeNames(); // ['string']
