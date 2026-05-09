# Domaine d'Abomey

## Rôle de ce document

Ce document décrit le domaine métier d'Abomey : ce que l'application
modélise, comment les concepts s'articulent, et quelles règles
structurent leur comportement.

Les termes métier utilisés ici sont définis dans `glossary.md`. Les
formules de calcul de score sont détaillées dans ce document et
formalisées dans les tests du domaine.

## Le projet

Abomey est une application personnelle de gestion de parties de
jeux de cartes à points. La version courante se limite au tarot.
La belote et la coinche sont évoquées comme extensions possibles
mais ne font pas partie du périmètre actuel.

Le nom Abomey renvoie à l'ancienne capitale du royaume du Danhomè,
en écho au nom Teg (de Tegbessou, roi du Danhomè) qui désigne
l'assistant ayant accompagné la conception du projet.

Abomey remplace une application payante existante dont l'usage
principal est le comptage des points en fin de donne et le suivi
d'une partie entre amis. Le projet ne vise pas la simulation du
jeu de cartes : les cartes ne sont pas modélisées individuellement,
seuls les résultats des donnes sont saisis et calculés.

## Périmètre fonctionnel

Abomey gère :

- La création et la gestion d'une base de **Joueurs** persistants
- La création d'une **Partie** avec un groupe de Joueurs
- La saisie successive des **Donnes** d'une Partie, avec calcul
  automatique des **Scores**
- Le suivi de l'état courant de la Partie (scores cumulés,
  statistiques de partie)
- Deux modes de Donne : classique (avec Preneur et Contrat) et
  Vachette (chacun pour soi)
- Les trois Modes de tarot de la FFT : 3, 4, 5 joueurs
- La gestion du **Mort** pour les tablées de plus de 5 joueurs

Abomey ne gère pas :

- La **Prise** (ou Petite) comme Contrat : seuls les contrats à
  partir de la Garde sont pris en charge
- Les **Plis** individuels : seule la Donne comme unité atomique
  est modélisée
- Les statistiques **cross-parties** : la v1 se limite aux
  statistiques internes à la Partie courante
- L'**authentification** ou la gestion multi-utilisateurs :
  l'application est personnelle

## Concepts centraux

Abomey modélise quatre concepts principaux, articulés en deux
Aggregates au sens DDD.

**Joueur** est un Aggregate Root indépendant. Son cycle de vie ne
dépend d'aucune Partie. Un Joueur peut exister sans jamais avoir
participé à une Partie. Il a une identité globale persistante et
un nom modifiable.

**Partie** est un Aggregate Root qui encapsule les Donnes. Elle
référence les Joueurs par leur identifiant sans les contenir. Une
Partie a un Mode de tarot fixé à sa création, un groupe de Joueurs
participants, et une collection ordonnée de Donnes jouées.

**Donne** est une Entity interne à l'Aggregate Partie. Elle n'a de
sens que dans le contexte de sa Partie. Une Donne porte un type
(classique ou Vachette), les Joueurs actifs de cette Donne (pour
gérer le Mort), et les éléments nécessaires au calcul du Score.

**Score** est un Value Object calculé à partir d'une Donne. Il
porte les résultats chiffrés par Joueur pour cette Donne.

## Cycle de vie d'une Partie

Une Partie est créée avec :

- Un Mode de tarot choisi (3, 4 ou 5)
- Un ensemble de Joueurs participants, de taille supérieure ou
  égale au Mode de tarot

Une fois créée, une Partie accumule des Donnes au fil du temps.
Chaque Donne a un numéro d'ordre dans la Partie.

Une Partie n'a pas d'état "clos" dans Abomey. On peut toujours
reprendre une Partie existante et y ajouter de nouvelles Donnes.
La notion de fin de Partie est uniquement décidée par les joueurs
physiques : l'application ne force ni ne détecte la clôture.

Les statistiques de la Partie (scores cumulés, nombre de prises
par Joueur, taux de réussite) se calculent à tout moment à partir
des Donnes existantes.

## Cycle de vie d'une Donne

Chaque Donne suit un flux en trois étapes.

**Saisie des Joueurs actifs.** Dans une Partie dont le nombre de
Joueurs dépasse le Mode de tarot, l'utilisateur désigne pour
chaque Donne les Joueurs qui ne participent pas (les Morts). Le
Mort est désigné manuellement sans rotation automatique. Si le
nombre de Joueurs égale le Mode de tarot, cette étape est
implicite.

