<?php

namespace WebSK\Model;

/**
 * Interface InterfaceGetTitle
 * @package WebSK\Model
 * Возвращает экранное, человекочитаемое имя модели, которое можно выводить в админке и т.п.
 */
interface InterfaceGetTitle
{
    /**
     * @return string
     */
    public function getTitle(): string;
} 