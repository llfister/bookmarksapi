# API Bookmarks

This API allows users to manage their favorite videos and photos from Vimeo and Flickr.

You can :
- Access the list of your bookmarks
- Access a single bookmark
- Add a bookmark
- Update a bookmark
- Delete a bookmark
- Add a keyword to a bookmarks
- Remove a keyword from a bookmarks
- Modify a keyword

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose up --build` (the logs will be displayed in the current shell)

## Run tests

```SHELL
# PHPCsFixer
docker-compose exec php composer lint
docker-compose exec php composer lint:fix

# PHPStan
docker-compose exec php composer phpstan

# PHPUnit
docker-compose exec php composer phpunit
```

## Usage

Open Postman or use curl in your terminal.

### Create your first bookmark :

```json
POST http://localhost:801/bookmarks

{
    "url" : "https://vimeo.com/470201160",
    "title" : "An awesome video !",
    "keywords" : [
        {
            "name" : "shark"
        },
        {
            "name" : "swim"
        }
    ]
} 
```

Only ``url`` property  is required. If you don't specify any ``title``, by default, the video/photo title will be chosen.
You can add ``keywords``.

### To access single bookmark

```
GET http://localhost:801/bookmarks/{id}
```
It will return the following response :

```json
{
    "id": 1,
    "title": "An awesome video !",
    "url": "https://vimeo.com/470201160",
    "createdDate": "2021-04-14T12:58:08+00:00",
    "height": 240,
    "width": 426,
    "duration": 103,
    "type": "video",
    "keywords": [
        {
            "id": 3,
            "name": "shark"
        },
        {
            "id": 4,
            "name": "swim"
        }
    ],
    "author": "Les coops de l'information"
}
```

### To access the bookmarks list :

```
GET http://localhost:801/bookmarks
```

### To delete a bookmark :

```
DELETE http://localhost:801/bookmarks/{id}
```

### To modify a bookmark :

```
PUT http://localhost:801/bookmarks/{id}
```

You can also add ``keywords`` on specific bookmark if you forget it :


```json
POST http://localhost:801/bookmarks/{id}/keywords

[
    {
    "name" : "Awe"
    },{
    "name" : "Some !"
    }
]

```

Or remove it :

```
DELETE http://localhost:801/bookmarks/{id_bookmark}/keywords/{id_keyword}
```

If you've made a spelling mistake in your keyword, don't panic, you can edit it !

```
PUT http://localhost:801/keywords/{id}

{
  "name" : "Sharks"
}
```
