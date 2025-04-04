<?php

namespace WebSK\Model;

/**
 * Для работы с ActiveRecord необходимо:
 *
 * 1. создаем таблицу в БД с полем "id" (auto increment) и прочими нужными полями
 * 2. создаем класс для модели:
 *      - для каждого поля в таблице у класса должно быть свое свойство
 *      - значения по-умолчанию должны соответствовать полям таблицы
 *      - указываемм две константы:
 *          - const DB_ID           - идентификатор БД (news, stats, etc.)
 *          - const DB_TABLE_NAME   - имя таблицы в которой хранятся данные модели
 *      - подключаем трейты:
 *          - ProtectProperties
 *          - ActiveRecord
 *      - пишем необходимые геттеры и сеттеры
 *
 * Сделано трейтом, чтобы:
 * - был нормальный доступ к данным объекта (в т.ч. защищенным)
 * - идешка видела методы ActiveRecord
 * Class ActiveRecord
 * @package WebSK\Model
 */
trait ActiveRecord
{
    /**
     * метод позволяет писать в защищенные свойства (используется, например, в CRUD)
     * @param array $fields_arr
     */
    public function ar_setFields(array $fields_arr): void
    {
        foreach ($fields_arr as $field_name => $field_value) {
            $this->$field_name = $field_value;
        }
    }

    /**
     * @param string $field_name
     * @return mixed
     */
    public function getFieldValueByName(string $field_name): mixed
    {
        return $this->$field_name;
    }

    public function save(): void
    {
        ActiveRecordHelper::saveModelObj($this);

        if (
            ($this instanceof InterfaceLoad) &&
            ($this instanceof InterfaceFactory)
        ) {
            $this::afterUpdate($this->getId());
        }
    }

    /**
     * @param array $field_names_arr
     * @return int|null
     * @throws \Exception
     */
    public function getIdByFieldNamesArr(array $field_names_arr): ?int
    {
        return ActiveRecordHelper::getIdByFieldNamesArr($this, $field_names_arr);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function load(int $id):bool
    {
        return ActiveRecordHelper::loadModelObj($this, $id);
    }

    public function delete()
    {
        $model_class_name = get_class($this);

        // Проверяем связанные данные
        if (isset($model_class_name::$related_models_arr)) {
            foreach ($model_class_name::$related_models_arr as $related_model_class_name => $related_model_data) {
                if (!array_key_exists('link_field', $related_model_data)) {
                    throw new \Exception(
                        'Missing link_field in $related_model_data'
                    );
                }

                $related_field_name = $related_model_data['field_name'];

                $related_ids_arr = $this->$related_field_name;
                if (!empty($related_ids_arr)
                    && array_key_exists('removal_of_banned', $related_model_data)
                    && ($related_model_data['removal_of_banned'] === true)
                ) {
                    return 'Удаление невозможно. Имеются связанные данные ' . $related_model_data['list_title'];
                }

                // Удаляем связанные данные
                $related_ids_arr = $this->$related_field_name;
                foreach ($related_ids_arr as $related_id) {
                    $related_model_obj = Factory::createAndLoadObject($related_model_class_name, $related_id);
                    $related_model_obj->delete();
                }
            }
        }

        if (
            ($this instanceof InterfaceLoad) &&
            ($this instanceof InterfaceFactory)
        ) {
            $check_message = $this->beforeDelete();
            if ($check_message !== true) {
                return $check_message;
            }

            ActiveRecordHelper::deleteModelObj($this);
            $this->afterDelete();
        } else {
            ActiveRecordHelper::deleteModelObj($this);
        }

        return true;
    }
}
