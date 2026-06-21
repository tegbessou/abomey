# Saisie et calcul des Donnes

## Contexte

Une Partie de tarot prend sa valeur quand on y enregistre des
Donnes successives. Sans saisie de Donne, la Partie créée
reste vide et l'investissement de l'Utilisateur dans
l'inscription, la création de Joueurs et la création de
Partie ne débouche sur rien. Ce sujet débloque toute la
valeur du produit : la saisie en direct, autour de la table,
des Donnes jouées, avec calcul automatique des Scores selon
les règles FFT et historique cumulé par Partie.

## Utilisateurs

Un Utilisateur authentifié et consentant, propriétaire d'une
Partie qu'il a créée, autour d'une table de jeu, smartphone
ou ordinateur à portée. La saisie a lieu en direct entre
deux Donnes, pendant la soirée. Fréquence typique : 15 à 30
saisies dans une soirée.

## Objectif

Permettre à l'Utilisateur d'enregistrer chaque Donne jouée
pendant la soirée, en moins d'une minute par Donne, et de
voir à tout moment le tableau cumulé des Scores par Joueur
depuis le début de la Partie. Le calcul des Scores est
entièrement automatisé.

## Scénario principal

Cas le plus courant : Donne classique à 4 joueurs, tablée
égale au Mode (pas de Mort).

1. L'Utilisateur est sur la page détail d'une Partie qu'il a
   créée.
2. Il clique sur « Ajouter une Donne classique » (un bouton
   distinct existe pour « Ajouter une Vachette », traité
   en variante).
3. Il choisit le **Preneur** parmi les Joueurs actifs.
4. Il choisit le **Contrat** : Garde, Garde Sans ou Garde
   Contre.
5. Il indique le **nombre de Bouts** du Preneur en fin de
   Donne : 0, 1, 2 ou 3.
6. Il saisit les **points réalisés** par le Preneur, entier
   entre 0 et 91.
7. Il complète les **primes** éventuelles :
   - **Petit au Bout** : aucun, côté Preneur, ou côté
     Défense.
   - **Poignée(s)** : zéro ou plusieurs, chacune avec le
     Joueur annonceur et la taille (Simple, Double, Triple).
   - **Chelem** : aucun, réalisé non annoncé, annoncé et
     réalisé, ou annoncé non réalisé.
   - **Misère(s)** : zéro ou plusieurs, chacune avec le
     Joueur annonceur et le type (Atouts, Tête).
8. Il valide. Abomey calcule le Score selon les règles FFT,
   persiste la Donne et l'ajoute en fin de tableau sur la
   page Partie. Le total cumulé par Joueur est recalculé.

Sur la page Partie, les Donnes sont affichées sous forme de
tableau : une ligne par Donne, une colonne par Joueur
participant. La dernière ligne du tableau affiche le total
cumulé par Joueur.

## Variantes et cas limites

### Donne classique à 5 joueurs (Partenaire ou Preneur seul)

Après le choix du Preneur, étape supplémentaire : choisir le
**Partenaire** parmi les autres Joueurs actifs, ou « Preneur
seul ». Le Partenaire ne peut pas être le Preneur. Le reste
du déroulé est inchangé.

### Donne classique à 3 joueurs

Pas de notion de Partenaire (le Preneur joue toujours seul
contre la Défense). Le reste du déroulé est inchangé.

### Tablée supérieure au Mode (Mort)

Quand la Partie a plus de Joueurs participants que son Mode
(par exemple 6 Joueurs en Tarot à 4), une étape préalable
apparaît : l'Utilisateur désigne manuellement le ou les
Morts parmi les Joueurs participants. Le nombre de Morts à
désigner est imposé par la différence `tablée - Mode`. Pas
de rotation automatique. Les Morts désignés ne reçoivent ni
ne perdent de points sur cette Donne.

### Donne Vachette

Au lieu de « Ajouter une Donne classique », l'Utilisateur
clique sur « Ajouter une Vachette ». Le déroulé est
radicalement plus court :

1. Désignation du ou des Morts si tablée > Mode (identique
   aux Donnes classiques).
2. Saisie d'un **classement strict** des Joueurs actifs :
   chaque Joueur actif reçoit une position unique de 1 à N
   (où N est le Mode).
3. Validation. Abomey applique le barème fixe selon le Mode
   et persiste.

Le départage entre Joueurs à scores bruts égaux n'est pas
géré par Abomey. C'est aux Joueurs autour de la table de se
mettre d'accord sur l'ordre du classement saisi.

### Correction de la dernière Donne

L'Utilisateur peut modifier la dernière Donne saisie de la
Partie à tout moment, depuis la page Partie. Un bouton
« Modifier la dernière Donne » ouvre le formulaire
pré-rempli avec les valeurs courantes. Tout est modifiable,
y compris le type (basculer Classique ↔ Vachette). Les
règles métier s'appliquent à la correction comme à la
saisie initiale : une correction qui produirait une Donne
invalide est rejetée. Les Donnes antérieures sont figées et
ne peuvent pas être modifiées.

