<?php

namespace Untek\Core\Code\Libs;

use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Contract\Common\Exceptions\InternalServerErrorException;
use Untek\Core\Text\Libs\RandomString;

/**
 * Замер производительности в произвольных местах кода.
 */
class Benchmark
{

    private static $data = [];
    private static $sessionId = null;

    /**
     * Начать замер.
     * 
     * @param string $name
     * @param null $data
     */
    public static function begin(string $name, $data = null)
    {
        $microTime = microtime(true);
        if (!self::isEnable()) {
            return;
        }
        $name = self::getName($name);
        $item['name'] = $name;
        $item['begin'] = $microTime;
        $item['data'] = [$data];
        self::append($item);
    }

    /**
     * Закончить замер.
     * 
     * @param string $name
     * @param null $data
     * @throws InternalServerErrorException
     */
    public static function end(string $name, $data = null)
    {
        $microTime = microtime(true);
        if (!self::isEnable()) {
            return;
        }
        $name = self::getName($name);
        if (!isset(self::$data[$name])) {
            return;
        }
        $item = self::$data[$name];
        if (isset($item['end'])) {
            return;
        }

        if (!isset($item['begin'])) {
            throw new InternalServerErrorException('Benchmark not be started!');
        }
        $item['end'] = $microTime;
        if ($data) {
            $item['data'][] = $data;
        }
        self::append($item);
    }

    /**
     * Очистить результат.
     */
    public static function flushAll()
    {
        self::$data = [];
    }

    /**
     * Получить массив точек измерения.
     * 
     * @return array
     */
    public static function all(): array
    {
        return self::$data;
    }

    /**
     * Получить одну точку измерения.
     * 
     * @param string $name
     * @param int $percision
     * @return float|null
     */
    public static function one(string $name, int $percision = 5): ?float
    {
        $all = self::allFlat($percision);
        return ArrayHelper::getValue($all, $name);
    }

    /**
     * Проверяет, сущестует ли точка измерения.
     * 
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        $all = self::allFlat();
        return isset($all[$name]);
    }

    /**
     * Получить массив точек измерения в виде плоского массива.
     * 
     * @param int $percision
     * @return array
     */
    public static function allFlat(int $percision = 5): array
    {
        $durations = ArrayHelper::map(self::$data, 'name', 'duration');
        $durations = array_map(function ($value) use ($percision) {
            return round($value, $percision);
        }, $durations);
        return $durations;
    }

    private static function getName($name)
    {
        if (is_string($name)) {
            return $name;
        }
        $scope = microtime(true) . '_' . serialize($name);
        $hash = hash('md5', $scope);
        return $hash;
    }

    private static function isEnable()
    {
        return true;
        //return EnvService::get('mode.benchmark', false);
    }

    private static function getRequestId()
    {
        if (!self::$sessionId) {
            self::$sessionId = time() . '.' . RandomString::generateNumLowerUpper(8);
        }
        return self::$sessionId;
    }

    private static function append($item)
    {
        $name = $item['name'];
        if (!empty($item['end'])) {
            $item['duration'] = $item['end'] - $item['begin'];
        }
        self::$data[$name] = $item;
        if (!empty($item['duration'])) {
            /*$store = self::getStoreInstance();
            $store->save([
                '_SERVER' => $_SERVER,
                'data' => self::$data,

            ]);*/
        }
    }
}
