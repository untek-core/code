<?php

namespace Untek\Core\Code\Helpers;

/**
 * Работа с типами данных
 */
class TypeHelper
{

    /**
     * Проверяет, является ли значение бинарным.
     * 
     * @param $str
     * @return bool
     */
    public static function isBinary($str): bool
    {
        //return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
        return !ctype_print($str);
    }

    /**
     * Проверяет, является ли значение хэшем Sha1.
     * 
     * @param string $string
     * @return false|int
     */
    public static function isSha1(string $string): bool
    {
        return preg_match('/[0-9a-f]{40}/i', $string);
    }

    /**
     * Проверяет, является ли значение вызываемым.
     * 
     * @param array | string | callable $value
     * @return bool
     */
    public static function isCallable($value): bool
    {
        return $value instanceof \Closure || is_callable($value);
    }
}
