# Task 2 — Image de production FrankenPHP

> Sujet : [product/deploiement.md](../../product/deploiement.md)

## Objectif
Produire une image Docker de **production** distincte de l'image de
dev : allégée, sans outillage de test, prête à tourner en
`APP_ENV=prod`. Même runtime FrankenPHP qu'en dev pour la cohérence.

## Livré
- Un `Dockerfile` de prod (stage dédié ou fichier distinct) et le
  `.dockerignore` associé.

## Critères de vérification
- L'image build sans erreur.
- Le conteneur démarre en `APP_ENV=prod` et sert l'application.
- Les assets (AssetMapper) sont compilés et servis.
- L'image ne contient **pas** chromium/chromedriver (réservés à
  Panther en test) et pèse sensiblement moins que l'image de dev.

## Definition of Ready
- Image de dev FrankenPHP existante (`Dockerfile` actuel) comme base
  de référence.

## Reporté
- Worker mode FrankenPHP (optimisation perf) : activable plus tard
  si le trafic le justifie.

## Plan technique
- **Multi-stage** :
  - stage `composer` — `composer install --no-dev
    --optimize-autoloader --classmap-authoritative`.
  - stage `assets` — `bin/console asset-map:compile` en
    `APP_ENV=prod` (AssetMapper, aucun toolchain JS).
  - stage final — `dunglas/frankenphp` **sans** chromium ni driver,
    code + vendors + assets copiés, cache réchauffé
    (`cache:warmup`), `APP_ENV=prod`, OPcache + APCu activés,
    utilisateur non-root.
- **Config FrankenPHP** : écoute en HTTP interne (le TLS est géré
  par `kamal-proxy`, cf. T3), healthcheck HTTP exposé.
- `.dockerignore` : exclure `tests/`, `var/`, `.git`, `infra/`,
  `node_modules` éventuels.
