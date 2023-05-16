<?php

namespace Untek\Core\Code\Helpers;

/**
 * Работа с конфигурацией PHP (php.ini)
 */
class PhpIniHelper
{

    /**
     * Проверяет, включена ли опция конфигурации PHP.
     *
     * @param string $name configuration option name.
     * @return bool option is on.
     */
    public static function isOn($name): bool
    {
        $value = ini_get($name);
        if (empty($value)) {
            return false;
        }
        return ((int)$value === 1 || strtolower($value) === 'on');
    }

    /**
     * Проверяет, отключена ли опция конфигурации PHP.
     *
     * @param string $name configuration option name.
     * @return bool option is off.
     */
    public static function isOff($name): bool
    {
        $value = ini_get($name);
        if (empty($value)) {
            return true;
        }
        return (strtolower($value) === 'off');
    }

    /**
     * Проверяет, пустое ли значение опции конфигурации PHP.
     * 
     * @param $name
     * @return bool
     */
    public static function isEmpty($name): bool
    {
        $value = ini_get($name);
        if (empty($value)) {
            return true;
        }

        return (strlen($value) === 0);
    }

    /**
     * Проверяет, не пустое ли значение опции конфигурации PHP.
     *
     * @param $name
     * @return bool
     */
    public static function isNotEmpty($name): bool
    {
        return !self::isEmpty($name);
    }
}
