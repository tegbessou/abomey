# Glossaire d'Abomey

## Rôle de ce document

Ce glossaire définit le vocabulaire métier du domaine Abomey : les 
termes utilisés dans le code, dans les discussions, et dans la 
documentation. Il constitue l'ubiquitous language du projet au sens 
où Eric Evans l'entend dans le Blue Book.

Les définitions s'appuient sur la terminologie officielle de la 
Fédération Française de Tarot (FFT). Quand Abomey s'écarte de la 
FFT pour un choix de périmètre ou de modélisation, la divergence 
est signalée explicitement.

Ce glossaire ne couvre que les concepts utilisés dans Abomey. Les 
termes du tarot hors-scope (comme la Prise) ne sont mentionnés que 
s'il est utile de documenter leur non-prise-en-charge.

## Conventions

Les termes en **gras** renvoient à d'autres entrées du glossaire.

Chaque terme peut porter un marquage :

- **[Aggregate Root]** : concept modélisé comme aggregate dans le 
  domaine Abomey
- **[Entity]** : concept modélisé comme entity dans le domaine Abomey
- **[Value Object]** : concept modélisé comme value object dans le 
  domaine Abomey
- **[Non géré]** : concept du tarot non pris en charge par Abomey, 
  mentionné pour documenter une décision de périmètre

---

## A

### Atout

Carte de la couleur prioritaire sur les trois autres couleurs. Il 
y a 21 Atouts numérotés de 1 à 21. Le 1 s'appelle le **Petit**, le 
21 est l'Atout le plus fort. Les Atouts sont prioritaires sur les 
autres couleurs lors du jeu.

## B

### Bout [Value Object]

Carte ayant un statut particulier dans le calcul du score. Il y a 
trois Bouts : le **Petit** (Atout 1), le 21 (Atout le plus fort), 
et l'**Excuse**. Le nombre de Bouts détenus par le preneur à la 
fin de la **Donne** détermine le nombre de points qu'il devait 
réaliser.

