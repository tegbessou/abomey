# Calcul des Scores

Ce document formalise les règles de calcul des Scores
d'une Donne dans Abomey. Il sert de référence
implémentable et testable pour les règles métier de calcul
introduites par le sujet #003.

Le document s'enrichit tranche par tranche, en lien avec
le découpage de `product/saisie-donnes.md`. Chaque ajout
référence la tranche concernée.

## Périmètre couvert

À ce jour, le document couvre les besoins des tranches
**T1**, **T2**, **T3**, **T4**, **T5** et **T6** :

- Donne classique
- Tarot à 4 joueurs, tablée égale au Mode (pas de Mort)
- Prime Petit au Bout (T2a)
- Prime Chelem (T2b)
- Prime Poignée(s) (T2c)
- Prime Misère(s) (T2d)
- Neutralisation du Mort (T3)
- Tarot à 5 avec Partenaire ou Preneur seul (T4)
- Tarot à 3, Preneur seul contre deux Défenseurs (T5)
- Donne Vachette, barème par Mode (T6)

Le document couvre désormais tout le scoring du sujet #003.

## Neutralisation du Mort (T3)

Lorsque la tablée dépasse le Mode, un ou plusieurs Joueurs
sont désignés Morts avant la Donne. Les Joueurs morts ne
participent pas à la répartition du Score. Leur résultat
pour la Donne est 0.

Le score de la Donne se calcule uniquement sur les Joueurs
actifs (les `activePlayerIds`), en nombre égal au Mode.

## Donne classique — base FFT

### Multiplicateurs de Contrat

| Contrat | Multiplicateur |
|---|---|
| Garde | 1 |
| Garde Sans | 2 |
| Garde Contre | 4 |

La « Petite » est exclue d'Abomey (cf. règle D3 de
`product/saisie-donnes.md`).

### Buts du Preneur selon le nombre de Bouts

Le but est le nombre de points que le Preneur doit
réaliser pour remporter sa Donne. Il dépend du nombre de
Bouts qu'il détient en fin de Donne.

| Nombre de Bouts | But |
|---|---|
| 0 | 56 |
| 1 | 51 |
| 2 | 41 |
| 3 | 36 |

### Saisie des points réalisés

Les points réalisés par le Preneur sont saisis comme un
entier entre 0 et 91 inclus. Au tarot, le total des points
sur l'ensemble des cartes vaut 91 ; le complément
`91 − points réalisés` est implicitement attribué à la
Défense.

Les éventuels demi-points sont arrondis avant saisie selon
la convention de la table. Abomey ne traite que des
entiers.

### Score d'une Donne classique (sans primes)

Soit :

- `R` les points réalisés par le Preneur,
- `B` le but du Preneur (fonction du nombre de Bouts),
- `M` le multiplicateur de Contrat,
- `E = R − B` l'écart entre points réalisés et but.

Le **Score de la Donne**, en valeur absolue, est :

```
Score = (25 + |E|) × M
```

Attribution :

- Si `E ≥ 0`, le Preneur réalise son contrat. Le Preneur
  remporte la Donne et reçoit le Score à son crédit. Le
  cas limite `E = 0` (points réalisés = but) compte comme
  réussite du Preneur.
- Si `E < 0`, le Preneur chute. La Défense remporte la
  Donne et reçoit le Score à son crédit.

### Répartition à 4 joueurs

À 4 joueurs, le Preneur est seul contre 3 Défenseurs. Le
Score est appliqué à chaque Joueur actif de la façon
suivante :

| Cas | Preneur | Chaque Défenseur |
|---|---|---|
| Preneur réussit | `+3 × Score` | `−Score` |
| Preneur chute | `−3 × Score` | `+Score` |

La somme des points distribués sur la Donne vaut toujours
zéro. Cette invariance est utilisable comme garde-fou de
test.

## Exemples chiffrés

### Exemple 1 — Garde, Preneur réussit

- Contrat : Garde (`M = 1`)
- Bouts : 1 → but `B = 51`
- Points réalisés : `R = 60`
- Écart : `E = 60 − 51 = 9`

Score : `(25 + 9) × 1 = 34`.

Répartition : Preneur `+102`, chaque Défenseur `−34`.
Vérification de somme : `102 − 3 × 34 = 0`.

### Exemple 2 — Garde Sans, Preneur chute

- Contrat : Garde Sans (`M = 2`)
- Bouts : 0 → but `B = 56`
- Points réalisés : `R = 50`
- Écart : `E = 50 − 56 = −6`

