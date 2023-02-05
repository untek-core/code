<?php

namespace Untek\Core\Code\Helpers;

use Throwable;
use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Text\Helpers\Inflector;
use Untek\Core\Code\Factories\PropertyAccess;

/**
 * Работа с атрибутами класса
 */
class PropertyHelper
{

    /**
     * Получить значение атрибута.
     *
     * @param object $entity
     * @param string $attribute
     * @param mixed | null $defaultValue
     * @return mixed
     */
    public static function getValue(object $entity, string $attribute, mixed $defaultValue = null): mixed
    {
        if(is_array($entity)) {
            return ArrayHelper::getValue($entity, $attribute);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        try {
            return $propertyAccessor->getValue($entity, $attribute);
        } catch (Throwable $e) {
            // todo: логировать ошибки доступа к атрибутам
            return $defaultValue;
        }
    }

    /**
     * Установить значение атрибута.
     * 
     * @param object $entity
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function setValue(object $entity, string $name, mixed $value): void
    {
        if(is_array($entity)) {
            ArrayHelper::set($entity, $name, $value);
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyAccessor->setValue($entity, $name, $value);
    }

    /**
     * Назначить массив атрибутов.
     * 
     * @param object $entity
     * @param object | array $data
     * @param array $filedsOnly
     */
    public static function setAttributes(object $entity, object | array $data, array $filedsOnly = []): void
    {
        if (empty($data)) {
            return;
        }

        if(is_array($entity)) {
            $data = ArrayHelper::only($data);
            $entity = ArrayHelper::merge($entity, $data);
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($data as $name => $value) {
            $name = Inflector::variablize($name);
            $isAllow = empty($filedsOnly) || in_array($name, $filedsOnly);
            if ($isAllow) {
                $isWritable = $propertyAccessor->isWritable($entity, $name);
                if ($isWritable) {
                    $propertyAccessor->setValue($entity, $name, $value);
                }
            }
        }
    }

    /**
     * Проверяет, доступен ли атрибут для записи.
     * 
     * @param object $entity
     * @param string $name
     * @return bool
     */
    public static function isWritableAttribute(object $entity, string $name): bool
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        return $propertyAccessor->isWritable($entity, $name);
    }

    /**
     * Проверяет, доступен ли атрибут для чтения.
     * 
     * @param object $entity
     * @param string $name
     * @return bool
     */
    public static function isReadableAttribute(object $entity, string $name): bool
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        return $propertyAccessor->isReadable($entity, $name);
    }
}
