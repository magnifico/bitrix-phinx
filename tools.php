<?php

/**
 * Fetch a single key from a collection of arrays.
 *
 * Example:
 * <code>
 * $collection = [
 *     ['ID' => 12, 'NAME' => 'Foo'],
 *     ['ID' => 23, 'NAME' => 'Bar'],
 * ];
 * $names = array_pluck($collection, 'NAME'); // ['Foo', 'Bar']
 * </code>
 *
 * @param mixed  $collection
 * @param string $key
 *
 * @return array
 */
if (!function_exists('array_pluck')) {
    function array_pluck($collection, $key)
    {
        $result = [];

        if (!is_iterable($collection)) {
            throw new \RuntimeException('Given for pluck collection must be iterable');
        }

        foreach ($collection as $item) {
            if (isset($item[$key])) {
                $result[] = $item[$key];
            }
        }

        return $result;
    }
}

// check for php 7.1
if (!function_exists('is_iterable')) {
    function is_iterable($it = null) {
        if (is_array($it)) {
            return true;
        }

        if (is_callable($it)) {
            $ref = new \ReflectionFunction($it);
            return $ref->isGenerator();
        }

        if ($it instanceof \Traversable) {
            return true;
        }

        return false;
    }
}