Score : `(25 + 6) × 2 = 62`.

Répartition : Preneur `−186`, chaque Défenseur `+62`.
Vérification de somme : `−186 + 3 × 62 = 0`.

### Exemple 3 — Garde, Preneur exactement au but

- Contrat : Garde (`M = 1`)
- Bouts : 3 → but `B = 36`
- Points réalisés : `R = 36`
- Écart : `E = 0`

Score : `(25 + 0) × 1 = 25`. Le Preneur réussit (cas
limite).

Répartition : Preneur `+75`, chaque Défenseur `−25`.

### Exemple 4 — Garde Contre, Preneur réussit largement

- Contrat : Garde Contre (`M = 4`)
- Bouts : 2 → but `B = 41`
- Points réalisés : `R = 45`
- Écart : `E = 4`

Score : `(25 + 4) × 4 = 116`.

Répartition : Preneur `+348`, chaque Défenseur `−116`.

## Donne classique — Petit au Bout (T2a)

Le Petit au Bout est une prime attribuée au camp qui remporte
le dernier Pli en y ayant joué le Petit (Atout 1). La prime
est versée **indépendamment du résultat de la Donne** : le
Preneur peut chuter et toucher la prime, ou réussir son
contrat et la verser à la Défense.

### Valeur

Trois cas mutuellement exclusifs :

| Petit au Bout | Effet sur le score net du Preneur |
|---|---|
| Aucun | 0 |
| Côté Preneur | `+10 × M` |
| Côté Défense | `−10 × M` |

`M` est le multiplicateur du Contrat (cf. table plus haut).

### Score net d'une Donne classique avec Petit au Bout

Soit :

- `S = (25 + |E|) × M` le Score de base de la Donne
  (cf. section précédente),
- `signe = +1` si le Preneur réussit, `−1` s'il chute,
- `bonus_PAB ∈ {0, +10 × M, −10 × M}` selon le camp qui
  remporte le Petit au Bout.

Le **score net par défenseur, du point de vue du Preneur**
vaut :

```
score_net = signe × S + bonus_PAB
```

Et la répartition à 4 joueurs reste structurellement la
même qu'en T1 :

| Cas | Preneur | Chaque Défenseur |
|---|---|---|
| `score_net ≥ 0` | `+3 × score_net` | `−score_net` |
| `score_net < 0` | `+3 × score_net` | `−score_net` |

La formule fonctionne quel que soit le signe : la somme
des points distribués vaut toujours zéro.

### Exemples chiffrés avec Petit au Bout

#### Exemple 5 — Garde, Preneur réussit, PAB côté Preneur

- Contrat : Garde (`M = 1`), Bouts : 1 (`B = 51`), `R = 60`.
- `E = 9`, `S = (25 + 9) × 1 = 34`.
- PAB côté Preneur : `bonus_PAB = +10`.
- `score_net = +1 × 34 + 10 = 44`.

Répartition : Preneur `+132`, chaque Défenseur `−44`.
Vérification : `132 − 3 × 44 = 0`.

#### Exemple 6 — Garde Sans, Preneur chute, PAB côté Preneur

- Contrat : Garde Sans (`M = 2`), Bouts : 0 (`B = 56`),
  `R = 50`.
- `E = −6`, `S = (25 + 6) × 2 = 62`.
- PAB côté Preneur : `bonus_PAB = +20`.
- `score_net = −1 × 62 + 20 = −42`.

Le Preneur chute mais récupère la prime PAB. Répartition :
Preneur `−126`, chaque Défenseur `+42`.
Vérification : `−126 + 3 × 42 = 0`.

#### Exemple 7 — Garde, Preneur réussit, PAB côté Défense

- Contrat : Garde (`M = 1`), Bouts : 2 (`B = 41`), `R = 50`.
- `E = 9`, `S = (25 + 9) × 1 = 34`.
- PAB côté Défense : `bonus_PAB = −10`.
- `score_net = +1 × 34 − 10 = 24`.

Le Preneur gagne la Donne mais la Défense récupère la prime
PAB. Répartition : Preneur `+72`, chaque Défenseur `−24`.
Vérification : `72 − 3 × 24 = 0`.

## Donne classique — Chelem (T2b)