Synonyme : Oudler (terme plus ancien, moins utilisé aujourd'hui).

Seuils de points à réaliser selon le nombre de Bouts :

- 0 Bout : 56 points
- 1 Bout : 51 points
- 2 Bouts : 41 points
- 3 Bouts : 36 points

## C

### Chelem

Contrat spécial consistant à remporter toutes les levées de la 
**Donne**. Peut être annoncé ou réalisé sans annonce :

- Annoncé et réalisé : prime de 400 points
- Non annoncé mais réalisé : prime de 200 points
- Annoncé et non réalisé : pénalité de 200 points

### Chien

Ensemble de cartes mises de côté lors de la distribution, non 
attribuées aux joueurs. Le nombre de cartes du Chien dépend du 
**Mode de tarot** :

- 3 joueurs : 3 cartes
- 4 joueurs : 6 cartes
- 5 joueurs : 3 cartes

Le sort du Chien dépend du **Contrat** joué.

### Contrat [Value Object]

Engagement pris par le **Preneur** sur la façon dont il utilisera 
le **Chien**. Abomey gère trois contrats, par ordre croissant 
d'engagement et de multiplicateur :

- **Garde** : multiplicateur 2
- **Garde Sans** : multiplicateur 4
- **Garde Contre** : multiplicateur 6

La **Prise** (ou Petite), bien qu'existant dans le règlement FFT, 
n'est pas gérée par Abomey (voir entrée Prise).

### Couleur

Famille de cartes hors Atouts. Il y a quatre couleurs : Pique, 
Cœur, Carreau, Trèfle. Chaque couleur compte 14 cartes (de l'As 
au Roi, avec un Cavalier entre le Valet et la Dame).

## D

### Défense

Ensemble des joueurs opposés au **Preneur** dans une **Donne**. À 
4 joueurs, la Défense compte 3 joueurs. À 5 joueurs avec un 
**Partenaire**, la Défense compte 3 joueurs aussi. À 3 joueurs, 
la Défense compte 2 joueurs.

### Donne [Entity]

Unité atomique de jeu au sein d'une **Partie**. Une Donne est 
composée d'une distribution, d'une prise de contrat (ou d'un 
mode Vachette), du jeu des cartes, et d'un calcul de score. 

Abomey modélise la Donne comme une Entity interne à l'Aggregate 
**Partie** : elle n'existe pas en dehors de sa Partie.

Abomey ne modélise pas le détail du jeu des cartes (les **Plis** 
individuels). Seul le résultat final de la Donne est saisi : 
preneur, contrat, bouts, points réalisés, primes.

Deux types de Donne existent dans Abomey :

- **Donne classique** : avec Preneur, Contrat, calcul FFT standard
- **Donne Vachette** : mode chacun pour soi, calcul spécifique

## E

### Écart

Ensemble des cartes que le **Preneur** met de côté après avoir 
incorporé le **Chien** à sa main (contrat Garde uniquement). 
L'Écart est comptabilisé avec les levées du Preneur en fin de 
Donne.

### Excuse

Carte particulière du jeu de tarot (78e carte). Elle est l'un 
des trois **Bouts**. Elle peut être jouée à tout moment et 
dispense de fournir la couleur demandée. L'Excuse ne peut pas 
être jouée à la dernière levée (sauf en cas de Chelem), sinon 
elle change de camp.

## G

### Garde [Contrat]

Contrat de base géré par Abomey. Le Preneur incorpore le 
**Chien** à sa main, puis fait son **Écart**. Multiplicateur : 2.

### Garde Contre [Contrat]

Le Preneur ne regarde pas le **Chien**. Les cartes du Chien sont 
attribuées à la **Défense** et comptabilisées avec leurs levées. 
Multiplicateur : 6.

### Garde Sans [Contrat]

Le Preneur ne regarde pas le **Chien**. Les cartes du Chien sont 
comptabilisées avec les levées du Preneur. Multiplicateur : 4.

## J

### Joueur [Aggregate Root]

Personne physique qui participe à des Parties. Dans Abomey, le
Joueur a une identité persistante au sein de l'espace de son
**Utilisateur** propriétaire : il existe en dehors de toute
Partie et peut être réutilisé d'une Partie à l'autre.

Le Joueur appartient à un et un seul Utilisateur. Il n'est
visible et modifiable que par celui-ci.

Attributs :
- Identifiant unique
- Nom (modifiable)
- Utilisateur propriétaire (référencé par identifiant)

Le Joueur est un Aggregate Root distinct de l'Utilisateur, dont
il référence l'identifiant pour matérialiser l'appartenance. Les
Parties et les Donnes référencent le Joueur par son
identifiant.

## L

### Levée

Synonyme de **Pli**. Voir ce terme.

## M

### Mode de tarot

Configuration d'une **Partie** selon le nombre effectif de joueurs 
actifs par **Donne**. Abomey gère trois modes :

- **Tarot à 3** : 3 joueurs actifs par Donne
- **Tarot à 4** : 4 joueurs actifs par Donne
- **Tarot à 5** : 5 joueurs actifs par Donne

Si la tablée compte plus de joueurs (6, 7, etc.), la notion de 
**Mort** s'applique : certains joueurs ne participent pas à 
certaines Donnes pour maintenir le Mode de tarot choisi.

### Mort

Joueur qui ne participe pas à une **Donne** donnée pour maintenir 
le **Mode de tarot** choisi dans une Partie comportant plus de 
joueurs que le mode.

Dans Abomey :

- La désignation du Mort se fait manuellement à chaque Donne 
  (pas de rotation automatique)
- Le Mort ne reçoit ni ne perd de points sur la Donne
- Le Mort est ignoré dans les statistiques de la Donne et de la 
  Partie

## P

### Partenaire

À 5 joueurs, joueur allié du **Preneur** désigné par l'appel 
d'un Roi. Le Partenaire forme avec le Preneur le camp attaquant 
(2 contre 3). Les points de gain ou de chute sont répartis entre 
Preneur et Partenaire selon les règles FFT (2/3 pour le Preneur, 
1/3 pour le Partenaire).

Le Partenaire peut ne pas exister si le Preneur s'appelle lui-même 
ou si le Roi appelé est au Chien — auquel cas le Preneur joue seul 
contre les 4 Défenseurs.

### Partie [Aggregate Root]

Séance de jeu composée de plusieurs **Donnes** successives avec
un groupe fixe de **Joueurs**. Une Partie a un début, puis
accumule des Donnes au fil du temps. Elle n'a pas d'état
« clos » dans Abomey : on peut toujours reprendre une Partie
existante et y ajouter de nouvelles Donnes. La notion de fin
de Partie est décidée par les joueurs physiques, pas par
l'application.

La Partie appartient à un et un seul **Utilisateur**. Elle n'est
visible et modifiable que par celui-ci. Tous les Joueurs
participants appartiennent au même Utilisateur que la Partie.

Attributs :
- Identifiant unique
- Utilisateur propriétaire (référencé par identifiant)
- Mode de tarot (3, 4 ou 5)
- Joueurs participants (référencés par leur identifiant)
- Date de création
- Donnes (collection)

La Partie est un Aggregate Root. Elle encapsule les Donnes, qui
sont des Entities internes. Elle référence l'Utilisateur
propriétaire et les Joueurs par leur identifiant, sans les
contenir.

Invariants :
- Le nombre de Joueurs doit être supérieur ou égal au Mode de
  tarot choisi
- Tous les Joueurs participants appartiennent au même
  Utilisateur propriétaire que la Partie

### Petit

Atout 1, la plus petite carte d'Atout. C'est l'un des trois 
**Bouts**. Le Petit est vulnérable : contrairement au 21 et à 
l'Excuse, il peut changer de camp en cours de jeu.

### Petit au Bout

Prime accordée lorsque le **Petit** fait partie de la dernière 
levée. Le camp qui réalise cette levée bénéficie d'une prime de 
10 points, multipliable selon le **Contrat**, quel que soit le 
résultat de la Donne.

### Pli

Série de cartes jouées (une par chaque joueur actif) remportée 
par l'un d'eux. Plusieurs Plis constituent une **Donne**.

Abomey ne modélise pas les Plis individuellement : seul le 
résultat final de la Donne est saisi.

Synonyme : Levée.

### Poignée

Prime basée sur le nombre d'Atouts détenus dans la main du 
joueur en début de Donne. La Poignée doit être annoncée et 
présentée avant la première carte jouée.

Primes de Poignée (non multipliables) :

- Simple Poignée : 20 points
- Double Poignée : 30 points
- Triple Poignée : 40 points

Le nombre d'Atouts requis varie selon le Mode de tarot :

- À 3 joueurs : 13, 15, 18 Atouts
- À 4 joueurs : 10, 13, 15 Atouts
- À 5 joueurs : 8, 10, 13 Atouts

### Preneur

Joueur qui, lors d'une **Donne** classique, s'engage à réaliser 
un certain nombre de points en fonction de son **Contrat** et du 
nombre de **Bouts** qu'il détiendra. Le Preneur est opposé à la 
**Défense** (à 3 ou 4 joueurs) ou, à 5 joueurs, allié à un 
**Partenaire** sauf s'il joue seul.

Le Preneur fait nécessairement partie des Joueurs actifs de la 
Donne.

### Prise [Non géré]

Contrat de base défini par la FFT, multiplicateur 1. Également 
appelé « Petite ».

Abomey ne gère pas la Prise. Seuls les contrats à partir de la 
**Garde** sont pris en charge. Cette décision est un choix de 
périmètre : en pratique amicale, la Prise est rarement jouée.

## S

### Score [Value Object]

Résultat chiffré d'une **Donne**, calculé selon les règles FFT 
(ou selon les règles de la Vachette en mode Vachette).

Formule de base pour une Donne classique :

`(25 + écart) × multiplicateur de contrat + primes`

où l'écart est la différence entre les points réalisés par le 
Preneur et le seuil à atteindre selon son nombre de Bouts.

Les signes s'appliquent selon la réussite ou la chute, et la 
répartition entre Preneur, Partenaire et Défense varie selon le 
Mode de tarot.

## T

### Tarot à 3

**Mode de tarot** à 3 joueurs actifs. Distribution 3 par 3, 
chaque joueur reçoit 24 cartes, Chien de 3 cartes.

### Tarot à 4

**Mode de tarot** à 4 joueurs actifoçàs. Configuration officielle 
standard de la FFT. Distribution 3 par 3, chaque joueur reçoit 
18 cartes, Chien de 6 cartes.

### Tarot à 5

**Mode de tarot** à 5 joueurs actifs. Distribution 3 par 3, 
chaque joueur reçoit 15 cartes, Chien de 3 cartes. Le Preneur 
appelle un Roi pour désigner son **Partenaire**.

Note : les tournois à 5 joueurs ne sont pas homologables par la 
FFT, mais les règles FFT existent et sont pratiquées en jeu 
amical. Abomey s'appuie sur ces règles.

## U

### Utilisateur [Aggregate Root]

Personne qui utilise Abomey. Chaque Utilisateur dispose d'un
espace personnel strictement isolé contenant ses propres
**Joueurs** et ses propres **Parties**. Aucune donnée n'est
partagée entre Utilisateurs.

L'Utilisateur est identifié de manière stable par le couple
(fournisseur d'identité externe, identifiant unique fourni par
ce fournisseur). Cet identifiant interne ne change jamais, même
si l'Utilisateur modifie son nom ou son email chez le
fournisseur.

Attributs :
- Identifiant interne unique
- Fournisseur d'identité (Google ou Apple)
- Identifiant unique stable fourni par le fournisseur
- Nom complet (resynchronisé à chaque connexion ; à défaut,
  « Anonyme »)
- Email (resynchronisé à chaque connexion)

Cycle de vie : création silencieuse au premier passage par un
fournisseur d'identité, suppression dure et complète à la
demande de l'Utilisateur (efface profil, Joueurs, Parties, et
l'identité chez le fournisseur intermédiaire).

Deux Utilisateurs sont toujours distincts s'ils proviennent de
fournisseurs différents ou ont des identifiants différents chez
un même fournisseur, même si le nom ou l'email présentés sont
identiques. Aucune fusion, automatique ou manuelle, n'est
proposée.

L'Utilisateur est un Aggregate Root indépendant. Les Joueurs
et les Parties le référencent par son identifiant interne.

## V

### Vachette [Mode de jeu]

Variante non-officielle où chaque joueur joue pour lui-même avec 
l'objectif de remporter le **moins** de points possible. Pas de 
**Preneur**, pas de **Contrat**, pas de **Partenaire**.

Règles spécifiques :

- Le **Chien** est remporté par le joueur ayant remporté le 
  dernier Pli
- Chaque **Bout** vaut 4,5 points (comme en partie classique), 
  mais n'a aucun autre impact (pas de seuil à atteindre)
- En fin de Donne, chaque joueur compte ses propres Plis
- Les joueurs sont classés du nombre de points le plus faible au 
  plus élevé
- Les scores sont compris entre -120 et +120 points, répartis 
  selon le classement

Abomey modélise la Vachette comme un **Mode de jeu** de la Donne, 
pas comme un Contrat. Une même Partie peut enchaîner des Donnes 
classiques et des Donnes Vachette.

Cette variante ne fait pas partie des règles FFT et n'est 
généralement pas admise en tournoi.
