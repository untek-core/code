<?php

namespace Untek\Core\Code\Helpers;

use Untek\Core\FileSystem\Helpers\FilePathHelper;

class PhpHelper
{

    /**
     * Проверяет, зарезервированно ли имя в PHP.
     *
     * @param string $name
     * @return bool
     */
    public static function isReservedName(string $name)
    {
        $keywords = array(
            '__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class',
            'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty',
            'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends',
            'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once',
            'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private',
            'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try',
            'unset', 'use', 'var', 'while', 'xor');

        return in_array($name, $keywords);
    }

    /**
     * Подключить PHP-файлы из директории.
     *
     * @param string $directory Путь до директории
     * @param bool $isRecursive Рекурсивно
     */
    public static function requireFromDirectory(string $directory, bool $isRecursive = false)
    {
        $directory = rtrim($directory, '/');
        $libs = FindFileHelper::scanDir($directory);
        foreach ($libs as $lib) {
            $path = $directory . '/' . $lib;
            if (is_file($path)) {
                if (is_file($path) && FilePathHelper::fileExt($lib) == 'php') {
                    require_once $path;
                }
            } elseif (is_dir($path)) {
                self::requireFromDirectory($path, $isRecursive);
            }
        }
    }

    /**
     * Проверяет, доступно ли данное расширение PHP и соответствует ли его версия заданной.
     *
     * @param string $extensionName PHP extension name.
     * @param string $version required PHP extension version.
     * @param string $compare comparison operator, by default '>='
     * @return bool if PHP extension version matches.
     */
    public static function checkPhpExtensionVersion($extensionName, $version, $compare = '>='): bool
    {
        if (!extension_loaded($extensionName)) {
            return false;
        }
        $extensionVersion = phpversion($extensionName);
        if (empty($extensionVersion)) {
            return false;
        }
        if (strncasecmp($extensionVersion, 'PECL-', 5) === 0) {
            $extensionVersion = substr($extensionVersion, 5);
        }

        return version_compare($extensionVersion, $version, $compare);
    }

    public static function runValue($value, $params = [])
    {
        if (TypeHelper::isCallable($value)) {
            $value = call_user_func_array($value, $params);
        }
        return $value;
    }

    public static function isValidName($name)
    {
        if (!is_string($name)) {
            return false;
        }
        // todo: /^[\w]{1}[\w\d_]+$/i
        return preg_match('/([a-zA-Z0-9_]+)/', $name);
    }
}
