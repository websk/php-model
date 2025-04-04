<?php
/**
 * Базовая реализация интерфейса \WebSK\Model\InterfaceFactory
 */

namespace WebSK\Model;

/**
 * Trait FactoryTrait
 * @package WebSK\Model
 */
trait FactoryTrait
{
    /**
     * Возвращает глобализованное имя класса модели.
     * @return string
     */
    public static function getMyGlobalizedClassName(): string
    {
        $class_name = get_called_class(); // "Gets the name of the class the static method is called in."
        $class_name = Helper::globalizeClassName($class_name);

        return $class_name;
    }

    /**
     * Базовая загрузка объекта по Id
     * @param int $id_to_load
     * @param bool|true $exception_if_not_loaded
     * @return $this
     */
    public static function factory(int $id_to_load, bool $exception_if_not_loaded = true)
    {
        $class_name = self::getMyGlobalizedClassName();
        $obj = Factory::createAndLoadObject($class_name, $id_to_load);

        if ($exception_if_not_loaded) {
            if (!$obj) {
                throw new \Exception(
                     'Object is not loaded'
                );
            }
        }

        return $obj;
    }

    /**
     * Загрузка объекта по набору полей
     * @param array $fields_arr - array($field_name => $field_value)
     * @param bool $exception_if_not_loaded
     * @return $this
     */
    public static function factoryByFieldsArr(array $fields_arr, bool $exception_if_not_loaded = true)
    {
        $class_name = self::getMyGlobalizedClassName();
        $obj = Factory::createAndLoadObjectByFieldsArr($class_name, $fields_arr);

        if ($exception_if_not_loaded) {
            if (!$obj) {
                throw new \Exception(
                    'Object is not loaded'
                );
            }
        }

        return $obj;
    }

    /**
     * @param int $id_to_remove
     */
    public static function removeObjFromCacheById(int $id_to_remove)
    {
        $class_name = self::getMyGlobalizedClassName();
        Factory::removeObjectFromCache($class_name, $id_to_remove);
    }

    /**
     * Базовая обработка изменения.
     * Если на это событие есть подписчики - нужно переопределить обработчик в самом классе и там eventmanager::invoke, где уже подписать остальных подписчиков.
     * сделано статиками чтобы можно было вызывать для других объектов не создавая, только по id.
     * @param int $id
     */
    public static function afterUpdate(int $id)
    {
        $model_class_name = self::getMyGlobalizedClassName();

        if (isset($model_class_name::$depends_on_models_arr)) {
            foreach ($model_class_name::$depends_on_models_arr as $depends_model_class_name => $depends_model_data) {
                if (!array_key_exists('link_field', $depends_model_data)) {
                    throw new \Exception(
                        'Missing link_field in $depends_model_data'
                    );
                }

                $model_obj = Factory::createAndLoadObject($model_class_name, $id);

                $reflect = new \ReflectionClass($model_obj);
                $property_obj = $reflect->getProperty($depends_model_data['link_field']);

                $depends_id = $property_obj->getValue($model_obj);

                $depends_model_class_name::afterUpdate($depends_id);
            }
        }

        self::removeObjFromCacheById($id);
    }

    /**
     * @return bool
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Метод чистки после удаления объекта.
     * Поскольку модели уже нет в базе, этот метод должен использовать только данные объекта в памяти:
     * - не вызывать фабрику для этого объекта
     * - не использовать геттеры (они могут обращаться к базе)
     * - не быть статическим: работает в контексте конкретного объекта
     */
    public function afterDelete()
    {
        $model_class_name = self::getMyGlobalizedClassName();

        if (isset($model_class_name::$depends_on_models_arr)) {
            foreach ($model_class_name::$depends_on_models_arr as $depends_model_class_name => $depends_model_data) {
                if (!array_key_exists('link_field', $depends_model_data)) {
                    throw new \Exception(
                        'Missing link_field in $depends_model_data'
                    );
                }

                $reflect = new \ReflectionClass($this);
                $property_obj = $reflect->getProperty($depends_model_data['link_field']);

                $depends_id = $property_obj->getValue($this);

                $depends_model_class_name::afterUpdate($depends_id);
            }
        }

        self::removeObjFromCacheById($this->id);
    }
}
