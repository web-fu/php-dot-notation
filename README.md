PHP Dot Notation
==============================================================================================
[![Latest Stable Version](https://poser.pugx.org/web-fu/php-dot-notation/v)](https://packagist.org/packages/web-fu/php-dot-notation)
[![PHP Version Require](https://poser.pugx.org/web-fu/php-dot-notation/require/php)](https://packagist.org/packages/web-fu/php-dot-notation)
![Test status](https://github.com/web-fu/php-dot-notation/actions/workflows/tests.yaml/badge.svg)
![Static analysis status](https://github.com/web-fu/php-dot-notation/actions/workflows/static-analysis.yml/badge.svg)
![Code style status](https://github.com/web-fu/php-dot-notation/actions/workflows/code-style.yaml/badge.svg)

### A library that allows to access objects and arrays using Dot Notation

This library allows to access objects and arrays using dot notation. 

It also allows getting, setting, and creation of objects and arrays using dot notation.

## Installation
```bash
composer require web-fu/php-dot-notation
```

## Getting and setting values
```php
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

// Accessing an object
$class = new class() {
    public string $property = 'test';
    
    public function method(): string
    {
        return 'foo';
    }
};

$dot = new Dot($class);
echo $dot->get('property'); //test
echo $dot->get('method()'); //foo

// Setting a value in an object
$dot->set('property', 'baz');
echo $class->property; //baz
```

## Creating a new path
```php
$array = [];
$dot = new Dot($array);
$dot->create('foo.baz', 'test');
echo $array['foo']['baz']; // test
echo PHP_EOL; 

$class = new stdClass();
$dot = new Dot($class);
$dot->create('foo', 'test');
echo $class->foo; // test
echo PHP_EOL; 
```

## Unsetting a value in an array or an object
```php
$test = new class {
    public array $array = [
        'foo' => 'bar',
    ];
};

$dot = new Dot($test);
$dot->unset('array.foo');

var_dump(array_key_exists('foo', $test->array)); // false
```

> **Note**: The `init` method check if the path exits before trying to initialize it. 
> 
> The `create` method creates the path if it does not exist, if possible.

## Converting from and to Dot Notation
```php
// Turning an object or an array into the dotified version of it
$array = [
    'foo' => [
        'bar' => 'test',
    ],
];
$dotified = Dot::dotify($array);

echo $dotified['foo.bar']; //test

// Turning a dotified array into a normal array
$normal = Dot::undotify($dotified);

echo $normal['foo']['bar']; //test
```

See `/examples` folder for full examples

## Limitations And Warnings
This tool have some limitations: 

### Getting a method executes the method
```php
$class = new class() {
    public function iDoSomething(): int
    {
        echo 'I Do Something ';
        return 0;
    }
}

$dot = new Dot($class);
echo $dot->get('iDoSomething()'); // I Do Something 0
```

### It's not possible to access private or protected properties
This is a design decision to avoid breaking encapsulation.
```php
$class = new class() {
    private string $property = 'test';
};

$dot = new Dot($class);
echo $dot->get('property'); //Unhandled Exception: WebFu\DotNotation\Exception\PathNotFoundException Path `property` not found
```

### It's not possible to discern if a method returns NULL or does not return at all
This is a limitation of PHP
```php
$class = new class() {
    public function thisMethodReturnsNull(): int|null
    {
        return null;
    }
    public function thisMethodDoesNotReturn(): void
    {
        //do something
    } 
};

$dot = new Dot($class);
var_dump($dot->get('thisMethodReturnsNull()')); //NULL
var_dump($dot->get('thisMethodDoesNotReturn()')); //NULL
```

### It's not possible setting a method
```php
$class = new class() {
    public function method(): int {
        return 0;
    }
};

$dot = new Dot($class);
$dot->set('method()', 20); //Unhandled Exception: WebFu\Proxy\UnsupportedOperationException Cannot set a class method
```
