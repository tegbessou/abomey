# Task 3 — Mort manuel (tablée supérieure au Mode)

> Sujet : [product/saisie-donnes.md](../../product/saisie-donnes.md)

## Règles métier couvertes
D9 (désignation manuelle du ou des Morts, pas de rotation
automatique), D22 (les Morts désignés sont des participants
de la Partie), D7 (les Morts ne reçoivent ni ne perdent de
points). Le verrou temporaire posé en T1
(`DeadPlayersNotYetSupportedException`) est levé.

## Critères d'acceptation

- *Scénario : désignation d'un Mort à 5 autour d'une table de Tarot à 4*
  - Given une Partie en Tarot à 4 réunissant 5 Joueurs (Alice,
    Bob, Charlie, David, Eve)
  - When l'Utilisateur démarre la saisie d'une Donne classique
    et désigne Eve comme Morte
  - Then seuls Alice, Bob, Charlie et David sont proposés comme
    Joueurs actifs (Preneur, Bouts, points), et la Donne se
    calcule comme une Donne à 4

- *Scénario : le Mort ne marque pas*
  - Given la Partie ci-dessus, Eve désignée Morte, Alice
    Preneur en Garde, 1 Bout, 60 points réalisés (S = 34,
    Preneur réussit)
  - When la Donne est validée
  - Then Alice marque +102, Bob/Charlie/David marquent −34
    chacun, Eve marque 0 ; le Score cumulé d'Eve est inchangé
    par cette Donne

- *Scénario : deux Morts à 6 autour d'une table de Tarot à 4*
  - Given une Partie en Tarot à 4 réunissant 6 Joueurs
  - When l'Utilisateur désigne deux Morts pour la Donne
  - Then quatre Joueurs actifs restent et la Donne se calcule à 4

- *Scénario : le verrou de T1 est levé*
  - Given une Partie dont la tablée dépasse le Mode
  - When l'Utilisateur ouvre l'ajout d'une Donne
  - Then le formulaire est accessible ; le message « pas encore
    supporté » affiché en T1 a disparu

- *Scénario : un Mort doit être un participant de la Partie (D22)*
  - Given la saisie d'une Donne
  - When l'Utilisateur désigne les Morts
  - Then seuls les participants de la Partie sont proposés ;
    aucun Joueur étranger à la Partie ne peut être désigné

## Definition of Ready
- Cartes rouges produit concernées fermées ✓ — la question Qo4
  (composant de désignation du Mort) est une question
  d'exécution UI, renvoyée à `technical-plan`, pas une question
  produit ouverte.
- Au moins un scénario concret ✓

## Reporté
- Désignation automatique des Morts par rotation (hors-scope du
  sujet, cf. D9).
- Autres Modes (Tarot à 5 → T4, à 3 → T5), Vachette (T6),
  correction (T7).

## Plan technique

### Bounded context
Tarot.

### Domaine
- Agrégat `Game` (existant) — `recordClassicDeal` reçoit un
  nouveau paramètre `deadPlayerIds: list<string>`. La méthode
  valide D22 (chaque ID de mort doit figurer dans
  `participantIds`) puis calcule
  `activePlayerIds = participantIds − deadPlayerIds` avant de
  passer à `Deal::createClassic`. La validation D1
  (`count(activePlayerIds) == mode.value`) reste dans
  `Deal::createClassic` et n'est pas dupliquée.
- Entité enfant `Deal` (existante) — aucun changement de
  structure. `activePlayerIds` reçoit désormais les joueurs
  actifs réels quand la tablée dépasse le Mode.
- Exception `DeadPlayerNotParticipantException` (nouvelle,
  `Domain\Game`, étend `\DomainException`) — levée par
  `Game::recordClassicDeal` si un ID de mort ne figure pas
  dans `participantIds`.
- `DeadPlayersNotYetSupportedException` — supprimée (code
  mort après levée de la garde).

### Application
- `RecordClassicDealCommand` (existant) — nouveau champ
  `deadPlayerIds: list<string>`. Liste vide quand tablée =
  Mode : comportement T1/T2 préservé sans condition.
- `RecordClassicDealCommandHandler` (existant) — transmet
  `deadPlayerIds` à `game->recordClassicDeal()`.

### Ports & adaptateurs
Aucun port nouveau. `GameRepository::update` (existant) —
`Deal::$activePlayerIds` est déjà mappé Doctrine (T1) ; pas
de migration.

### Domain events
Aucun.

### Forme de la tranche
`ShowRecordClassicDealFormController` (GET) passe les
participants de la `GameView` comme option au form type →
`RecordClassicDealFormType` ajoute un `ChoiceType`
multi+expanded (checkboxes) sur les participants, rendu
uniquement quand `count(participants) > mode` → POST
`RecordClassicDealController` (le verrou de redirection
quand tablée > Mode est supprimé ici) →
`RecordClassicDealCommandHandler` → `Game::recordClassicDeal`
→ `Deal::createClassic` → `GameRepository::update`.

### Contact avec le noyau partagé
- Introduit : `DeadPlayerNotParticipantException`.
- Modifié : `Game::recordClassicDeal`, `RecordClassicDealCommand`,
  `RecordClassicDealCommandHandler`, `RecordClassicDealFormData`,
  `RecordClassicDealFormType`, `ShowRecordClassicDealFormController`,
  `RecordClassicDealController`.
- Supprimé : `DeadPlayersNotYetSupportedException`.
