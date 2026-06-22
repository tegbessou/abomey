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
Pré-requis (fait) : barème Vachette par Mode reporté dans
`docs/scoring.md` (section « Donne Vachette (T6) »).

### Bounded context
Tarot.

### Domaine
La Donne devient une hiérarchie polymorphe (Single Table
Inheritance) : un type de Donne diverge de l'autre par ses
champs, sa validation **et** son calcul. Décision prise via
`evaluate-design-tension` (le conditionnel ne suffit pas : la
divergence est structurelle et multi-points).

- `Deal` (existant) → devient **abstrait**, entité interne de
  l'agrégat `Game`. Porte le commun : `game`, `position`, et
  une méthode abstraite `pointsByPlayer(): array<string, int>`.
- `ClassicDeal` (nouveau, **extrait** de l'actuel `Deal`) —
  tous les champs classiques (taker, contract, bouts, points,
  PAB, chelem, poignées, misères, partnerId, activePlayerIds)
  et le calcul actuel. Comportement classique **inchangé**
  (refactor sous le filet des tests existants).
- `VachetteDeal` (nouveau) — porte un `Ranking` et les
  `activePlayerIds`. Calcul = barème fixe par Mode (cf.
  `scoring.md`), sans écart ni multiplicateur ni prime.
- `Ranking` (nouveau value object) — classement strict : à
  chaque Joueur actif une position unique de 1 à N (N = Mode).
  Invariant porté ici, dans le domaine (pas dans l'UI) :
  positions uniques, contiguës de 1 à N, tous les actifs
  classés.
- `InvalidRankingException` (nouveau) — classement non strict
  (position dupliquée, manquante, ou Joueur actif sans
  position). Étend `\DomainException`.

### Application
- `RecordVachetteCommand` + `RecordVachetteCommandHandler`
  (nouveaux) sur `command.bus`. Distincts de
  `RecordClassicDeal` : saisie de nature différente.
- `Game::recordVachette(...)` (nouvelle méthode de l'agrégat)
  — valide la cohérence tablée/Mort (mêmes invariants D22/D1
  que le classique : actifs = Mode) puis crée un `VachetteDeal`.

### Ports & adaptateurs
- `GameRepository` (existant) — inchangé.
- Adaptateur Doctrine : passage de `deals` en Single Table
  Inheritance — `#[InheritanceType('SINGLE_TABLE')]`,
  `#[DiscriminatorColumn(name: 'type')]`,
  `#[DiscriminatorMap(['classic' => ClassicDeal, 'vachette' =>
  VachetteDeal])]` sur `Deal`. Migration : colonne
  discriminante `type` + colonne `ranking` (JSON, nullable) ;
  les colonnes classiques deviennent nullable (portées par le
  seul `ClassicDeal`).

### Domain events
Aucun.

### Forme de la tranche
UI (bouton « Ajouter une Vachette » sur la page Partie +
formulaire de saisie du classement — Qo9, mode de saisie
tranché au moment de l'UI) → `RecordVachetteCommand` →
`RecordVachetteCommandHandler` → `Game::recordVachette` →
`VachetteDeal` (calcul par `pointsByPlayer()`). Le cumul des
Scores (`ShowGameQueryHandler`) consomme `pointsByPlayer()`
de façon polymorphe — une Vachette apparaît dans le tableau
cumulatif comme une Donne, sans branchement de type côté
lecture.

### Contact avec le noyau partagé
- Introduit : hiérarchie `Deal` (abstrait) / `ClassicDeal` /
  `VachetteDeal`, value object `Ranking`,
  `InvalidRankingException`, `RecordVachetteCommand` +
  handler, `Game::recordVachette`.
- Consomme : `Game`, `Mode`, `GameRepository`, le calcul
  classique (inchangé, déplacé dans `ClassicDeal`).
