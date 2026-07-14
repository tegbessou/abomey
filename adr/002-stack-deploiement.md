# ADR 002 — Stack de déploiement et mise en production

- **Date** : 2026-07-14
- **Statut** : Accepté

## Contexte

Abomey est fonctionnellement complet (comptes, Parties, saisie et
calcul des Donnes) mais n'est pas déployé : il ne tourne qu'en local
via le `docker compose` de développement. Le sujet #004
(`product/deploiement.md`) vise sa mise en ligne pour un usage réel
entre amis.

Contraintes structurantes :

- Faible trafic (usage entre amis) : un seul serveur suffit.
- Budget serré, cible ~5 €/mois.
- Réutiliser la stack existante : FrankenPHP (runtime, déjà en dev),
  MariaDB, AssetMapper (assets sans toolchain JS).
- Auth déléguée à Logto (ADR 001), qui impose une application de
  production distincte du développement.
- Cohérence dev/prod ; éviter d'opérer une infrastructure lourde.

Cette ADR formalise la **chaîne de déploiement** : où héberger,
comment provisionner le serveur, comment déployer l'application.

## Options considérées

### Axe hébergement et déploiement

| | PaaS (Clever Cloud / Scalingo) | VPS + `docker compose` + script SSH maison | VPS + Kamal | Kubernetes managé |
|---|---|---|---|---|
| Coût mensuel | ~15-25 € | ~5 € | ~5 € | élevé |
| Ops à charge | quasi nulle | moyenne (tout à la main) | faible (Kamal orchestre) | élevée |
| Zéro-downtime / rollback | inclus | à coder soi-même | natif (`kamal-proxy`, `kamal rollback`) | natif |
| TLS automatique | inclus | à configurer | natif (`kamal-proxy`) | à configurer |
| Lock-in | fort | nul | nul | modéré |
| Adéquation au trafic visé | surdimensionné et cher | oui | oui | très surdimensionné |

**PaaS écarté** : le plus simple à opérer, mais 3-4× plus cher pour
un besoin à faible trafic, avec un lock-in fort. N'apporte rien tant
que le trafic reste faible.

**Script SSH maison écarté** : réinvente en moins bien ce que Kamal
fournit (pas de zéro-downtime, pas de rollback, proxy et TLS à
câbler à la main). Dette d'outillage plutôt qu'un gain.

**Kubernetes écarté** : surdimensionné pour un serveur unique et un
usage entre amis ; charge opérationnelle disproportionnée.

### Axe provisioning

- **Manuel (console / CLI Hetzner)** : suffisant pour un serveur,
  mais non reproductible ni versionné — le « comment ce serveur a
  été créé » reste implicite.
- **OpenTofu (retenu)** : infrastructure en code, reproductible et
  versionnée dès le départ, au prix d'une légère courbe initiale.

## Décision

- **Hébergement** : un VPS unique **Hetzner Cloud**, région EU.
- **Provisioning** : **OpenTofu** (provider `hcloud`) — infra en
  code dès le départ.
- **Déploiement** : **Kamal** — `kamal-proxy` (TLS Let's Encrypt +
  déploiement rolling zéro-downtime), image poussée sur `ghcr.io`,
  **MariaDB en accessory** conteneur (volume persistant), rollback
  natif.
- **Runtime** : image de production **FrankenPHP** (cohérence avec
  le développement) ; le TLS est délégué à `kamal-proxy`, FrankenPHP
  sert l'app en HTTP derrière le proxy.
- **CI** : **GitHub Actions** au push sur `main` déclenche
  `kamal deploy` (build sur les runners GitHub, pas sur le VPS).
- **Backups** : `mysqldump` cron quotidien.

## Conséquences

Positives :

- Coût minimal (~5 €/mois), sans lock-in.
- Zéro-downtime, rollback et TLS automatique sans code maison
  (Kamal).
- Infrastructure reproductible et versionnée (OpenTofu).
- Cohérence dev/prod (FrankenPHP des deux côtés).

Négatives et coûts assumés :

- **Ops à notre charge** : mises à jour système, sécurité et
  disponibilité du serveur reposent sur nous, sans le SLA d'un PaaS.
- **Point unique de défaillance** : un seul serveur, une base
  conteneurisée sur un volume — d'où la criticité des backups (T5)
  et, à terme, de leur externalisation hors du serveur.
- **Dépendance à Kamal** : outil relativement jeune ; un changement
  majeur de son modèle nous impacterait — risque atténué par sa
  simplicité et son socle standard (Docker + SSH).
- **Courbe d'entrée d'OpenTofu** vs un provisioning manuel, assumée
  pour la reproductibilité.

## Sources

- Sujet #004 : `product/deploiement.md` et `tracking/abomey.md`.
- ADR 001 (fournisseur d'authentification) — consommée : ce
  déploiement requiert une application Logto de production.
- Documentation Kamal (kamal-deploy.org), OpenTofu (provider
  Hetzner `hcloud`), FrankenPHP.
