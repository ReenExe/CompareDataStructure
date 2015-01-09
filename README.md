Assert Structure
====================

Native PHP assert for testing REST API JSON response

Example:
------------
You have REST API method that return response like:
```json
{
    "id": 3,
    "name": "Alex",
    "location": 3,
    "gender": "men",
    "joined": {
        "source": 1,
        "at": "2011-11-11 11:11:11"
    },
    "friends": [
        {
            "id": 7,
            "name": "Alice"
        },
        {
            "id": 8,
            "name": "Bob"
        }
    ],
    "interests": ["programming", "books", "sport"],
    "games": null,
    "books": [
        {
            "author": "Достоевский Фёдор Михайлович",
            "title": "Преступление и наказание"
        },
        {
            "author": "Steve McConnell",
            "title": "Code Complete"
        }
    ],
    "social": ["GitHub", "LinkedIn"]
}
```
And we want to check these structures through the settings:
```php
[
    'assoc' => [
        'id'    => 'integer',
        'name'  => 'string',
        'location'  => 'integer',
        'gender'    => [
            'set' => ['men', 'women', null]
        ],
        'joined'    => [
            'assoc' => [
                'source'    => 'integer|null',
                'at'        => 'string'
            ]
        ],
        'friends' => [
            'type' => 'null',
            'values' => [
                'id'    => 'integer',
                'name'  => 'string'
            ]
        ],
        'interests' => [
            'type' => 'null',
            'values' => 'string'
        ],
        'games' => [
            'type'  => 'null',
            'values' => [
                'title' => 'string'
            ]
        ],
        'books' => [
            'values' => [
                'author' => 'string',
                'title'  => 'string',
            ]
        ],
        'social' => [
            'set' => [
                'GitHub', 'LinkedIn', 'Facebook', 'Google', 'Twitter',
            ]
        ]
    ]
]
```
In this example, tried to describe the full scope

Also:
------------
We can test the simple types
```json
1
```
```php
'integer'
```
or
```php
array_merge(
    range(1, 100),
    range('a', 'z'),
    [true, false]
)
```
```php
['values' => 'integer|string|boolean']
```

Use:
------------
```php
AssertArrayStructure::check($data, $structure)
```

If all right - return
```php
true
```
Else
```php
     /**
      @var $diff StructureDiffInfo
      @method StructureDiffInfo::getMessage
      @method StructureDiffInfo::getPath
     */
```

`@TODO` or to be continued...
------------

Together with the structures of `assoc`,` values`, `set` - there is a desire to add structure `range`
Perhaps also worth adding the ability to set "user types" and they "recursion"

Alternatives:
------------
AssertJsonStructure: (https://github.com/t4web/AssertJsonStructure)
    - part of it was used to create, also `used idea of checking the structure`