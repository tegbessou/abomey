# ADR 001 — Choix d'un fournisseur d'authentification externe

- **Date** : 2026-05-14
- **Statut** : Accepté

## Contexte

Abomey s'ouvre à l'inscription publique. La spec
`produit/abomey/comptes-utilisateurs.md` fixe le périmètre
produit : authentification déléguée à des fournisseurs
d'identité externes (Google et Apple), pas d'authentification
email/mot de passe, isolation totale des données par
Utilisateur, suppression dure de compte, conformité RGPD
minimale.

Cette ADR formalise le choix du **service intermédiaire**
d'authentification entre Abomey et les fournisseurs d'identité.

Contraintes structurantes :

- Stack technique : PHP / Symfony / MariaDB.
- Volonté explicite de ne pas gérer l'authentification soi-même
  (pas de stockage de mot de passe, pas de mécanique de reset,
  pas de mécanique d'invitation manuelle).
- Acceptation des problématiques RGPD liées à l'inscription
  publique, à condition que la conformité reste tenable pour un
  projet de loisir.
- Couverture obligatoire : Google et Apple comme fournisseurs
  d'identité.

## Options considérées

Quatre approches comparées :

| | Auth maison | Auth0 | Supabase Auth | Logto Cloud |
|---|---|---|---|---|
| Free tier | gratuit | ~7 500 MAU | ~50 000 MAU | ~50 000 MAU |
| Google et Apple en standard | manuel via bundles OAuth | natif | natif | natif (3 connecteurs sociaux gratuits) |
| Stack idiomatique en PHP / Symfony | oui | SDK PHP, intégration OIDC | écosystème JS / Postgres, intégration via JWT | SDK PHP officiel, sans bundle Symfony dédié |
| Open-source / option de self-host | n/a | non | partiel | oui (Logto OSS, intégralement self-hostable) |
| Région EU disponible | sous notre contrôle | oui | oui | oui (Netherlands) |
| DPA standard | n/a | oui | oui | oui (intégré aux ToS, sans signature séparée) |
| Risque tarifaire à terme | faible (code maison) | élevé (Okta) | modéré | modéré (FOSS atténue) |

**Auth maison écartée** : oblige à implémenter et maintenir le
flux OAuth, la gestion de session OIDC, la rotation de tokens,
et alourdit le périmètre RGPD côté Abomey (responsable unique).
Va à l'encontre du critère « ne pas gérer l'authentification ».

**Auth0 écarté** : free tier le plus restrictif (7 500 MAU),
politique tarifaire incertaine depuis le rachat par Okta. Bonne
solution sur le fond, mais moins de marge pour un projet de
loisir.

**Supabase Auth écarté** : écosystème orienté JavaScript et
PostgreSQL. Importer cet écosystème dans un projet PHP / Symfony
/ MariaDB introduirait une dépendance hétérogène pour un seul
besoin. Free tier généreux, mais le coût d'intégration est
disproportionné par rapport à l'alternative.

## Décision

**Logto Cloud** sur le plan Free, **tenant hébergé en région EU
(Netherlands)**, intégré au backend Symfony via le **SDK PHP
officiel** (`composer require logto/sdk`) adapté à Symfony
Security.

## Grandes lignes d'intégration

L'intégration ne fait pas l'objet d'un bundle Symfony de
référence. Elle reste donc à l'écrire en s'appuyant sur le
SDK PHP fourni et sur les composants `Symfony Security`. Les
grandes lignes anticipées :

- Création d'un **authenticator custom** héritant d'un
  authenticator Symfony approprié, qui orchestre le flux OIDC
  via le SDK Logto.
- **Adaptation de la persistance de session** du SDK Logto : le
  SDK utilise par défaut la session PHP native (`$_SESSION`),
  alors que Symfony gère sa propre session via le `RequestStack`.
  Une implémentation de stockage compatible Symfony sera fournie.
- **Routes dédiées** : une route d'initiation du flux par
  fournisseur (lien « Se connecter avec Google » / « Se
  connecter avec Apple »), une route de callback après retour
  du fournisseur.
- **Mapping `(provider, sub OIDC)` vers l'identifiant interne
  de l'aggregate Utilisateur** d'Abomey. Création silencieuse
  au premier login, lookup direct ensuite. Resynchronisation
  systématique du nom et de l'email à chaque connexion.
- **Secrets** (`client_id`, `client_secret` Logto) configurés
  via les variables d'environnement Symfony, jamais commitées.
- **Configuration de deux applications Logto par environnement**
  (web app pour Abomey, M2M éventuelle si besoin) à arbitrer au
  moment de l'implémentation.

Le détail concret (signatures de classes, structure des routes,
mapping exact) relève de l'implémentation et sera tranché au
moment de la tranche 1 du sujet « Comptes utilisateurs ». Si la
mécanique d'intégration s'avère plus complexe que prévu, une
ADR fille pourra documenter l'approche définitive.

## Conséquences

### Positives

- Pas de mot de passe stocké côté Abomey. Le périmètre RGPD est
  significativement réduit.
- Délégation de la sécurité d'authentification à un service
  spécialisé (SOC 2 Type II, TLS, chiffrement au repos,
  isolation entre clients).
- Couverture immédiate de Google et Apple. Possibilité d'ajouter
  d'autres fournisseurs (parmi les 30+ connecteurs Logto) sans
  refondre l'intégration.
- Caractère open-source de Logto : porte de sortie self-host
  documentée en cas de changement défavorable du Free tier.
- Région EU disponible : les données restent dans l'Union
  européenne, ce qui simplifie significativement la conformité
  RGPD.
- Multi-tenant gratuit (jusqu'à 10 tenants par compte) :
  Logto pourra être réutilisé pour d'autres projets sans
  multiplier les comptes.

### Négatives

- **Dépendance fonctionnelle** : une indisponibilité de Logto
  rend toute connexion à Abomey impossible. Mitigation : la
  spec accepte cet état, l'utilisateur est informé via un
  message d'erreur explicite.
- **Pas de bundle Symfony officiel** : l'intégration au stack
  Symfony reste à écrire (authenticator custom, storage de
  session, mapping `sub` → User). Coût estimé : quelques jours
  d'écriture et de mise au point lors de la tranche 1.
- **Sign in with Apple impose un compte développeur Apple**
  (~99 USD par an) pour s'enregistrer comme « service » auprès
  d'Apple. Coût administratif et financier à anticiper.
- **Sous-traitant RGPD** : Logto et ses propres sous-processors
  doivent figurer dans la politique de confidentialité d'Abomey.
  La chaîne est publiée publiquement sur
  [trust.logto.io](https://trust.logto.io/) et doit être suivie.
- **Plafonds du Free tier à surveiller** : 50 000 MAU,
  3 applications par tenant, 3 connecteurs sociaux, 1
  collaborateur. La limite de 3 applications par tenant
  correspond pile au triplet dev / staging / prod, sans marge.

### Obligations à honorer

- Créer le tenant Logto en **région EU (Netherlands)** à
  l'initialisation du projet, et non en US par défaut.
- Lister explicitement Logto comme sous-traitant dans la
  **politique de confidentialité d'Abomey** (sera livrée dans la
  tranche 4 du sujet « Comptes utilisateurs »).
- **Répercuter la liste des sous-processors de Logto** dans
  cette politique de confidentialité, et la mettre à jour si
  Logto en modifie la composition.
- Privilégier l'usage des standards **OIDC** plutôt que des
  spécificités propriétaires du SDK Logto, partout où c'est
  possible, pour faciliter une migration éventuelle.
- Stocker `client_id` et `client_secret` Logto dans le coffre
  de secrets Symfony, jamais dans le dépôt.

### Plan de mitigation à long terme

- Surveiller annuellement les conditions tarifaires de Logto et
  l'évolution du Free tier.
- Maintenir l'option de migration vers **Logto OSS self-hosté**
  comme plan B documenté ; le code applicatif reste compatible
  puisque le contrat d'interface est OIDC standard.
- Si l'authentification ou la gestion des comptes gagnent en
  complexité au point de justifier un bounded context séparé
  « Identity & Access » (voir `docs/domain.md`, section « Choix
  de modélisation »), réinterroger ce choix.

## Sources

- [Logto Cloud Trust & Security](https://logto.io/trust)
- [Logto Tenant Settings](https://docs.logto.io/logto-cloud/tenant-settings)
- [Logto Pricing](https://logto.io/pricing)
- [Logto PHP Quick Start](https://docs.logto.io/quick-starts/php)
- [Logto Privacy Policy](https://logto.io/terms/privacy-policy)
- [Logto Trust Center (subprocessors)](https://trust.logto.io/)
- [Logto pricing plan updates — September 2025](https://blog.logto.io/pricing-sep-2025)
