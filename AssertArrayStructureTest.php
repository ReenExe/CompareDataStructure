<?php

require_once 'AssertArrayStructure.php';

class AssertArrayStructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider simpleTypeProvider
     */
    public function testSimpleTypeSuccess($data, $structure)
    {
        $this->assertArrayStructureSuccess($data, $structure);
    }

    public function simpleTypeProvider()
    {
        return [
            [1,         'integer'],
            [null,      'integer|null'],

            ['text',    'string'],
            [null,      'string|null'],

            [1.0,       'double'],
            [null,      'double|null'],

            [[],        'array'],
            [[1, 2, 3], 'array'],
            [[1, ''],   'array'],
            [null,      'array|null'],

            [true,      'boolean'],
            [null,      'boolean|null'],

            [1,         'string|integer'],
            ['',        'string|integer'],
            [true,      'string|integer|boolean'],
            [[],        'string|integer|boolean|array'],
        ];
    }

    /**
     * @dataProvider simpleTypeFailProvider
     */
    public function testSimpleTypeFail($data, $structure)
    {
        $this->assertArrayStructureFail($data, $structure);
    }

    public function simpleTypeFailProvider()
    {
        return [
            ['',         'integer'],
            [1,          'array'],
            [[],         'string|null'],
        ];
    }

    /**
     * @dataProvider arrayStructureSuccessProvider
     */
    public function testArrayStructureSuccess($data, $structure)
    {
        $this->assertArrayStructureSuccess($data, $structure);
    }

    public function arrayStructureSuccessProvider()
    {
        return [

            /* ~ */
            [
                [1, 2, 3],

                ['values' => 'integer']
            ],

            /* ~ */
            [
                array_merge(
                    range(1, 100),
                    range('a', 'z'),
                    [true, false]
                ),

                ['values' => 'integer|string|boolean']
            ],

            /* ~ */
            [
                [],

                [
                    'values' => [
                        'id'    => 'integer',
                        'name'  => 'string'
                    ]
                ]
            ],

            /* ~ */
            [
                [
                    [
                        'id'    => 3,
                        'name'  => 'Anna'
                    ],

                    [
                        'id'    => 5,
                        'name'  => 'Alex'
                    ],
                ],

                [
                    'values' => [
                        'id'    => 'integer',
                        'name'  => 'string'
                    ]
                ]
            ],

            /* ~ */
            [
                [
                    'id'    => 1,
                    'name'  => 'Alex',

                    'friends' => [
                        [
                            'id'    => 3,
                            'name'  => 'Anna',
                        ],

                        [
                            'id'    => 7,
                            'name'  => 'Bob',
                        ],
                    ]
                ],

                [
                    'assoc' => [
                        'id'    => 'integer',
                        'name'  => 'string',
                        'friends' => [
                            'values' => [
                                'id'    => 'integer',
                                'name'  => 'string',
                            ]
                        ]
                    ]
                ]
            ],


        ];
    }

    private function assertArrayStructureSuccess($data, $structure)
    {
        $this->assertTrue(AssertArrayStructure::check($data, $structure));
    }

    private function assertArrayStructureFail($data, $structure)
    {
        /**
         * Ошибка возвращаются в формате массива
         */
        $diff = AssertArrayStructure::check($data, $structure);

        $this->assertTrue(
            $diff instanceof StructureDiffInfo
        );
    }
}