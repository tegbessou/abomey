# Task 4 — Déploiement continu via GitHub Actions

> Sujet : [product/deploiement.md](../../product/deploiement.md)

## Objectif
Rendre le déploiement automatique : un push sur `main` déclenche
`kamal deploy`, sans commande manuelle. GitHub Actions est le
déclencheur ; Kamal reste le moteur de déploiement (posé en T3).

## Livré
- Un workflow `.github/workflows/deploy.yml`.
- Les secrets GitHub nécessaires au déploiement.

## Critères de vérification
- Un push sur `main` déclenche le workflow et exécute
  `kamal deploy`.
- À l'issue, la prod sert la nouvelle version (rolling, sans
  coupure perceptible).
- Deux pushs rapprochés ne se déploient pas en parallèle
  (concurrence maîtrisée).

## Definition of Ready
- T3 livrée : `kamal deploy` fonctionne à la main.

## Reporté
- Étapes de gate (tests/quality en pré-déploiement) : peuvent être
  ajoutées au workflow ultérieurement ; hors de cette tranche qui
  vise le déclenchement automatique.

## Plan technique
- **Workflow** sur `push` vers `main` :
  - checkout, installation de Kamal (image officielle ou gem),
    `kamal deploy`.
  - le build de l'image se fait sur le runner GitHub, pas sur le
    VPS.
- **Secrets GitHub** : clé SSH de déploiement (`SSH_PRIVATE_KEY`),
  hôte/IP, `KAMAL_REGISTRY_PASSWORD` (ou `GITHUB_TOKEN` pour
  `ghcr.io`), secrets applicatifs si non déjà sur le serveur.
- **`concurrency`** : groupe unique `deploy-main` pour sérialiser
  les déploiements.
- **`ghcr.io`** : authentification via `GITHUB_TOKEN` (permissions
  `packages: write`).
