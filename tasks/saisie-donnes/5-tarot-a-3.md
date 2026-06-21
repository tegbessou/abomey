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
Pré-requis (fait) : `docs/scoring.md` complété avec la
répartition à 3 joueurs — Preneur +2×, deux Défenseurs −×
chacun. Somme nulle conservée.

### Bounded context
Tarot.

### Domaine
- `Game` (existant), `Deal` (existant) — référencés, **non
  modifiés**. `Deal::pointsByPlayer()` couvre déjà le Mode 3
  par la formule générique (`defendersCount = count(actifs)
  − 1` = 2, pas de Partenaire). D11 (pas de Partenaire à 3)
  est satisfaite par construction : `partnerId` reste `null`.
- Aucun nouvel agrégat, value object ou exception.

### Application
- `RecordClassicDealCommand` / `RecordClassicDealCommandHandler`
  (existants) — inchangés.

### Ports & adaptateurs
- Aucun changement. Pas de migration (colonnes inchangées).

### Forme de la tranche
La tranche est essentiellement une **ouverture d'accès** :
- UI : le bouton « Ajouter une Donne » de `show.html.twig`
  passe de `game.mode in [4, 5]` à inclure `3`.
- Le formulaire existant fonctionne tel quel : `partnerId`
  conditionné à `mode == 5` (donc absent à 3), Morts
  conditionnés à `tablée > mode`.

### Couverture de test
- Unit : un test de spécification D11 sur `Game`
  (Tarot à 3, Preneur seul → +2×/−1×). Régression probable
  (comportement déjà couvert) — signal légitime de « déjà
  couvert », pas un cycle à forcer.
- e2e Panther : parcours de saisie d'une Donne en Tarot à 3.

### Contact avec le noyau partagé
- Introduit : rien.
- Consomme : `Game`, `Deal`, `RecordClassicDealCommand`,
  `RecordClassicDealCommandHandler`, le formulaire de Donne.
