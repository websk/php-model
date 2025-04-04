<?php

namespace WebSK\Model;

/**
 * Class Helper
 * @package WebSK\Model
 */
class Helper
{
    /**
     * Глобализация имен классов не является абсолютно необходимой,
     * но в большом проекте проще и безопаснее всегда использовать глобальные имена классов.
     * Пых всегда возвращает имена классов полные (со всеми неймспейсами), но не глобальные (без \ в начале).
     * @param string $class_name
     * @return string
     */
    public static function globalizeClassName(string $class_name): string
    {
        if (!preg_match("@^\\\\@", $class_name)) { // если в начале имени класса нет слэша - добавляем
            $class_name = '\\' . $class_name;
        }

        return $class_name;
    }

    /**
     * @param string $class_name Принимает как глобальное, так и неглобальное имя класса.
     * @param string $interface_class_name Имя интерфейса, обязательно не глобальное!
     * @throws \Exception
     */
    public static function exceptionIfClassNotImplementsInterface(string $class_name, string $interface_class_name): void
    {
        $global_class_name = self::globalizeClassName($class_name);

        $model_class_interfaces_arr = class_implements($global_class_name);

        if (!array_key_exists($interface_class_name, $model_class_interfaces_arr)) {
            throw new \Exception('model class ' . $class_name . ' does not implement ' . $interface_class_name);
        }
    }
}
