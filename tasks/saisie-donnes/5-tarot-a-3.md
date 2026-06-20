# Task 5 — Tarot à 3

> Sujet : [product/saisie-donnes.md](../../product/saisie-donnes.md)

## Règles métier couvertes
D11 (en Tarot à 3, la Donne classique n'a pas de Partenaire :
le Preneur joue seul contre la Défense), confirmée pour le
Mode 3 avec la répartition adaptée.

## Critères d'acceptation

- *Scénario : Donne classique à 3, Preneur réussit*
  - Given une Partie en Tarot à 3 (Alice, Bob, Charlie)
  - When Alice prend en Garde (1 Bout, 60 points réalisés →
    S = 34, Preneur réussit)
  - Then Alice marque +68, Bob −34 et Charlie −34 (somme nulle)

- *Scénario : Donne classique à 3, Preneur chute*
  - Given une Partie en Tarot à 3 (Alice, Bob, Charlie)
  - When Alice prend en Garde Sans (0 Bout, 50 points réalisés →
    E = −6, S = 62, Preneur chute)
  - Then Alice marque −124, Bob +62 et Charlie +62 (somme nulle)

- *Scénario : pas de Partenaire à 3 (D11)*
  - Given une Partie en Tarot à 3
  - When l'Utilisateur saisit une Donne classique
  - Then aucune étape de désignation de Partenaire n'apparaît ;
    le Preneur joue seul contre deux Défenseurs

## Definition of Ready
- Cartes rouges produit concernées fermées ✓ — aucune question
  produit ouverte sur cette tranche.
- Au moins un scénario concret ✓

## Reporté
- Vachette (T6), correction (T7).

## Plan technique
Pré-requis : compléter `docs/scoring.md` avec la répartition
à 3 joueurs — Preneur +2×, deux Défenseurs −× chacun. Somme
nulle conservée.

_(reste à remplir par `technical-plan` au moment de la mise en production)_
