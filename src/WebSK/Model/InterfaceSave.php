<?php

namespace WebSK\Model;

/**
 * Interface InterfaceSave
 * @package WebSK\Model
 * Если класс реализует этот интерфейс, то он должен иметь:
 * - Метод save(), который сохраняет данные объекта. Если объекта нет в базе - он должен создавать и его id должен заполняться
 * правильным значением.
 */
interface InterfaceSave
{
    public function save();
}