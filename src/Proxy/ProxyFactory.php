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

namespace WebFu\Proxy;

class ProxyFactory
{
    /**
     * @param mixed[]|object $element
     */
    public static function create(array|object &$element): ProxyInterface
    {
        if (is_array($element)) {
            return new ArrayProxy($element);
        }

        return new ClassProxy($element);
    }
}
