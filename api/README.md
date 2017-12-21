# Scrappy api

API for Scrappy Life.

# Requirements

    MongoDB running on port 27017

# Run

    sbt run
    
## Afficher la spec Swagger

  [http://localhost:9000](http://localhost:9000)

## Voir les posts

      curl "http://localhost:9000/posts" | jq

## Voir un seul post

      curl "http://localhost:9000/posts/<id>" | jq

## Filtrer par auteurs

      curl "http://localhost:9000/posts?author=anonyme" | jq

## Filter par dates

      curl "http://localhost:9000/posts?from=2017-12-21T10:20:00Z&to=2017-12-22T00:30:00Z" | jq

# On Docker

Attention: Cette étape peut etre longue a cause du téléchargement de plusieurs images

      docker build -t scrappy-api .

      docker run -e "MONGO_URI=<uri>" scrappy-api

# Test

       sbt clean coverage test coverageReport
