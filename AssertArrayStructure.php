<?php

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

class AssertArrayStructure
{
    /**
     * @param $data
     * @param $structure
     * @return bool|StructureDiffInfo
     */
    public static function check($data, $structure)
    {
        static $self;

        if (empty($self)) $self = new self;

        return $self->compare($data, $structure) ?: true;
    }

    private function checkTypes($value, array $types)
    {
        /**
         * Возможно тут будет переопределение проверки
         * К примеру формата даты или длины
         */

        return in_array(strtolower(gettype($value)), $types);
    }

    private function compare($data, $structure)
    {
        if (is_string($structure)) {
            $needTypes = explode('|', $structure);

            if (!$this->checkTypes($data, $needTypes)) {
                return $this->createDiff('var:type', StructureDiffInfo::TYPE);
            }

        } elseif (is_array($structure)) {
            $needTypes = ['array'];

            if (isset($structure['type'])) {
                $needTypes = array_merge(
                    $needTypes,
                    explode('|', $structure['type'])
                );
            }

            if (!$this->checkTypes($data, $needTypes)) {
                return $this->createDiff('type', StructureDiffInfo::TYPE);
            }

            if (is_array($data)) {

                if (isset($structure['assoc'])) {

                    if ($diff = $this->assoc($structure['assoc'], $data)) {
                        return $diff;
                    }

                } elseif(isset($structure['values'])) {

                    if (is_array($structure['values'])) {
                        foreach ($data as $key => $subData) {

                            if ($diff = $this->assoc($structure['values'], $subData)) {
                                return $this->processDiff($diff, "[$key]");
                            }

                        }
                    } elseif (is_string($structure['values'])) {
                        $needTypes = explode('|', $structure['values']);

                        $arrayTypes = array_map(function($entry) {
                            return strtolower(gettype($entry));
                        }, $data);

                        if (array_diff($arrayTypes, $needTypes)) {
                            return $this->createDiff('array:values', StructureDiffInfo::TYPE);
                        }
                    }

                } else {

                    return $this->createDiff('array:type', StructureDiffInfo::CONFIG);
                }
            }

        }
    }

    private function assoc(array $assoc, array $data)
    {
        foreach ($assoc as $key => $structure) {

            if (!array_key_exists($key, $data)) {
                return $this->createDiff($key, StructureDiffInfo::KEY);
            };

            if ($diff = $this->compare($data[$key], $structure)) {
                return $this->processDiff($diff, $key);
            }
        }
    }

    private function createDiff($key, $message, StructureDiffInfo $error = null)
    {
        return $this->processDiff(new StructureDiffInfo($message), $key);
    }

    private function processDiff(StructureDiffInfo $diff, $key)
    {
        $diff->addPath($key);

        return $diff;
    }
}