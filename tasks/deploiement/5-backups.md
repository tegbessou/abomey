# Task 5 — Sauvegardes de la base

> Sujet : [product/deploiement.md](../../product/deploiement.md)

## Objectif
Protéger les données : produire une sauvegarde quotidienne de
MariaDB, **vérifiée restaurable**. Sans cette tranche, la donnée
utilisateur vit sur un seul volume sans filet.

## Livré
- Un mécanisme de dump quotidien (`mysqldump`) planifié.
- Une procédure de restauration documentée et testée.

## Critères de vérification
- Un dump de la base est produit automatiquement chaque jour.
- Les dumps sont soumis à une rotation (pas d'accumulation infinie).
- Une **restauration** a été effectuée avec succès depuis un dump
  (test réel, pas seulement le dump).

## Definition of Ready
- T3 livrée : la base MariaDB tourne en prod (accessory Kamal).

## Reporté
- Externalisation des dumps hors du serveur (Hetzner Storage Box /
  objet S3) : recommandée pour survivre à la perte du serveur, à
  ajouter dans un second temps.
- Sauvegarde applicative des volumes (certs, uploads) si le besoin
  émerge.

## Plan technique
- **Dump** : `mysqldump` de l'accessory MariaDB vers un répertoire
  dédié du serveur, planifié en **cron** (quotidien), avec rotation
  (ex. rétention 7-14 jours).
- **Exécution** : cron système sur le VPS invoquant
  `docker exec` sur le conteneur MariaDB, ou un petit conteneur
  planifié.
- **Restauration** : procédure documentée (`mysql < dump.sql` dans
  l'accessory) et jouée une fois pour valider.
- **Secrets** : identifiants MariaDB lus depuis l'environnement, pas
  en clair dans le script.
