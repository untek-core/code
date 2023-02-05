<?php

namespace Untek\Core\Code\Helpers;

use Composer\Autoload\ClassLoader;
use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Code\Exceptions\NotFoundDependencyException;

/**
 * Работа с Composer
 */
class ComposerHelper
{

    private static $composerVendorClassLoader;

    /**
     * Получить загрузчик классов
     * 
     * @return ClassLoader
     */
    public static function getComposerVendorClassLoader(): ClassLoader
    {
        if (!self::$composerVendorClassLoader) {
            $loaders = ClassLoader::getRegisteredLoaders();
            $vendorDir = realpath(__DIR__ . '/../../../../../../../vendor');
            self::$composerVendorClassLoader = $loaders[$vendorDir];
        }
        return self::$composerVendorClassLoader;
    }

    /**
     * Требовать установку composer-пакета
     * 
     * @param string $className
     * @param string $packageName
     * @param string|null $version
     * @throws NotFoundDependencyException
     * @example ComposerHelper::requireAssert(Untek\Group\Package\Class::class, 'zngroup/package', 'v1.23.45');
     */
    public static function requireAssert(string $className, string $packageName, string $version = null): void
    {
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            $package = $packageName;
            if (!empty($version)) {
                $package .= ":$version";
            }
            $message = "Class \"$className\" not exists!\n";
            $message .= "\"$packageName\" package not loaded! \nRun the command: \"composer require $package\"";
            throw new NotFoundDependencyException($message);
        }
    }

    /**
     * Зарегистрировать пространство имен
     * @param string $namespace
     * @param string $path
     * @example ComposerHelper::register('App', __DIR__ . '/../src');
     */
    public static function register(string $namespace, string $path): void
    {
        self::getComposerVendorClassLoader()->addPsr4($namespace . '\\', $path);
    }

    /**
     * Получить имя директории из namespace
     * @param $path
     * @return false|string
     */
    public static function getPsr4Path($path)
    {
        $path = str_replace('/', '\\', $path);
        $paths = self::find($path);
        $resPath = ArrayHelper::first($paths);
/*if(is_null($resPath)) {
//    dump($path, $paths);
    return null;
}*/
        $resPath = str_replace('\\', '/', $resPath);

        return realpath($resPath);
    }

    private static function find($path): array
    {
        $pathItems = explode('\\', $path);
        $paths = [];
        $prependPath = '';
        $autoloadPsr4 = self::getComposerVendorClassLoader()->getPrefixesPsr4();
        for ($i = 0; $i <= count($pathItems) - 1; $i++) {
            $prependPath .= $pathItems[$i] . '\\';
            unset($pathItems[$i]);
            $dirs = ArrayHelper::getValue($autoloadPsr4, $prependPath);
            if ($dirs) {
                foreach ($dirs as $dir) {
                    $relativeDir = implode('\\', $pathItems);
                    $path = trim($dir . '\\' . $relativeDir, '\\');
                    $absolutPath = $prependPath . $relativeDir;
                    $paths[$absolutPath] = $path;
                }
            }
        }
        return $paths;
    }
}