**Saisie du résultat.** Pour une Donne classique : le Preneur, le
Contrat (Garde, Garde Sans ou Garde Contre), le Partenaire
éventuel à 5 joueurs, le nombre de Bouts du Preneur, les points
réalisés, les primes éventuelles (Petit au Bout, Poignée, Chelem).
Pour une Donne Vachette : le décompte de points pour chaque Joueur
actif et le gagnant du dernier Pli (qui remporte le Chien).

**Calcul du Score.** À partir des éléments saisis, l'application
calcule les points attribués à chaque Joueur actif de la Donne.
Les Joueurs Morts de cette Donne ne reçoivent ni ne perdent de
points.

## Règles métier structurantes

### Règles générales des Parties

- Le Mode de tarot d'une Partie est fixé à sa création et ne peut
  pas changer.
- Le nombre de Joueurs de la Partie est fixé à sa création et ne
  peut pas changer. Pour jouer avec d'autres Joueurs, il faut
  créer une nouvelle Partie.
- Une Donne ne peut être ajoutée à une Partie que si ses Joueurs
  actifs sont bien des Joueurs de la Partie.
- Le nombre de Joueurs actifs d'une Donne doit être exactement
  égal au Mode de tarot.

### Règles de la Donne classique

- Le Preneur fait partie des Joueurs actifs de la Donne.
- À 5 joueurs, le Partenaire (s'il existe) fait partie des Joueurs
  actifs et n'est pas le Preneur. Le Preneur peut aussi jouer seul
  contre la Défense (appel à soi-même ou Roi appelé dans le Chien).
- Le nombre de Bouts du Preneur est compris entre 0 et 3.
- Les points réalisés par le Preneur sont compris entre 0 et 91
  (au demi-point près).
- Les seuils à atteindre selon les Bouts sont définis par la FFT :
  56 (0 Bout), 51 (1 Bout), 41 (2 Bouts), 36 (3 Bouts).

### Règles de la Donne Vachette

- Pas de Preneur, pas de Contrat, pas de Partenaire.
- Chaque Joueur actif compte ses propres points.
- Le Chien est attribué au Joueur qui remporte le dernier Pli.
- Les totaux chiffrés de chaque Joueur actif doivent sommer à 91
  points (éventuellement au demi-point près).
- Les scores finaux sont compris entre -120 et +120 points,
  répartis selon le classement des Joueurs.

### Règles de calcul du Score

La formule générale pour une Donne classique est :

`(25 + écart) × multiplicateur de Contrat + primes`

où :

- L'écart est la différence entre les points réalisés et le seuil
  à atteindre selon les Bouts
- Le multiplicateur est 2 pour la Garde, 4 pour la Garde Sans,
  6 pour la Garde Contre
- Les primes incluent le Petit au Bout (multipliable), la Poignée
  (non multipliable) et le Chelem (non multipliable)

La répartition du Score entre Preneur, Partenaire éventuel et
Défense dépend du Mode de tarot. La règle officielle FFT
s'applique : à 3 joueurs les points du Preneur sont doublés, à 5
joueurs avec Partenaire la répartition est 2/3 pour le Preneur et
1/3 pour le Partenaire.

Les règles détaillées, les cas particuliers et les exemples
chiffrés sont documentés dans `scoring.md` et formalisés dans les
tests unitaires du domaine. La FFT (https://fftarot.fr) est la
source autoritaire pour tout cas non explicitement traité par
Abomey.

## Choix de modélisation

Abomey suit l'architecture définie dans le CLAUDE.md utilisateur
de Teg : hexagonale avec couches internes (Domain, Application,
Infrastructure, UI), ubiquitous language aligné sur la FFT, tests
comme spécification exécutable.

Le domaine distingue volontairement deux Aggregates (Joueur et
Partie) plutôt qu'un seul. Cette séparation reflète la différence
de cycle de vie : un Joueur existe indépendamment de toute Partie,
une Donne n'existe que dans le contexte de sa Partie.

Le Pli n'est pas modélisé comme concept. C'est un choix assumé de
niveau de granularité : Abomey s'arrête à la Donne comme unité
atomique. Les informations relatives aux Plis (comme le gagnant du
dernier Pli en Vachette) sont saisies directement dans la Donne
sans modélisation intermédiaire.

## Ce qu'Abomey fera peut-être plus tard

Cette section documente les extensions envisagées pour éviter la
confusion entre "non fait par oubli" et "non fait par choix".

- Statistiques cross-parties (agrégation des performances d'un
  Joueur sur l'ensemble de ses Parties)
- Support des autres jeux (belote, coinche) — décision
  architecturale à trancher le moment venu
- Gestion du Contrat Prise si un besoin réel émerge
- Authentification et mode multi-utilisateurs si l'application
  devient partagée
- Clôture explicite de Partie et historique avec recherche
- Export ou impression des scores d'une Partie
