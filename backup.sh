#!/bin/bash

# Configuration
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="/root/monsite/backups"
CONTAINER="monsite-mariadb-1"
DB_NAME="monsite"
DB_USER="root"
DB_PASS="secret"

# Création du dump
docker exec $CONTAINER mariadb-dump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Message de confirmation
echo "Sauvegarde effectuée : backup_$DATE.sql"

# Supprime les sauvegardes de plus de 7 jours
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
echo "Anciennes sauvegardes supprimées"
