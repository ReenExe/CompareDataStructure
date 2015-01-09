<?php

class AssertArrayStructure
{
    private $custom = [];

    public static function addCustom(array $custom)
    {
        $instance = self::instance();

        $instance->custom = $instance->custom + $custom;
    }
    /**
     * @param $data
     * @param $structure
     * @return true|StructureDiffInfo
     */
    public static function check($data, $structure)
    {
        return self::instance()->compare($data, $structure) ?: true;
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

        if (in_array($this->getType($value), $types)) return /* success */;

        if ($this->custom) {

            if ($intersect = array_intersect($types, array_keys($this->custom))) {
                foreach ($intersect as $key) {
                    $diff = $this->compare($value, $this->custom[$key]);

                    if (empty($diff)) return /* success */;
                }

                return $diff;
            }
        }

        return $this->createDiff('var:type', StructureDiffInfo::TYPE);
    }

    private function diffType($data, $structure)
    {
        $needTypes = explode('|', $structure);

        if ($diff = $this->checkTypes($data, $needTypes)) {
            return $diff;
        }
    }

    private function diffSet($data, $set)
    {
        $data = (array) $data;
        $set = (array) $set;

        if (array_diff($data, $set)) {
            return $this->createDiff('set:out', StructureDiffInfo::TYPE);
        }


        foreach ($data as $value) {
            if (in_array($value, $set, true)) continue;

            return $this->createDiff('var:type', StructureDiffInfo::TYPE);
        }
    }

    private function diffStructure($data, array $structure)
    {
        /**
         * structure `set`
         */
        if (isset($structure['set'])) {
            return $this->diffSet($data, $structure['set']);
        }

        if ($diff = $this->checkTypes($data, $this->getStructureType($structure))) {
            return $diff;
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

    /**
     * @return AssertArrayStructure
     */
    private function instance()
    {
        static $self;

        return $self ?: $self = new self;
    }
}