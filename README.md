# PHP Dot Notation
### A library that allows to access objects and arrays

Library that allows to access array and object with strong type support using Javascript-like Dot Notation

## Note
This library is an Alpha version and should not be used in a production environment.

## Examples
```php
$array = [
    'foo' => [
        'bar' => 'test',
    ],
];

$dot = new Dot($array);
echo $dot->get('foo.bar'); //test

$dot->set('foo.bar', 'baz');
echo $array['foo']['bar']; //baz

$class = new class() {
    public string $property = 'test';
    
    public function method(): string
    {
        return 'foo';
    }
}

$dot = new Dot($class);
echo $dot->get('property'); //test
echo $dot->get('method()'); //foo

$dot->set('property', 'baz');
echo $class->property; //baz
```

## Limitations and Warnings
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