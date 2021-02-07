<?php

namespace WebSK\Model;

/**
 * Поддержка классом этого интерфейса означает, что класс умеет создавать свои экземпляры, кэшировать их и сбрасывать кэш при изменениях.
 * Базовая реализация есть в трейте FactoryTrait.
 * Interface InterfaceFactory
 * @package WebSK\Model
 */
interface InterfaceFactory
{

    public static function factory(int $id_to_load, bool $exception_if_not_loaded = true);

    public static function factoryByFieldsArr(array $fields_arr, bool $exception_if_not_loaded = true);

    public static function getMyGlobalizedClassName(): string;

    public static function removeObjFromCacheById(int $id_to_remove);

    public static function afterUpdate(int $id);

    public function beforeDelete();

    public function afterDelete();
}