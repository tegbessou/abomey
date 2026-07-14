# Déploiement / mise en production

> Sujet **technique** (infra/ops). Contrairement aux sujets produit,
> il ne porte pas de règles métier ni de scénarios Given/When/Then :
> ses tranches portent des **critères de vérification technique**.
> Suivi : `tracking/abomey.md`, sujet #004.

## Contexte
Abomey est fonctionnellement complet (comptes et isolation des
données, création de Partie, saisie et calcul des Donnes) mais ne
tourne qu'en local via le `docker compose` de développement.
L'objectif est de le mettre en ligne pour un usage réel entre amis.

## Objectif
Abomey accessible publiquement en HTTPS sur son domaine,
redéployable par simple push sur `main`, avec des sauvegardes
quotidiennes restaurables — pour un coût d'environ 5 €/mois.

## Contraintes
- Faible trafic (usage entre amis) : un seul serveur suffit.
- Réutiliser la stack existante : **FrankenPHP** (runtime, déjà en
  dev), **MariaDB**, **AssetMapper** (assets sans toolchain JS).
- Auth déléguée à **Logto** (EU) : nécessite une app/tenant de prod.
- Budget serré : ni PaaS ni base managée au départ.
- Cohérence dev/prod : même runtime FrankenPHP.

## Stack retenue
- **Provisioning** : VPS **Hetzner** décrit en **OpenTofu**
  (provider `hcloud`).
- **Runtime** : image de prod **FrankenPHP** allégée (sans
  chromium, `composer --no-dev`, assets compilés, `APP_ENV=prod`).
- **Déploiement** : **Kamal** — `kamal-proxy` (TLS Let's Encrypt +
  rolling zéro-downtime), **MariaDB en accessory** conteneur,
  secrets injectés par Kamal, rollback en une commande. Image
  poussée sur **`ghcr.io`**.
- **CI** : **GitHub Actions** au push sur `main` → `kamal deploy`
  (build sur les runners GitHub, pas sur le VPS).
- **Backups** : `mysqldump` cron quotidien.

Le choix de la stack de déploiement (Kamal + OpenTofu plutôt qu'un
script maison ou un PaaS) est structurant : à consigner en **ADR**
avant la mise en œuvre de T3.

## Hors-scope
- Environnement de staging (prod seule au départ).
- Scaling horizontal / multi-serveurs.
- Base managée (conteneur au départ).
- Suppression côté Logto (dette #001, non liée à ce sujet).

## Préalables externes
À réunir avant la première mise en ligne (T3) :
- **Nom de domaine** acquis, DNS pointant vers le VPS.
- **App/tenant Logto de prod** distincte du dev (client id/secret,
  redirect URIs de production).

## Critère de succès
Abomey est accessible en HTTPS sur son domaine, un push sur `main`
le redéploie sans intervention manuelle, et une sauvegarde
quotidienne de la base est produite et restaurable.

## Tranches
Découpage en étapes livrables et vérifiables. Détail dans
`tasks/deploiement/`.

1. [T1 — Infra OpenTofu](../tasks/deploiement/1-infra-opentofu.md) —
   le VPS Hetzner existe, provisionné en code.
2. [T2 — Image de prod FrankenPHP](../tasks/deploiement/2-image-prod.md) —
   une image de prod allégée build et démarre.
3. [T3 — Déploiement bout-en-bout (Kamal)](../tasks/deploiement/3-deploiement-kamal.md) —
   Abomey en ligne, HTTPS, via `kamal deploy` manuel.
4. [T4 — CI/CD GitHub Actions](../tasks/deploiement/4-ci-cd.md) —
   un push sur `main` déploie automatiquement.
5. [T5 — Backups](../tasks/deploiement/5-backups.md) —
   sauvegarde quotidienne restaurable.
