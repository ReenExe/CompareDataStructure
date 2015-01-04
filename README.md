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
    ]
}
```