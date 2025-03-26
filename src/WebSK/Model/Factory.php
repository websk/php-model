<?php

namespace WebSK\Model;

use WebSK\Config\ConfWrapper;
use WebSK\Cache\CacheWrapper;
use WebSK\Utils\Assert;

/**
 * Class Factory
 * @package WebSK\Model
 */
class Factory
{
    const DEFAULT_CACHE_TTL_SEC = 60;

    /**
     * @param string $class_name
     * @param int $object_id
     * @return string
     */
    protected static function getObjectCacheId(string $class_name, int $object_id): string
    {
        return $class_name . '::' . $object_id;
    }

    /**
     * @param string $class_name
     * @param int $object_id
     */
    public static function removeObjectFromCache(string $class_name, int $object_id): void
    {
        $cache_key = self::getObjectCacheId($class_name, $object_id);
        CacheWrapper::delete($cache_key);
    }

    /**
     * @param string $class_name
     * @param int $object_id
     * @return mixed|null
     * @throws \Exception
     */
    public static function createAndLoadObject(string $class_name, int $object_id)
    {
        $cache_key = self::getObjectCacheId($class_name, $object_id);

        $cached_obj = CacheWrapper::get($cache_key);

        if ($cached_obj !== false) {
            return $cached_obj;
        }

        $obj = new $class_name;

        $object_is_loaded = call_user_func_array([$obj, "load"], [$object_id]);

        if (!$object_is_loaded) {
            return null;
        }

        $cache_ttl_seconds = (int)ConfWrapper::value('cache.expire', self::DEFAULT_CACHE_TTL_SEC);

        if ($obj instanceof InterfaceCacheTtlSeconds) {
            $cache_ttl_seconds = $obj->getCacheTtlSeconds();
        }

        CacheWrapper::set($cache_key, $obj, $cache_ttl_seconds);

        return $obj;
    }

    /**
     * @param $class_name
     * @param $fields_arr
     * @return mixed|null
     * @throws \Exception
     */
    public static function createAndLoadObjectByFieldsArr(string $class_name, array $fields_arr)
    {
        $obj = new $class_name;

        if (!($obj instanceof InterfaceLoad)) {
            Assert::assert($obj);
        }

        $id_to_load = call_user_func_array([$obj, "getIdByFieldNamesArr"], [$fields_arr]);

        return self::createAndLoadObject($class_name, $id_to_load);
    }
}