### Abandon d'une saisie en cours

Si l'Utilisateur ferme le formulaire ou change de page avant
de valider, la saisie en cours est perdue. Aucune
persistance de brouillon, ni côté serveur, ni côté
appareil.

## Règles métier

Numérotation D1 à D26, regroupées par bloc.

### Cadre général

**D1** — Le nombre de Joueurs actifs d'une Donne est exactement
égal au Mode de tarot de la Partie.

**D9** — Si la tablée dépasse le Mode, l'Utilisateur désigne
manuellement le ou les Morts pour chaque Donne. Pas de
rotation automatique.

**D21** — Chaque Donne porte un numéro d'ordre
auto-incrémenté dans sa Partie, immuable, reflétant la
chronologie de saisie.

**D22** — Les Joueurs actifs et les Morts d'une Donne sont
des Joueurs participants à la Partie. Aucun Joueur étranger
à la Partie ne peut figurer dans une Donne.

**D23** — Le Mode de tarot d'une Donne est celui de sa
Partie. Pas de variation par Donne.

**D24** — Une Donne n'est persistée qu'une fois toutes ses
règles métier respectées. Pas de saisie partielle
persistée.

**D25** — Le propriétaire d'une Donne est implicitement
celui de sa Partie. Pas de propriétaire propre à la Donne.

**D26** — Le calcul du Score s'effectue après validation
complète de la Donne. Pas de calcul ni d'aperçu en cours de
saisie.

### Donne classique

**D2** — Le Preneur est un Joueur actif de la Donne.

**D3** — Le Contrat appartient à `{Garde, Garde Sans, Garde
Contre}`. La « Petite » est explicitement exclue.

**D4** — Le nombre de Bouts du Preneur appartient à
`{0, 1, 2, 3}`.

**D5** — Les points réalisés par le Preneur sont un entier
compris entre 0 et 91 inclus.

**D6** — Le Score d'une Donne classique est calculé
automatiquement selon la formule FFT
`(25 + écart) × multiplicateur de Contrat + primes`. Le
détail des multiplicateurs, des buts par Bout et des primes
est formalisé dans `docs/scoring.md`.

**D7** — Les Morts d'une Donne ne reçoivent ni ne perdent
de points sur cette Donne.

**D10** — En Tarot à 5, la Donne classique a soit un
Partenaire (Joueur actif différent du Preneur), soit le
Preneur joue seul.

**D11** — En Tarot à 3 et à 4, la Donne classique n'a pas
de notion de Partenaire. Le Preneur joue toujours seul
contre la Défense.

### Primes des Donnes classiques

**D14** — Petit au Bout : valeur dans
`{aucun, Preneur, Défense}`. Si non « aucun », prime de 10
points multipliée par le multiplicateur de Contrat,
attribuée au camp désigné.

**D15** — Poignée(s) : zéro ou plusieurs entrées par
Donne, chacune composée d'un Joueur actif annonceur et d'une
taille dans `{Simple, Double, Triple}`. La prime est fixe
par taille (20, 30, 40 points respectivement), attribuée
au camp gagnant la Donne, indépendamment de qui l'a
annoncée. La somme des primes Poignée s'ajoute au calcul.

**D16** — Chelem : valeur dans
`{aucun, réalisé non annoncé, annoncé et réalisé, annoncé
non réalisé}`. Prime de +400 (annoncé et réalisé), +200
(réalisé non annoncé), pénalité de −200 (annoncé non
réalisé). Attribuée au Preneur.

**D17** — Misère(s) : zéro ou plusieurs entrées par
Donne, chacune composée d'un Joueur actif annonceur et d'un
type dans `{Atouts, Tête}`. La paire (annonceur, type) est
unique par Donne ; un même Joueur peut annoncer les deux
types. Pour chaque Misère, l'annonceur reçoit
`+10 × (Mode − 1)` points, chacun des autres Joueurs actifs
reçoit −10. La prime est indépendante du Contrat, du
résultat de la Donne, et de la présentation de la main.
Annonce sur l'honneur, sans pénalité d'oubli.

Définitions :

- **Misère d'Atouts** : aucun atout dans la main du Joueur.
- **Misère de Tête** : aucune figure (Roi, Dame, Cavalier)
  dans la main du Joueur.

### Donne Vachette

**D12** — La Donne Vachette est définie par un classement
strict des Joueurs actifs : chaque Joueur actif reçoit une
position unique de 1 à N (où N est le Mode). Le Score est
calculé selon un barème fixe par Mode :

- À 3 joueurs : +120 / 0 / −120 pour les positions 1, 2, 3.
- À 4 joueurs : +120 / +60 / −60 / −120 pour les positions
  1, 2, 3, 4.
- À 5 joueurs : +120 / +60 / 0 / −60 / −120 pour les
  positions 1, 2, 3, 4, 5.

### Correction d'une Donne

