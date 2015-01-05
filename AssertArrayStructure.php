<?php

class AssertArrayStructure
{
    /**
     * @param $data
     * @param $structure
     * @return true|StructureDiffInfo
     */
    public static function check($data, $structure)
    {
        static $self;

        if (empty($self)) $self = new self;

        return $self->compare($data, $structure) ?: true;
    }

    private function compare($data, $structure)
    {
        if (is_string($structure)) {
            return $this->diffType($data, $structure);
        }

        if (is_array($structure)) {
            return $this->diffStructure($data, $structure);
        }
    }

    private function checkTypes($value, array $types)
    {
        /**
         * Возможно тут будет переопределение проверки
         * К примеру формата даты или длины
         */

        return in_array($this->getType($value), $types);
    }

    private function diffType($data, $structure)
    {
        $needTypes = explode('|', $structure);

        if (!$this->checkTypes($data, $needTypes)) {
            return $this->createDiff('var:type', StructureDiffInfo::TYPE);
        }
    }

    private function diffSet($data, $set)
    {
        if (array_diff((array) $data, (array) $set)) {
            return $this->createDiff('set:out', StructureDiffInfo::TYPE);
        }

        /**
         * TODO: Сделать проверку через "===" in_array($needle, $haystack, $strict = true)
         */
    }

    private function diffStructure($data, array $structure)
    {
        /**
         * structure `set`
         */
        if (isset($structure['set'])) {
            return $this->diffSet($data, $structure['set']);
        }

        if (!$this->checkTypes($data, $this->getStructureType($structure))) {
            return $this->createDiff('var:type', StructureDiffInfo::TYPE);
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

                    $arrayTypes = array_map([$this, 'getType'], $data);

                    if (array_diff($arrayTypes, $needTypes)) {
                        return $this->createDiff('array:values', StructureDiffInfo::TYPE);
                    }
                }

            } else {

                return $this->createDiff('structure:type', StructureDiffInfo::CONFIG);
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

    private function getType($value)
    {
        return strtolower(gettype($value));
    }

    private function getStructureType(array $structure)
    {
        $types = ['array'];

        if (isset($structure['type'])) {
            $types = array_merge(
                $types,
                explode('|', $structure['type'])
            );
        }

        return $types;
    }

    private function createDiff($key, $message)
    {
        return $this->processDiff(new StructureDiffInfo($message), $key);
    }

    private function processDiff(StructureDiffInfo $diff, $key)
    {
        $diff->addPath($key);

        return $diff;
    }
}