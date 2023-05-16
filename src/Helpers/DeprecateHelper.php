<?php

namespace Untek\Core\Code\Helpers;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Untek\Core\Code\Exceptions\DeprecatedException;
use Untek\Core\Container\Helpers\ContainerHelper;

/**
 * Работа с устаревшим кодом
 */
class DeprecateHelper
{

    /**
     * Строгий режим устаревания
     * @var bool
     */
    private static $isStrictMode = false;

    /**
     * Мягкое устаревание
     *
     * При включенном строгом режиме устаревания вызывает исключение.
     * При отключенном строгом режиме устаревания ничего не происходит.
     * Метод служит для мягкого перехода между версиями
     * @param string $message
     * @throws DeprecatedException
     */
    public static function softThrow(string $message = ''): void
    {
        if (self::isStrictMode()) {
            self::hardThrow($message);
        }
    }

    /**
     * Строгое устаревание
     *
     * Всегда вызывает исключение.
     * @param string $message
     * @throws DeprecatedException
     */
    public static function hardThrow(string $message = ''): void
    {
        self::log($message, debug_backtrace(), \Monolog\Logger::CRITICAL);
        throw new DeprecatedException('Deprecated: ' . $message);
    }

    /**
     * Проверяет, включен ли строгий режим устаревания.
     * 
     * @return bool
     */
    public static function isStrictMode(): bool
    {
        return self::getStrictMode() == true;
    }

    /**
     * Установить строгий режим устаревания.
     * 
     * @param bool $value
     */
    public static function setStrictMode(bool $value = true): void
    {
        self::$isStrictMode = $value;
    }

    private static function getStrictMode(): bool
    {
        return getenv('DEPRECATE_STRICT_MODE') ?: self::$isStrictMode;
    }

    private static function log(string $message = '', $trace = [], $level = \Monolog\Logger::NOTICE)
    {
        $container = ContainerHelper::getContainer();
        if (!$container instanceof ContainerInterface) {
            return;
        }
        if (!$container->has(LoggerInterface::class)) {
            return;
        }
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);
        $noticeMessage = 'Deprecated';
        if ($message) {
            $noticeMessage .= ': ' . $message;
        }

        $logger->log($level, $noticeMessage, [
            'message' => $message,
            'trace' => $trace,
        ]);
        /*$logger->notice($noticeMessage, [
            'message' => $message,
            'trace' => $trace,
        ]);*/
    }
}