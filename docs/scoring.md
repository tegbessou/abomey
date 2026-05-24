# Calcul des Scores

Ce document formalise les règles de calcul des Scores
d'une Donne dans Abomey. Il sert de référence
implémentable et testable pour les règles métier de calcul
introduites par le sujet #003.

Le document s'enrichit tranche par tranche, en lien avec
le découpage de `product/saisie-donnes.md`. Chaque ajout
référence la tranche concernée.

## Périmètre couvert

À ce jour, le document couvre uniquement les besoins de la
**Tranche 1** :

- Donne classique
- Tarot à 4 joueurs, tablée égale au Mode (pas de Mort)
- Sans primes (Petit au Bout, Poignée, Chelem, Misère)

Restent à formaliser dans les tranches suivantes :

- Primes des Donnes classiques (T2a → T2d)
- Désignation et neutralisation du Mort (T3)
- Tarot à 5 et répartition avec Partenaire ou Preneur seul
  (T4)
- Tarot à 3 et répartition (T5)
- Barème Vachette par Mode (T6)

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
