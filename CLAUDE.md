# Projet Abomey

## Nature du projet

Abomey est une application personnelle de gestion de parties de
jeux de cartes à points, centrée actuellement sur le tarot. C'est
un projet non authentifié, à usage solo ou en groupe restreint.

Le domaine, le vocabulaire métier et les choix de modélisation
sont décrits dans les documents de `docs/` (voir ci-dessous). Ce
CLAUDE.md ne les duplique pas.

## Sources de connaissance du domaine

Avant toute discussion qui touche au domaine métier (concepts du
tarot, règles, modélisation), tu consultes systématiquement les
documents suivants :

- `docs/glossary.md` — vocabulaire métier (ubiquitous language)
- `docs/domain.md` — description du domaine et règles
  structurantes
- `docs/scoring.md` — formules de calcul des scores (à créer
  quand l'implémentation du scoring approchera)

Tu utilises les termes tels qu'ils sont définis dans le glossaire,
sans substituer tes propres formulations.

## Décisions architecturales

Les décisions architecturales du projet sont documentées dans
`adr/`. Avant toute proposition structurante, tu consultes les
ADR existantes pour éviter les contradictions.

## Stack technique

- PHP dernière version stable
- Symfony dernière version stable
- MariaDB pour la persistance
- Twig pour les templates
- Symfony UX (Live Components, Twig Components, Native) et
  Stimulus pour l'interactivité côté client

Les conventions PHP et Symfony générales vivent dans les skills
dédiés de Teg (`php-conventions`, `symfony-patterns`), pas dans
ce CLAUDE.md.

## Tests

Le projet exige deux niveaux de tests :

- **Tests unitaires** sur l'intégralité du domaine. Toute règle
  métier formalisée dans `docs/domain.md` ou `docs/scoring.md`
  doit être couverte par un test. Les tests servent de
  spécification exécutable des règles.
- **Tests end-to-end** sur les pages, avec Symfony Panther.
  Chaque parcours utilisateur significatif doit avoir un test
  e2e.

Tu ne produis pas de code métier sans proposer les tests
associés.

## Comportement attendu

Avant toute modification du domaine, tu vérifies la cohérence
avec `docs/glossary.md` et `docs/domain.md`. En cas de
divergence entre une proposition et ces documents, tu signales
explicitement la divergence et on tranche ensemble : soit on
met à jour la documentation, soit on corrige la proposition. On
ne laisse pas pourrir.