Le Chelem (toutes les levées remportées par le Preneur, ou
toutes sauf la levée jouée à l'Excuse) est une prime de
forte valeur attribuée au Preneur. Convention retenue dans
Abomey : le Chelem est si rare qu'il mérite une grosse
récompense — la prime suit la mécanique de redistribution
des autres primes (multipliée par le nombre de défenseurs
pour le Preneur, soustraite forfaitairement à chaque
défenseur).

### Valeur

Quatre cas mutuellement exclusifs :

| Chelem | Bonus net du Preneur (par défenseur) |
|---|---|
| Aucun | 0 |
| Réalisé sans avoir été annoncé | `+200` |
| Annoncé et réalisé | `+400` |
| Annoncé mais non réalisé | `−200` |

Le bonus est indépendant du multiplicateur de Contrat.

### Score net d'une Donne classique avec Chelem

Soit :

- `S = (25 + |E|) × M` le Score de base,
- `signe = +1` si le Preneur réussit son contrat, `−1`
  sinon,
- `bonus_PAB` le bonus Petit au Bout (cf. T2a),
- `bonus_chelem ∈ {0, +200, +400, −200}`.

Le score net par défenseur, du point de vue du Preneur :

```
score_net = signe × S + bonus_PAB + bonus_chelem
```

La répartition à 4 joueurs reste structurellement la même :
Preneur reçoit `+3 × score_net`, chaque Défenseur reçoit
`−score_net`. Somme zéro conservée.

### Exemples chiffrés avec Chelem

#### Exemple 8 — Garde, Preneur réussit, Chelem annoncé et réalisé

- Contrat : Garde (`M = 1`), Bouts : 3 (`B = 36`), `R = 91`
  (Preneur a tout fait).
- `E = 55`, `S = (25 + 55) × 1 = 80`.
- PAB côté Preneur (logique : il a tout pris au dernier
  Pli) : `bonus_PAB = +10`.
- Chelem annoncé et réalisé : `bonus_chelem = +400`.
- `score_net = +1 × 80 + 10 + 400 = +490`.

Répartition : Preneur `+1470`, chaque Défenseur `−490`.
Vérification : `1470 − 3 × 490 = 0`.

#### Exemple 9 — Garde, Preneur annonce un Chelem mais chute

- Contrat : Garde (`M = 1`), Bouts : 3 (`B = 36`), `R = 35`
  (échec à 1 point près).
- `E = −1`, `S = (25 + 1) × 1 = 26`.
- Pas de PAB.
- Chelem annoncé non réalisé : `bonus_chelem = −200`.
- `score_net = −1 × 26 + 0 − 200 = −226`.

Répartition : Preneur `−678`, chaque Défenseur `+226`.
Vérification : `−678 + 3 × 226 = 0`.

## Donne classique — Poignée(s) (T2c)

Une Poignée est une annonce d'atouts faite par un Joueur
actif (Preneur ou Défenseur) avant la première levée. Elle
porte sur la possession d'un nombre minimum d'atouts dans
la main du Joueur annonceur. Une Donne peut comporter
**zéro ou plusieurs Poignées**, annoncées indépendamment par
différents Joueurs.

### Valeur

Trois tailles de Poignée :

| Taille | Bonus par Poignée |
|---|---|
| Simple | `+20` |
| Double | `+30` |
| Triple | `+40` |

Le bonus est **indépendant du multiplicateur de Contrat**.
La prime est attribuée au **camp qui remporte la Donne**,
indépendamment du camp qui a annoncé la Poignée. Si
plusieurs Poignées sont annoncées dans une même Donne,
leurs bonus s'additionnent et sont attribués en bloc au
camp gagnant.

### Score net d'une Donne classique avec Poignée(s)

Soit :

- `S = (25 + |E|) × M` le Score de base,
- `signe = +1` si le Preneur réussit son contrat, `−1`
  sinon,
- `bonus_PAB` le bonus Petit au Bout (cf. T2a),
- `bonus_chelem` le bonus Chelem (cf. T2b),
- `bonus_poignees = Σ bonus(taille_i)` la somme des bonus
  Poignée annoncés sur la Donne.

Le score net par défenseur, du point de vue du Preneur :

```
score_net = signe × S + bonus_PAB + bonus_chelem + signe × bonus_poignees
```

Le facteur `signe` devant `bonus_poignees` traduit l'attribution
au camp gagnant : si le Preneur réussit, il reçoit le bonus ;
s'il chute, c'est la Défense qui en bénéficie.

Répartition à 4 joueurs : Preneur `+3 × score_net`, chaque
Défenseur `−score_net`. Somme zéro conservée.

### Exemple chiffré avec Poignée

#### Exemple 10 — Garde, Preneur réussit, une Poignée Simple annoncée par un défenseur

- Contrat : Garde (`M = 1`), Bouts : 1 (`B = 51`), `R = 60`.
- `E = 9`, `S = (25 + 9) × 1 = 34`.
- Pas de PAB, pas de Chelem.
- Poignée Simple : `bonus_poignees = 20`.
- Le Preneur réussit (signe = +1) donc reçoit la prime
  Poignée même si elle a été annoncée par la Défense.
- `score_net = +1 × 34 + 0 + 0 + (+1) × 20 = 54`.

Répartition : Preneur `+162`, chaque Défenseur `−54`.
Vérification : `162 − 3 × 54 = 0`.

## Donne classique — Misère(s) (T2d)

Une Misère est l'annonce par un Joueur actif qu'il n'a, à
l'ouverture de la Donne, aucune carte d'un type donné dans
sa main. Convention d'Abomey : deux types reconnus, **Misère
d'Atouts** (aucun atout) et **Misère de Tête** (aucune
figure : Roi, Dame, Cavalier). Une Donne peut comporter
**zéro ou plusieurs Misères**, annoncées indépendamment par
différents Joueurs. Un même Joueur peut annoncer les deux
types ; mais la paire `(annonceur, type)` est unique.

