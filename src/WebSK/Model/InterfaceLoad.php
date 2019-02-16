<?php

namespace WebSK\Model;

/**
 * Interface InterfaceLoad
 * @package WebSK\Model
 * Реализация классом этого интерфейса означает, что класс имеет метод load, который:
 * - принимает один параметр: идентификатор объекта
 * - заполняет поля объекта
 * - возвращает true если все нормально, false - если не получилось загрузить объект (нет в БД и т.п.)
 * Также класс должен иметь метод getId, который возвращает идентификатор объекта в виде строки.
 */
interface InterfaceLoad
{
    public function load($id);

    public function getId();
}
