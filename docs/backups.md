# Sauvegardes de la base

Sauvegarde quotidienne de la base MariaDB de production (`abomey`),
via `mariadb-dump` sur le conteneur accessory `abomey-db`. Tranche T5
du sujet #004.

## Principe
- `scripts/backup-database.sh` produit un dump **compressé et
  horodaté** dans `/var/backups/abomey`, puis supprime les dumps de
  plus de **14 jours**.
- Le mot de passe root n'est **jamais** dans le script : il est lu
  depuis l'environnement du conteneur (`MARIADB_ROOT_PASSWORD`,
  injecté par Kamal).
- Le dump est déclenché par un **cron sur le serveur** — Kamal ne gère
  pas les tâches planifiées.

## Installation (une fois, sur le serveur)
Depuis le poste, `<serveur>` étant l'IP du VPS :
```sh
scp -i ~/.ssh/id_rsa_prod scripts/backup-database.sh \
  root@<serveur>:/usr/local/bin/backup-abomey.sh
ssh -i ~/.ssh/id_rsa_prod root@<serveur> 'chmod +x /usr/local/bin/backup-abomey.sh'

# cron quotidien à 3h du matin
ssh -i ~/.ssh/id_rsa_prod root@<serveur> \
  '(crontab -l 2>/dev/null; echo "0 3 * * * /usr/local/bin/backup-abomey.sh >> /var/log/abomey-backup.log 2>&1") | crontab -'
```

## Vérifier
```sh
ssh -i ~/.ssh/id_rsa_prod root@<serveur> /usr/local/bin/backup-abomey.sh
ssh -i ~/.ssh/id_rsa_prod root@<serveur> 'ls -lh /var/backups/abomey'
```

## Restaurer un dump
```sh
ssh -i ~/.ssh/id_rsa_prod root@<serveur>
gunzip < /var/backups/abomey/abomey-YYYYMMDD-HHMMSS.sql.gz \
  | docker exec -i abomey-db sh -c 'exec mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" abomey'
```
La restauration doit être **testée au moins une fois** pour valider
la tranche (un dump qu'on ne sait pas restaurer ne protège rien).

## Reporté
- **Externalisation** des dumps hors du serveur (Hetzner Storage Box
  ou stockage objet S3). Tant qu'elle n'est pas en place, les
  sauvegardes protègent d'une corruption de données mais **pas d'une
  perte du serveur** (dumps et base sur la même machine).