**D18** — Seule la dernière Donne saisie de la Partie est
corrigible. Toutes les Donnes antérieures sont figées. La
correction porte sur n'importe quel champ, y compris le
type (Classique ↔ Vachette).

**D19** — Une Donne ne peut pas être supprimée. Une
correction ne peut pas mettre une Donne dans un état où
elle violerait les règles D1 à D17 ; sinon elle est
rejetée.

**D20** — Les Scores cumulés affichés sur la page Partie
sont dérivés des Donnes en temps réel, jamais stockés
indépendamment. Une correction de Donne entraîne un
recalcul implicite à l'affichage suivant.

## Hors-scope

- Statistiques cross-Donnes ou cross-Parties au-delà du
  cumul affiché sur la page Partie. La v1 se limite au
  cumul d'une seule Partie (cohérent avec `docs/domain.md`).
- Modification d'une Donne autre que la dernière. Compromis
  assumé : une erreur découverte tardivement n'est pas
  corrigible.
- Suppression d'une Donne (cf. D19).
- Saisie en différé après la soirée (posture en direct
  retenue).
- Saisie collaborative multi-utilisateurs sur une même
  Partie (cohérent avec R4 de #002 : propriétaire unique).
- Modélisation des cartes individuelles et des Plis
  (cohérent avec `docs/domain.md`).
- Désignation automatique des Morts par rotation (cf. D9).
- Aperçu du Score en cours de saisie (cf. D26).
- « Petite » comme Contrat (cf. D3).
- Autres types de Misères que `Atouts` et `Tête` (Misère de
  Bouts, Misère de Couleur, etc.).
- Identification du gagnant du dernier Pli en Vachette
  (information non saisie ni stockée).
- Validation « somme des points = 91 » en Vachette
  (sans objet : la saisie est en classement, pas en points
  bruts).
- Capture de l'annonceur du Chelem séparée de la prime (on
  capture le Preneur implicitement, l'annonce est portée par
  D16).
- Export, partage ou impression d'une Partie ou d'une
  Donne.
- Historique ou audit des modifications de Donne.
- Persistance d'un brouillon de saisie de Donne (cf. D24).

## Critère de succès

Une soirée complète de tarot (15 à 30 Donnes), avec ou sans
Vachettes, à 3, 4 ou 5 joueurs, peut être tenue
intégralement sur Abomey sans recours à une application
tierce ni à un calcul manuel, avec des Scores corrects,
sans abandon en cours de soirée. Le dogfooding est conduit
par l'auteur du projet en première instance.

## Dépendance préalable au découpage

Avant d'attaquer la phase 4, formaliser dans
`docs/scoring.md` :

- La formule FFT classique : multiplicateurs de Contrat,
  buts par nombre de Bouts, calcul du Score de base, et
  répartition entre Preneur, Partenaire éventuel et
  Défense.
- Le barème Vachette par Mode.
- L'application de chaque prime (Petit au Bout, Poignée,
  Chelem, Misère) sur le Score final.

Sans ce document, les règles D6, D14, D15, D16, D17 et D12
ne peuvent être ni implémentées ni vérifiées par des tests.

## Questions UX ouvertes pour la phase 4

- **Qo4** — Choix du Preneur, du Partenaire, du Mort :
  composant UI à arbitrer (radio, liste, autre).
- **Qo9** — Saisie du classement Vachette : drag-and-drop,
  sélections successives, ou autre.
- **Qo11** — Bouton « Modifier la dernière Donne » : place
  exacte sur la page Partie, flux de retour après
  correction.

## Tasks

Découpage en tranches verticales livrables. L'état
d'avancement (livrée, en cours) vit dans `tracking/`, pas
ici. Le détail produit des tranches restant à livrer vit dans
`tasks/saisie-donnes/`.

- **T0 — Liste des Parties et navigation** — livrée.
- **T1 — Walking skeleton : Donne classique à 4, tablée =
  Mode, sans primes** — livrée.
- **T2a — Petit au Bout** — livrée.
- **T2b — Chelem** — livrée.
- **T2c — Poignée(s)** — livrée.
- **T2d — Misère(s)** — livrée.
- **T3 — [Mort manuel (tablée > Mode)](../tasks/saisie-donnes/3-mort-manuel.md)**
  — désignation manuelle du ou des Morts, neutralisés au score.
- **T4 — [Tarot à 5 (Partenaire ou Preneur seul)](../tasks/saisie-donnes/4-tarot-a-5.md)**
  — désignation du Partenaire ou jeu en solo, répartition à 5.
- **T5 — [Tarot à 3](../tasks/saisie-donnes/5-tarot-a-3.md)**
  — Donne classique à 3, Preneur seul contre deux Défenseurs.
- **T6 — [Donne Vachette](../tasks/saisie-donnes/6-donne-vachette.md)**
  — saisie au classement strict, barème fixe par Mode.
- **T7 — [Correction de la dernière Donne](../tasks/saisie-donnes/7-correction-derniere-donne.md)**
  — modification de la dernière Donne, recalcul du cumul.
