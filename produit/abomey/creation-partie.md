# Création d'une Partie

## Contexte

Création de la première unité de jeu d'Abomey. Sans Partie,
l'Utilisateur ne peut rien faire après son inscription — la
création d'une Partie débloque toute la valeur métier (saisie
des Donnes, calcul des scores, statistiques à venir).

## Utilisateurs

Tout Utilisateur Abomey authentifié et ayant accepté la
politique de confidentialité. En contexte : autour d'une
table de jeu, smartphone ou ordinateur à portée. Fréquence
typique : une fois par soirée de jeu, parfois plusieurs
Parties dans une même soirée si la configuration change
(passage de 4 à 5 joueurs, par exemple).

## Objectif

Permettre à un Utilisateur de créer une Partie en quelques
clics : nommage, choix du Mode de tarot, sélection des
Joueurs participants (avec possibilité d'en créer à la volée
si nécessaire).

## Scénario principal

1. L'Utilisateur clique sur « Créer une Partie » depuis son
   espace personnel.
2. Un formulaire de création apparaît, demandant :
   - le **nom** de la Partie ;
   - le **Mode de tarot** (Tarot à 3, à 4 ou à 5) ;
   - les **Joueurs participants**, choisis parmi ses Joueurs
     existants.
3. Si l'Utilisateur n'a pas (ou pas assez) de Joueurs, il
   peut créer un Joueur supplémentaire via une **fenêtre
   modale** ouverte depuis le formulaire, sans quitter
   celui-ci. Le Joueur fraîchement créé apparaît dans la
   liste et est immédiatement sélectionnable.
4. Une indication visible montre le nombre de Joueurs
   sélectionnés par rapport à la plage attendue pour le Mode
   choisi. Le bouton de validation est désactivé tant que la
   plage n'est pas respectée et tant que le nom est vide.
5. À la validation, la Partie est créée et l'Utilisateur
   atterrit sur la **page détail** de cette Partie (encore
   vide de Donnes).

## Variantes et cas limites

- **Brouillon abandonné.** Si l'Utilisateur quitte le
  formulaire sans valider, le brouillon est perdu. Aucune
  persistance temporaire.
- **Tablée plus grande que le Mode.** Si le Mode est Tarot à
  4 et que 5 ou 6 Joueurs sont sélectionnés, la création
  réussit ; les Donnes ultérieures désigneront manuellement
  le ou les Morts.
- **Joueur créé à la volée puis non sélectionné.** Le Joueur
  est conservé dans le carnet de l'Utilisateur (créé en base)
  même si l'Utilisateur ne le coche pas avant de valider la
  Partie. Il reste disponible pour une Partie ultérieure.
- **Création de Joueur avec un nom déjà présent.** Aucune
  contrainte d'unicité sur le nom du Joueur dans le carnet :
  deux Joueurs distincts peuvent porter le même nom. Pas
  d'avertissement.
- **Doublon dans la sélection.** Impossible par construction
  de l'UI : la même case ne peut pas être cochée deux fois.
  La règle R6 reste explicite pour défendre le contrat côté
  application.

## Règles métier

**R1 — Mode immuable.** Le Mode de tarot d'une Partie (3, 4
ou 5) est fixé à la création et ne peut plus changer ensuite.

**R2 — Effectif minimal.** Le nombre de Joueurs participants
est supérieur ou égal au Mode de tarot choisi.

**R3 — Joueurs figés.** Le groupe de Joueurs participants
est figé à la création. Aucun ajout ni retrait possible
ensuite.

**R4 — Propriété de la Partie.** Une Partie appartient à un
et un seul Utilisateur, qui en est l'auteur.

**R5 — Joueurs du même Utilisateur.** Tous les Joueurs
participants à une Partie appartiennent au même Utilisateur
que la Partie.

**R6 — Joueurs distincts.** Les Joueurs participants à une
Partie sont tous distincts. Pas de doublon.

**R7 — Effectif maximal.** Le nombre de Joueurs participants
ne dépasse pas le Mode de tarot augmenté de 2 :

- Tarot à 3 : de 3 à 5 Joueurs ;
- Tarot à 4 : de 4 à 6 Joueurs ;
- Tarot à 5 : de 5 à 7 Joueurs.

**R8 — Nom de la Partie.** La Partie porte un nom saisi par
l'Utilisateur. Ce nom n'est pas vide après suppression des
espaces de début et de fin.

## Hors-scope

- **Saisie de Donnes et calcul de scores.** Sujets séparés
  qui suivront la création de Partie.
- **Modification d'une Partie après sa création.** Exclu par
  R1 (Mode immuable) et R3 (Joueurs figés).
- **Suppression d'une Partie.** Pas dans ce sujet.
- **Page dédiée de gestion des Joueurs** (édition,
  suppression, listage hors flux de création de Partie).
  Reportée. La création de Joueurs passe exclusivement par
  la modale dans le flux de création de Partie pour ce
  sujet.
- **Persistance d'un brouillon** entre deux visites.
- **Statistiques** d'un Joueur sur l'ensemble de ses Parties.

## Critère de succès

1. Un Utilisateur authentifié et consentant peut créer une
   Partie en remplissant un formulaire unique (nom + Mode +
   sélection de Joueurs), avec la possibilité de créer des
   Joueurs à la volée via une fenêtre modale.
2. La Partie créée respecte les règles R1 à R8.
3. L'Utilisateur atterrit sur la page détail de la Partie
   immédiatement après création.
4. Un autre Utilisateur ne voit pas cette Partie dans son
   espace.
