# Task 6 — Donne Vachette

> Sujet : [product/saisie-donnes.md](../../product/saisie-donnes.md)

## Règles métier couvertes
D12 (la Donne Vachette est définie par un classement strict
des Joueurs actifs — une position unique de 1 à N, N = Mode —
et un barème fixe par Mode).

## Critères d'acceptation

- *Scénario : Vachette à 4 joueurs*
  - Given une Partie en Tarot à 4 (Alice, Bob, Charlie, David)
  - When l'Utilisateur ajoute une Vachette avec le classement
    Alice 1re, Bob 2e, Charlie 3e, David 4e
  - Then Alice marque +120, Bob +60, Charlie −60, David −120
    (somme nulle), et la Vachette apparaît dans le tableau
    cumulatif comme une Donne

- *Scénario : Vachette à 3 joueurs*
  - Given une Partie en Tarot à 3 (Alice, Bob, Charlie)
  - When l'Utilisateur saisit le classement Alice 1re, Bob 2e,
    Charlie 3e
  - Then Alice marque +120, Bob 0, Charlie −120

- *Scénario : Vachette à 5 joueurs*
  - Given une Partie en Tarot à 5
  - When l'Utilisateur saisit le classement complet de 1 à 5
  - Then le barème +120 / +60 / 0 / −60 / −120 est appliqué
    selon les positions

- *Scénario : le classement est strict (D12)*
  - Given la saisie d'une Vachette
  - When l'Utilisateur attribue la même position à deux Joueurs,
    ou laisse un Joueur actif sans position
  - Then la Donne est refusée : chaque Joueur actif doit porter
    une position unique de 1 à N

- *Scénario : accès au flux Vachette*
  - Given la page d'une Partie
  - When l'Utilisateur veut saisir une Vachette
  - Then un bouton « Ajouter une Vachette » est disponible sur
    la page Partie

## Definition of Ready
- Cartes rouges produit concernées fermées ✓ — la question Qo9
  (mode de saisie du classement : drag-and-drop, sélections
  successives, autre) est une question d'exécution UI, renvoyée
  à `technical-plan`.
- Au moins un scénario concret ✓

## Reporté
- Correction (T7).
- Identification du gagnant du dernier Pli en Vachette
  (hors-scope du sujet).

## Plan technique
Pré-requis : reporter le barème Vachette de D12 dans
`docs/scoring.md` — +120/0/−120 à 3 ; +120/+60/−60/−120
à 4 ; +120/+60/0/−60/−120 à 5.

_(reste à remplir par `technical-plan` au moment de la mise en production)_
