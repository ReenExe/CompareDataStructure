<?php

/**
 * TODO: Возможно стоит лучше определить интерфейс и возвращать только его
 *          Чтобы убрать из ответа метод `addPath`
 */
class StructureDiffInfo
{
    const KEY    = 'Отсутствует ключ';
    const TYPE   = 'Разность типов';
    const CONFIG = 'Разность структуры';

    private $message;

    private $path = [];

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function addPath($key)
    {
        array_unshift($this->path, $key);
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getPath()
    {
        return join('.', $this->path);
    }
}