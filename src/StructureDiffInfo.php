<?php

namespace ReenExe\CompareDataStructure;
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

    private $equal = false;

    private function __construct($equal)
    {
        $this->equal = $equal;
    }

    public static function createDiff($message)
    {
        $self =  new self(false);
        $self->message = $message;
        return $self;
    }

    public static function createEqual()
    {
        return new self(true);
    }

    public function isEqual() {
        return $this->equal;
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

    public  function __toString()
    {
        return $this->isEqual() ? '' : "{$this->getMessage()} {$this->getPath()}";
    }
}