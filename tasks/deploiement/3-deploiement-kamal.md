# Task 3 — Déploiement bout-en-bout avec Kamal (manuel)

> Sujet : [product/deploiement.md](../../product/deploiement.md)

## Objectif
Mettre Abomey **en ligne** sur le VPS via un `kamal deploy` lancé à
la main. C'est le *walking skeleton* du sujet : toute la chaîne
(image → registry → serveur → proxy TLS → base → migrations) est
traversée une première fois.

## Livré
- `config/deploy.yml` (Kamal) + `.kamal/secrets`.
- Configuration `kamal-proxy` (domaine + TLS), accessory MariaDB,
  hook de migrations post-déploiement.

## Critères de vérification
- `kamal setup` puis `kamal deploy` mènent l'app en ligne.
- L'app répond en **HTTPS** sur le domaine (certificat Let's
  Encrypt émis par `kamal-proxy`).
- Le **login Logto réel** fonctionne (app de prod).
- Les **migrations Doctrine** sont appliquées au déploiement.
- `kamal rollback` restaure la version précédente.

## Definition of Ready
- T1 (VPS provisionné) et T2 (image de prod) livrés.
- Préalables externes réunis : **nom de domaine + DNS** vers le VPS,
  **app Logto de prod** (client id/secret, redirect URIs prod).
- Le choix de stack (Kamal + OpenTofu) est **consigné en ADR**.

## Reporté
- Automatisation du déploiement (→ T4).
- Sauvegardes (→ T5).

## Plan technique
- **`config/deploy.yml`** :
  - `service` + `image` (namespace `ghcr.io`).
  - `servers` : l'IP du VPS (sortie OpenTofu de T1).
  - `proxy` : `ssl: true`, `host: <domaine>` (kamal-proxy gère le
    TLS et le rolling).
  - `registry` : `ghcr.io`, user + `KAMAL_REGISTRY_PASSWORD`.
  - `env` : `clear` (APP_ENV=prod…) et `secret` (`APP_SECRET`,
    `DATABASE_URL`, `LOGTO_*`).
  - `accessories.db` : image MariaDB, volume persistant, port
    interne, mot de passe en secret.
  - `builder` : build multi-arch ou natif selon runner.
- **Migrations** : hook `post-deploy` ou `kamal app exec
  'bin/console doctrine:migrations:migrate --no-interaction'`.
- **Secrets** : `.kamal/secrets` lit l'environnement local /
  GitHub ; jamais commité.
- **TLS** : persister le volume de `kamal-proxy` pour conserver les
  certificats entre déploiements (éviter le rate-limit Let's
  Encrypt).
