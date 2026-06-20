# Task 4 — Tarot à 5 (Partenaire ou Preneur seul)

> Sujet : [product/saisie-donnes.md](../../product/saisie-donnes.md)

## Règles métier couvertes
D10 (en Tarot à 5, la Donne classique a soit un Partenaire —
Joueur actif différent du Preneur —, soit le Preneur joue seul).

## Critères d'acceptation

- *Scénario : Donne classique à 5 avec Partenaire*
  - Given une Partie en Tarot à 5 (Alice, Bob, Charlie, David,
    Eve)
  - When Alice prend en Garde (1 Bout, 60 points réalisés →
    S = 34, Preneur réussit) et désigne Bob comme Partenaire
  - Then Alice marque +68, Bob +34, et Charlie/David/Eve −34
    chacun (somme nulle)

- *Scénario : Donne classique à 5, Preneur seul*
  - Given une Partie en Tarot à 5
  - When Alice prend en Garde (S = 34, Preneur réussit) et
    choisit « Preneur seul »
  - Then Alice marque +136 et les quatre autres Joueurs −34
    chacun (somme nulle)

- *Scénario : étape de désignation du Partenaire*
  - Given la saisie d'une Donne classique en Tarot à 5
  - When l'Utilisateur a choisi le Preneur
  - Then une étape permet de désigner le Partenaire (Joueur
    actif distinct du Preneur) ou « Preneur seul », avant le
    choix du Contrat

- *Scénario : le Partenaire est distinct du Preneur (D10)*
  - Given Alice désignée Preneur
  - When l'Utilisateur désigne le Partenaire
  - Then Alice n'est pas proposée comme son propre Partenaire

## Definition of Ready
- Cartes rouges produit concernées fermées ✓ — la question Qo4
  (composant de désignation du Partenaire) est une question
  d'exécution UI, renvoyée à `technical-plan`.
- Au moins un scénario concret ✓

## Reporté
- Tarot à 3 (T5), Vachette (T6), correction (T7).

## Plan technique

### Bounded context
Tarot.

### Domaine
- `Deal` (existant, T1) — étendu : reçoit `partnerId: ?string`
  à la création. `pointsByPlayer()` utilise `partnerId` pour
  déterminer la répartition quand `activePlayerIds` compte
  5 entrées : Preneur ×2, Partenaire ×1, 3 Défenseurs −1
  chacun ; ou Preneur seul ×4, 4 Défenseurs −1 chacun si
  `partnerId` est null.
- `Game` (existant, T1) — `recordClassicDeal` reçoit
  `partnerId: ?string`. Invariants D10 validés avant délégation
  à `Deal::createClassic` : si non-null, le Partenaire doit
  être dans `activePlayerIds` et différent du Preneur.
- `PartnerMustBeActivePlayerException` (nouveau) — le
  Partenaire désigné n'est pas un Joueur actif de la Donne.
- `PartnerCannotBeTakerException` (nouveau) — le Partenaire
  désigné est le Preneur.

### Application
- `RecordClassicDealCommand` (existant) — ajout de
  `partnerId: ?string`.
- `RecordClassicDealCommandHandler` (existant) — passe
  `partnerId` à `Game::recordClassicDeal`.

### Ports & adaptateurs
- `GameRepository` (existant) — inchangé.
- Adaptateur Doctrine : migration pour ajouter la colonne
  `partner_id VARCHAR(36) NULL` sur la table `deals`.

### Forme de la tranche
UI (ChoiceType conditionnel `partnerId` quand mode == 5,
Preneur exclu des options) → `RecordClassicDealFormData`
(`partnerId: ?string`) → `RecordClassicDealController` →
`RecordClassicDealCommand` → `RecordClassicDealCommandHandler`
→ `Game::recordClassicDeal` → `Deal::createClassic` (stocke
`partnerId`, répartition par `pointsByPlayer()`).

Le bouton « Ajouter une Donne classique » dans `show.html.twig`
passe de `game.mode == 4` à `game.mode in [4, 5]`.

### Contact avec le noyau partagé
- Introduit : `PartnerMustBeActivePlayerException`,
  `PartnerCannotBeTakerException`.
- Consomme : `Deal`, `Game`, `RecordClassicDealCommand`,
  `RecordClassicDealCommandHandler`, `RecordClassicDealFormData`,
  `RecordClassicDealFormType`.
