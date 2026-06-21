# Task 7 — Correction de la dernière Donne

> Sujet : [product/saisie-donnes.md](../../product/saisie-donnes.md)

## Règles métier couvertes
D18 (seule la dernière Donne est corrigible, sur n'importe quel
champ y compris le type Classique ↔ Vachette), D19 (une Donne
n'est pas supprimable ; une correction qui violerait D1–D17 est
rejetée), D20 (les Scores cumulés sont dérivés des Donnes ;
une correction entraîne un recalcul implicite à l'affichage).

## Critères d'acceptation

- *Scénario : corriger les points de la dernière Donne*
  - Given une Partie en Tarot à 4 dont la dernière Donne est une
    Garde d'Alice à 60 points réalisés (S = 34, Preneur réussit,
    Alice +102, chaque Défenseur −34)
  - When l'Utilisateur ouvre « Modifier la dernière Donne » et
    change les points réalisés à 50 (E = −1, S = 26, Preneur
    chute)
  - Then le tableau cumulatif reflète Alice −78 et chaque
    Défenseur +26 pour cette Donne

- *Scénario : basculer Classique ↔ Vachette (D18)*
  - Given une Partie dont la dernière Donne est une Donne
    classique
  - When l'Utilisateur la corrige en Donne Vachette en
    saisissant un classement strict
  - Then la Donne devient une Vachette et le Score cumulé est
    recalculé en conséquence

- *Scénario : seule la dernière Donne est corrigible (D18)*
  - Given une Partie comportant au moins deux Donnes
  - When l'Utilisateur consulte la page Partie
  - Then seul un bouton « Modifier la dernière Donne » est
    offert ; les Donnes antérieures sont figées, sans bouton de
    correction

- *Scénario : une correction invalide est rejetée (D19)*
  - Given la dernière Donne d'une Partie en Tarot à 4
  - When l'Utilisateur la corrige dans un état qui violerait une
    règle D1–D17 (par exemple cinq Joueurs actifs, ou des points
    réalisés hors de l'intervalle 0–91)
  - Then la correction est refusée et la Donne conserve son état
    précédent

- *Scénario : le bouton n'apparaît qu'avec au moins une Donne*
  - Given une Partie sans aucune Donne saisie
  - When l'Utilisateur consulte la page Partie
  - Then aucun bouton « Modifier la dernière Donne » n'est offert

## Definition of Ready
- Cartes rouges produit concernées fermées ✓ — la question Qo11
  (emplacement du bouton et flux de retour après correction) est
  une question d'exécution UI, renvoyée à `technical-plan`.
- Au moins un scénario concret ✓

## Reporté
- Modification d'une Donne autre que la dernière (hors-scope du
  sujet, cf. D18).
- Suppression d'une Donne (hors-scope, cf. D19).
- Historique ou audit des modifications de Donne (hors-scope).

## Plan technique
_(rempli par `technical-plan` au moment de la mise en production)_
