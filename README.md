# Scrappy Life

Application web pour "Vie de merde"

Affiche les 200 derniers postes

# Run

N'hésitez pas a aller prendre un café !

      docker-compose up -d

Mais si vous etes pressés:

      docker-compose -f docker-compose-production.yml up -d

Il faudra quelques secondes pour que les posts VDM se chargent la premiere fois.
Rechargez la page au besoin !

# Application

Si vous souhaitez lancer l'application localement, chaque partie contient un README détaillé sur la marche a suivre.

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

# Ensuite?

- Implementer une Interface Web pour consomer l'API
- Tester l'application complete (Apres lancement via Docker)
