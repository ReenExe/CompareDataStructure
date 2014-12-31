<?php

require_once 'AssertArrayStructure.php';

class AssertArrayStructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider simpleTypeProvider
     */
    public function testSimpleTypeSuccess($data, $structure)
    {
        $this->assertTrue(AssertArrayStructure::check($data, $structure));
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
        $this->assertTrue(
            is_array(AssertArrayStructure::check($data, $structure))
        );
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
        $this->assertTrue(AssertArrayStructure::check($data, $structure));
    }

    public function arrayStructureSuccessProvider()
    {
        return [

            [
                [],

                [
                    'values' => [
                        'id'    => 'integer',
                        'name'  => 'string'
                    ]
                ]
            ],

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
}