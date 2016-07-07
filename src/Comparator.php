<?php

namespace ReenExe\CompareDataStructure;

class Comparator
{
    private $custom = [];

    /**
     * @var array
     */
    private $temporaryCustom;

    /**
     * @var array
     */
    private $exists;

    public static function addCustom(array $custom)
    {
        $instance = self::instance();

        $instance->custom = $instance->custom + $custom;
    }

    private function initialize(array $temporaryCustom)
    {
        $this->temporaryCustom = $this->custom + $temporaryCustom;

        $this->exists = array_keys($this->temporaryCustom);
    }

    /**
     * @param $data
     * @param $structure
     * @param array $custom
     * @return StructureDiffInfo
     */
    public static function check($data, $structure, array $custom = [])
    {
        $instance = self::instance();

        $instance->initialize($custom);

        return $instance->compare($data, $structure) ?: StructureDiffInfo::createEqual();
    }

    private function compare($data, $structure)
    {
        if (is_string($structure)) {
            return $this->diffType($data, $structure);
        }

        if (is_array($structure)) {
            return $this->diffStructure($data, $structure);
        }

        return $this->createDiff('undefined:structure', StructureDiffInfo::CONFIG);
    }

    /**
     * @param $value
     * @param array $types
     * @return StructureDiffInfo|null
     */
    private function diffTypes($value, array $types)
    {
        /**
         * Возможно тут будет переопределение проверки
         * К примеру формата даты или длины
         */

        if (in_array($this->getType($value), $types)) {
            return null;
        }

        if ($this->exists) {
            if ($intersect = array_intersect($types, $this->exists)) {
                foreach ($intersect as $key) {
                    $diff = $this->compare($value, $this->temporaryCustom[$key]);

                    if (empty($diff)) {
                        return null;
                    }
                }

                return $this->processDiff($diff, "custom:type:$key");
            }
        }

        return $this->createDiff('var:type', StructureDiffInfo::TYPE);
    }

    private function diffType($data, $structure)
    {
        $needTypes = explode('|', $structure);

        return $this->diffTypes($data, $needTypes);
    }

    /**
     * @param $data
     * @param $set
     * @return StructureDiffInfo|null
     */
    private function diffSet($data, $set)
    {
        $data = (array)$data;
        $set = (array)$set;

        if (array_diff($data, $set)) {
            return $this->createDiff('set:out', StructureDiffInfo::TYPE);
        }

        foreach ($data as $value) {
            if (!in_array($value, $set, true)) {
                return $this->createDiff('var:type', StructureDiffInfo::TYPE);
            }
        }

        return null;
    }

    /**
     * @param $data
     * @param array $structure
     * @return StructureDiffInfo|null
     */
    private function diffStructure($data, array $structure)
    {
        if (isset($structure['set'])) {
            return $this->diffSet($data, $structure['set']);
        }

        if ($diff = $this->diffTypes($data, $this->getStructureType($structure))) {
            return $diff;
        }

        if (is_array($data)) {
            return $this->diffArrayData($data, $structure);
        }

        return null;
    }

    /**
     * @param array $data
     * @param array $structure
     * @return StructureDiffInfo|null
     */
    private function diffArrayData(array $data, array $structure)
    {
        if (isset($structure['assoc'])) {
            return $this->assoc($structure['assoc'], $data);
        }

        if (isset($structure['values'])) {
            return $this->diffValuesStructure($data, $structure['values']);
        }

        return $this->createDiff('structure:type', StructureDiffInfo::CONFIG);
    }

    /**
     * @param array $data
     * @param $structure
     * @return StructureDiffInfo|null
     */
    private function diffValuesStructure(array $data, $structure)
    {
        if (is_array($structure)) {
            foreach ($data as $key => $subData) {
                if ($diff = $this->assoc($structure, $subData)) {
                    return $this->processDiff($diff, "[$key]");
                }

            }
        } elseif (is_string($structure)) {
            $needTypes = explode('|', $structure);

            foreach ($data as $key => $subData) {
                if ($diff = $this->diffTypes($subData, $needTypes)) {
                    return $this->processDiff($diff, "[$key]");
                }
            }
        }

        return null;
    }

    private function assoc(array $assoc, array $data)
    {
        foreach ($assoc as $key => $structure) {
            if (!array_key_exists($key, $data)) {
                return $this->createDiff($key, StructureDiffInfo::KEY);
            }

            if ($diff = $this->compare($data[$key], $structure)) {
                return $this->processDiff($diff, $key);
            }
        }

        return null;
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
        return $this->processDiff(StructureDiffInfo::createDiff($message), $key);
    }

    private function processDiff(StructureDiffInfo $diff, $key)
    {
        $diff->addPath($key);

        return $diff;
    }

    /**
     * @return Comparator
     */
    private static function instance()
    {
        static $self;

        return $self ?: $self = new self;
    }
}