# Environnement Web Conteneurisé - POWERiti

Projet réalisé dans le cadre d'un stage BTS SIO SISR chez POWERiti

# Description

Déploiement d'un environnement web complet conteneurisé avec Docker, comprenant un serveur web Nginx, un moteur PHP-FPM et une base de données MariaDB.

## Architecture 

Nginx (port 80) -> PHP-FPM (port 9000) -> MariaDB (port 3306)

## Installation 
### Prérequis

- Docker
- Docker Compose
- Git

### Lancer l'environnement

```bash
git clone https://github.com/potironfle/docker-web-env.git
cd docker-web-env
docker compose up -d --build
```
L'environnement est accessible sur http://localhost