### Mécanique de calcul

La Misère est indépendante du Contrat, du résultat de la
Donne, et de la présentation de la main (annonce sur
l'honneur). Pour chaque Misère :

- L'annonceur reçoit `+10 × (Mode − 1)` points.
- Chacun des autres Joueurs actifs reçoit `−10` points.

La somme par Misère vaut zéro
(`+10×(N−1) − (N−1)×10 = 0`).

Les Misères s'ajoutent **après** la répartition classique du
Score (et des éventuelles autres primes). Le total final par
Joueur est la somme du `score_net` réparti et des bonus
Misère cumulés.

### Exemple chiffré avec Misère

#### Exemple 11 — Garde, Preneur réussit, Alice annonce une Misère d'Atouts

Tablée à 4 joueurs : Alice (Preneur), Bob, Charlie, David.

- Contrat : Garde (`M = 1`), Bouts : 1 (`B = 51`), `R = 60`.
- `E = 9`, `S = 34`, `score_net = +34`.
- Pas de PAB, Chelem, Poignée.
- Misère d'Atouts annoncée par Alice :
  - Alice reçoit `+10 × (4 − 1) = +30`.
  - Bob, Charlie, David reçoivent chacun `−10`.

Répartition classique : Alice `+102`, Bob/Charlie/David
chacun `−34`.

Total final :
- Alice : `+102 + 30 = +132`.
- Bob, Charlie, David : `−34 − 10 = −44` chacun.

Vérification : `132 − 3 × 44 = 0`.

## Répartition à 5 joueurs (T4)

À 5 joueurs actifs, deux configurations sont possibles :
le Preneur désigne un Partenaire, ou le Preneur joue seul.

Le `score_net` est calculé par la même formule qu'à 4
joueurs :

```
score_net = signe × S + bonus_PAB + bonus_chelem + signe × bonus_poignees
```

La répartition diffère selon la configuration.

### Avec Partenaire

Le Partenaire est un Joueur actif distinct du Preneur,
désigné avant le Contrat. Le Preneur et le Partenaire
forment le camp attaquant (2 joueurs) contre 3 Défenseurs.

| Joueur | Points reçus |
|---|---|
| Preneur | `+2 × score_net` |
| Partenaire | `+1 × score_net` |
| Chaque Défenseur (×3) | `−1 × score_net` |

Somme : `2 + 1 − 3 = 0`.

### Preneur seul

Le Preneur joue seul contre les 4 autres Joueurs actifs.

| Joueur | Points reçus |
|---|---|
| Preneur | `+4 × score_net` |
| Chaque Défenseur (×4) | `−1 × score_net` |

Somme : `4 − 4 = 0`.

### Exemples chiffrés à 5 joueurs

#### Exemple 12 — Garde, Preneur réussit, avec Partenaire

Tablée à 5 joueurs actifs : Alice (Preneur), Bob (Partenaire),
Charlie, David, Eve.

- Contrat : Garde (`M = 1`), Bouts : 1 (`B = 51`), `R = 60`.
- `E = 9`, `S = (25 + 9) × 1 = 34`.
- Pas de PAB, Chelem, Poignée.
- `score_net = +1 × 34 = +34`.

Répartition : Alice `+68`, Bob `+34`, Charlie/David/Eve
`−34` chacun.
Vérification : `68 + 34 − 3 × 34 = 0`.

#### Exemple 13 — Garde, Preneur réussit, Preneur seul

Tablée à 5 joueurs actifs : Alice (Preneur seul), Bob,
Charlie, David, Eve.

- Contrat : Garde (`M = 1`), Bouts : 1 (`B = 51`), `R = 60`.
- `E = 9`, `S = 34`. `score_net = +34`.

Répartition : Alice `+136`, Bob/Charlie/David/Eve `−34`
chacun.
Vérification : `136 − 4 × 34 = 0`.

## Répartition à 3 joueurs (T5)

À 3 joueurs actifs, il n'y a jamais de Partenaire (D11) :
le Preneur joue seul contre deux Défenseurs. Le `score_net`
est calculé par la même formule qu'aux autres tablées.

| Joueur | Points reçus |
|---|---|
| Preneur | `+2 × score_net` |
| Chaque Défenseur (×2) | `−1 × score_net` |

Somme : `2 − 2 = 0`. C'est le cas général « Preneur seul »
appliqué à deux Défenseurs ; aucune règle de répartition
spécifique au Mode 3.

### Exemples chiffrés à 3 joueurs

#### Exemple 14 — Garde, Preneur réussit

Tablée à 3 joueurs actifs : Alice (Preneur), Bob, Charlie.

- Contrat : Garde (`M = 1`), Bouts : 1 (`B = 51`), `R = 60`.
- `E = 9`, `S = (25 + 9) × 1 = 34`. `score_net = +34`.

Répartition : Alice `+68`, Bob/Charlie `−34` chacun.
Vérification : `68 − 2 × 34 = 0`.

#### Exemple 15 — Garde Sans, Preneur chute

Tablée à 3 joueurs actifs : Alice (Preneur), Bob, Charlie.

- Contrat : Garde Sans (`M = 2`), Bouts : 0 (`B = 56`),
  `R = 50`.
- `E = −6`, `S = (25 + 6) × 2 = 62`. Preneur chute,
  `score_net = −62`.

Répartition : Alice `−124`, Bob/Charlie `+62` chacun.
Vérification : `−124 + 2 × 62 = 0`.

## Donne Vachette (T6)

La Vachette est un type de Donne distinct du classique : pas
de Preneur, pas de Contrat, pas de Partenaire (cf. D12 et
`glossary.md`). Chaque Joueur actif joue pour lui-même ; en
fin de Donne, les Joueurs sont **classés** du meilleur au
moins bon, et un **barème fixe par Mode** attribue les points
selon la position. Le classement est strict : chaque Joueur
actif porte une position unique de 1 à N (N = Mode).

Le barème ne dépend ni d'un écart, ni d'un multiplicateur, ni
de primes — il est entièrement déterminé par la position et
le Mode.

### Barème par Mode

| Position | 3 joueurs | 4 joueurs | 5 joueurs |
|---|---|---|---|
| 1er | `+120` | `+120` | `+120` |
| 2e | `0` | `+60` | `+60` |
| 3e | `−120` | `−60` | `0` |
| 4e | — | `−120` | `−60` |
| 5e | — | — | `−120` |

La somme est nulle à chaque Mode (barème symétrique autour
de 0). Les bornes sont toujours `+120` (1er) et `−120`
(dernier).

### Exemples chiffrés Vachette

#### Exemple 16 — Vachette à 4 joueurs

Classement : Alice 1re, Bob 2e, Charlie 3e, David 4e.

Répartition : Alice `+120`, Bob `+60`, Charlie `−60`,
David `−120`.
Vérification : `120 + 60 − 60 − 120 = 0`.

#### Exemple 17 — Vachette à 3 joueurs

Classement : Alice 1re, Bob 2e, Charlie 3e.

Répartition : Alice `+120`, Bob `0`, Charlie `−120`.
Vérification : `120 + 0 − 120 = 0`.

#### Exemple 18 — Vachette à 5 joueurs

Classement : positions 1 à 5.

Répartition : `+120 / +60 / 0 / −60 / −120` selon la position.
Vérification : `120 + 60 + 0 − 60 − 120 = 0`.
