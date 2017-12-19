# Scrappy Life

Application web pour "Vie de merde"

Affiche les 200 derniers postes

# Run

N'hésitez pas a aller prendre un café !

      docker-compose up -d

# Application

## Afficher la spec Swagger

  [http://localhost:9000](http://localhost:9000)

## Voir les posts

      curl "http://localhost:9000/posts" | jq
      
## Voir un seul post
      
      curl "http://localhost:9000/posts/<id>" | jq
