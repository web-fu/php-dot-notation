<?php

declare(strict_types=1);

use WebFu\DotNotation\Dot;

require __DIR__.'/../vendor/autoload.php';

$array = [
    'foo' => [
        'bar' => 'test',
    ],
];

// Accessing an array
$dot = new Dot($array);
echo $dot->get('foo.bar'); //test

// Setting a value in an array
$dot->set('foo.bar', 'baz');
echo $array['foo']['bar']; //baz

$class = new class() {
    public string $property = 'test';

    public function method(): string
    {
        return 'foo';
    }
};

// Accessing an object
$dot = new Dot($class);
echo $dot->get('property'); //test
echo $dot->get('method()'); //foo