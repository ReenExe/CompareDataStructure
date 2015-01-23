<?php

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
     * TODO: Возможно стоит всегда возвращать в одном формате. Экземпляр класса с методом `isEqual`
     */
    /**
     * @param $data
     * @param $structure
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

    private function checkTypes($value, array $types)
    {
        /**
         * Возможно тут будет переопределение проверки
         * К примеру формата даты или длины
         */

        if (in_array($this->getType($value), $types)) return /* success */;

        if ($this->exists) {

            if ($intersect = array_intersect($types, $this->exists)) {
                foreach ($intersect as $key) {
                    $diff = $this->compare($value, $this->temporaryCustom[$key]);

                    if (empty($diff)) return /* success */;
                }

                /**
                 * TODO: Возможно стоит возвращать с наименьшим путем
                 */
                return $this->processDiff($diff, "custom:type:$key");
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

                    foreach ($data as $key => $subData) {

                        if ($diff = $this->checkTypes($subData, $needTypes)) {
                            return $this->processDiff($diff, "[$key]");
                        }
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