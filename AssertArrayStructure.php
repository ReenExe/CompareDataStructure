<?php

class AssertArrayStructure
{
    public static function check($data, $structure)
    {
        static $self;

        if (empty($self)) $self = new self;

        return $self->execute($data, $structure);
    }

    private function checkTypes($value, array $types)
    {
        /**
         * Возможно тут будет переопределение проверки
         * К примеру формата даты или длины
         */

        return in_array(strtolower(gettype($value)), $types);
    }

    private function execute($data, $structure)
    {
        if (is_string($structure)) {
            $needTypes = explode('|', $structure);

            return $this->checkTypes($data, $needTypes) ?: $this->structureError('type', 'Разность типов');
        }

        if (is_array($structure)) {
            $needTypes = ['array'];

            if (isset($structure['type'])) {
                $needTypes = array_merge(
                    $needTypes,
                    explode('|', $structure['type'])
                );
            }

            if (!$this->checkTypes($data, $needTypes)) {
                return $this->structureError('type', 'Разность типов');
            }

            if (is_array($data)) {

                if (isset($structure['assoc'])) {

                    foreach ($structure['assoc'] as $key => $subStructure) {

                        if (!array_key_exists($key, $data)) {

                            return $this->structureError($key, 'Отсутствует ключ');

                        }

                        if (is_array($error = $this->execute($data[$key], $subStructure))) {

                            return $this->structureError($key, 'Разность структуры', $error);
                        }
                    }

                } elseif(isset($structure['values'])) {

                    if (is_array($structure['values'])) {
                        foreach ($data as $subData) {
                            foreach ($structure['values'] as $key => $subStructure) {

                                if (!array_key_exists($key, $subData)) {

                                    return $this->structureError($key, 'Отсутствует ключ');

                                };

                                if (is_array($error = $this->execute($subData[$key], $subStructure))) {

                                    return $this->structureError($key, 'Разность структуры', $error);
                                }
                            }
                        }
                    } elseif (is_string($structure['values'])) {
                        $needTypes = explode('|', $structure['values']);

                        $arrayTypes = array_map(function($entry) {
                            return strtolower(gettype($entry));
                        }, $data);

                        if (array_diff($arrayTypes, $needTypes)) {
                            return $this->structureError('array:values', 'Разность структуры');
                        }
                    }


                } else {

                    return $this->structureError('array:type', 'Разность структуры');
                }
            }

        }

        return true;
    }

    private function structureError($key, $message, array $error = [])
    {
        if (empty($error)) {
            $error = [
                'path'    => [],
                'message' => $message
            ];
        }

        array_unshift($error['path'], $key);

        return $error;
    }
}