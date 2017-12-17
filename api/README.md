# Scrappy api

API for Scrappy Life.

# Requirements

    MongoDB running on port 27017

# Run

    sbt run

## Voir les posts

      curl "http://localhost:9000/posts" | jq

## Voir un seul post

      curl "http://localhost:9000/posts/<id>" | jq

# On Docker

Attention: Cette étape peut etre longue a cause du téléchargement de plusieurs images

      docker build -t scrappy-api .

      docker run -e "MONGO_URI=<uri>" scrappy-api

# Test

       sbt clean coverage test coverageReport
