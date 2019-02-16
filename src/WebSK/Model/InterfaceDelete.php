<?php

namespace WebSK\Model;

/**
 * Interface InterfaceDelete
 * @package WebSK\Model
 * Если класс реализует этот интерфейс, то он должен иметь:
 * - Метод delete(), который удаляет данные объекта в базе. Поведение метода при наличии зависимых объектов пока не регламентировано.
 */
interface InterfaceDelete
{
    public function delete();
}