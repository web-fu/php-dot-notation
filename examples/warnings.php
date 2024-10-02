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
    private string $property = 'test';
};

$dot = new Dot($class);

try {
    echo $dot->get('property');
} catch (Throwable $e) {
    echo $e->getMessage(); // Path `property` not found
}
echo PHP_EOL;

$class = new class {
    public function iDoSomething(): int
    {
        echo 'I Do Something ';

        return 0;
    }
};

$dot = new Dot($class);
echo $dot->get('iDoSomething()'); // I Do Something 0
echo PHP_EOL;

$class = new class {
    public function thisMethodReturnsNull(): int|null
    {
        return null;
    }

    public function thisMethodDoesNotReturn(): void
    {
        // do something
    }
};

$dot = new Dot($class);
var_dump($dot->get('thisMethodReturnsNull()')); // NULL
var_dump($dot->get('thisMethodDoesNotReturn()')); // NULL

$class = new class {
    public function method(): int
    {
        return 0;
    }
};

$dot = new Dot($class);
try {
    $dot->set('method()', 20);
} catch (Throwable $e) {
    echo $e->getMessage(); // Cannot set a class method
}
echo PHP_EOL;
