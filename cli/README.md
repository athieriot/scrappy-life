# Scrappy cli

Command line tool for Scrappy Life.

# Requirement

      composer install

# Run

## Voir les posts

      bin/scrappy posts | jq

## Charger les posts dans MongoDB

      bin/scrappy load -vv

# On Docker

Attention: Cette étape peut etre longue a cause l'installation d'extensions PHP

      docker build -t scrappy .

      docker run -it scrappy posts

# Test

      vendor/bin/phpunit --coverage-html cover test
