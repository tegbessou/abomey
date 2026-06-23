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

### Bounded context
Tarot.

### Domaine
- `Game` (existant) — **nouvelles opérations** :
  `correctLastDealAsClassic(...)` (mêmes paramètres que
  `recordClassicDeal`) et `correctLastDealAsVachette(deadPlayerIds,
  ranking)`. Chacune **remplace** la dernière Donne par une
  nouvelle, à la même position. La correction est un
  remplacement, pas une mutation : cohérent avec les Donnes
  immuables et le Score dérivé. La bascule Classique ↔ Vachette
  (D18) est ainsi naturelle (remplacement par un autre sous-type).
- **Atomicité (D19)** : la nouvelle Donne est **construite et
  validée avant** de toucher la collection. Refactor : extraire
  de `recordClassicDeal` / `recordVachette` une construction
  validée sans ajout (`buildClassicDeal(position, ...)` /
  `buildVachetteDeal(position, ...)`), réutilisée par `record…`
  (ajout en fin) et `correctLast…` (remplacement). Si la
  construction lève (D1–D17 violées), la collection reste
  intacte → la Donne conserve son état.
- **D18 (seule la dernière)** garanti par l'API : aucune
  opération ne corrige une Donne par position ; seules
  `correctLast…` existent.
- `NoDealToCorrectException` (nouveau) — `correctLast…` appelée
  sur une Partie sans Donne. Étend `\DomainException`.
- `Deal` / `ClassicDeal` / `VachetteDeal` / `Ranking` (existants,
  STI) — référencés.

### Application
- `CorrectLastClassicDealCommand` + handler et
  `CorrectLastVachetteCommand` + handler (nouveaux) sur
  `command.bus`. Parallèles aux commandes `Record…`.

### Ports & adaptateurs
- `GameRepository` (existant) — inchangé.
- Pas de migration : le remplacement (y compris bascule de
  type STI) passe par `orphanRemoval` déjà en place sur
  `Game.deals` (l'ancienne ligne est supprimée, la nouvelle
  insérée avec son discriminant).

### Domain events
Aucun.

### Forme de la tranche
UI (bouton « Modifier la dernière Donne », visible si la Partie
a au moins une Donne ; formulaire de correction pré-rempli avec
la dernière Donne, autorisant le changement de champs et la
bascule de type — Qo11, emplacement du bouton et flux de retour
tranchés au moment de l'UI) → `CorrectLast…Command` → handler →
`Game::correctLast…` → remplacement de la dernière Donne. Le
cumul (`ShowGameQueryHandler`) reflète la correction sans code
dédié (Score dérivé).

### Contact avec le noyau partagé
- Introduit : `Game::correctLastDealAsClassic` /
  `correctLastDealAsVachette`, `buildClassicDeal` /
  `buildVachetteDeal` (extraits), `NoDealToCorrectException`,
  `CorrectLastClassicDealCommand` + handler,
  `CorrectLastVachetteCommand` + handler.
- Consomme : `Game`, `Deal`/`ClassicDeal`/`VachetteDeal`,
  `Ranking`, la validation `record…` existante.
