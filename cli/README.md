# Scrappy cli

Command line tool for Scrappy Life.

# Requirement

      composer install

# Run

      bin/scrappy posts | jq

# On Docker

      docker build -t scrappy .

      docker run -it scrappy posts

# Test

      vendor/bin/phpunit --coverage-html cover test
