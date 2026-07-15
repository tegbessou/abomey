#!/bin/sh
set -eu

BACKUP_DIR=/var/backups/abomey
RETENTION_DAYS=14

mkdir -p "$BACKUP_DIR"
target="$BACKUP_DIR/abomey-$(date +%Y%m%d-%H%M%S).sql.gz"

docker exec abomey-db sh -c 'exec mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction --databases abomey' | gzip > "$target"

find "$BACKUP_DIR" -name 'abomey-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete
