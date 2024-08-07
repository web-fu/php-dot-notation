PHP Dot Notation
==============================================================================================
[![Latest Stable Version](https://poser.pugx.org/web-fu/php-dot-notation/v)](https://packagist.org/packages/web-fu/php-dot-notation)
[![PHP Version Require](https://poser.pugx.org/web-fu/php-dot-notation/require/php)](https://packagist.org/packages/web-fu/php-dot-notation)
![Test status](https://github.com/web-fu/php-dot-notation/actions/workflows/tests.yaml/badge.svg)
![Static analysis status](https://github.com/web-fu/php-dot-notation/actions/workflows/static-analysis.yml/badge.svg)
![Code style status](https://github.com/web-fu/php-dot-notation/actions/workflows/code-style.yaml/badge.svg)

### A library that allows to access objects and arrays

Library that allows to access array and object with strong type support using Javascript-like Dot Notation

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

$class = new class() {
    public string $property = 'test';
    
    public function method(): string
    {
        return 'foo';
    }
}

// Accessing an object
$dot = new Dot($class);
echo $dot->get('property'); //test
echo $dot->get('method()'); //foo

// Setting a value in an object
$dot->set('property', 'baz');
echo $class->property; //baz
```

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

## Reflection support
Reflection support is provided by my reflection library: https://github.com/web-fu/reflection

```php
$class = new class() {
    public string $property = 'test';
    
    public function method(): string
    {
        return 'foo';
    }
};

$dot = new Dot($class);

$propertyReflectionType = $dot->getReflectionType('property')->getTypeNames(); // ['string']
$methodReturnReflectionType = $dot->getReflectionType('method()')->getTypeNames(); // ['string']

$array = [
    'foo' => [
        'bar' => 'test',
    ],
];

$dot = new Dot($array);
$indexReflectionType = $dot->getReflectionType('foo.bar')->getTypeNames(); // ['string']
```

See `/examples` folder for full examples

## Limitations
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
echo $dot->get('iDoSomething'); // I Do Something 0
```

### It's not possible to discern if a method returns NULL or does not return at all
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
}

$dot = new Dot($class);
var_dump($dot->get('thisMethodReturnsNull')); //NULL
var_dump($dot->get('thisMethodDoesNotReturn')); //NULL
```

### It's not possible setting a method
```php
$class = new class() {
    public function method(): int {
        return 0;
    }
}

$dot = new Dot($class);
$dot->set('method()', 20); //Unhandled Exception: WebFu\Proxy\UnsupportedOperationException Cannot set a class method

```
