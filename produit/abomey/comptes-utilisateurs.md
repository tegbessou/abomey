# Comptes utilisateurs et isolation des données

## Contexte

Abomey passe d'une application personnelle à une application
ouverte. Plusieurs personnes doivent pouvoir l'utiliser
indépendamment, chacune avec ses propres Joueurs et ses propres
Parties. La gestion des identifiants et l'authentification sont
déléguées à des fournisseurs d'identité externes pour éviter
d'avoir à gérer mots de passe, inscriptions et récupérations.

## Utilisateurs

Toute personne disposant d'un compte Google ou Apple, qui
souhaite tenir le compte de ses Parties de tarot et de celles de
son entourage. Inscription publique ouverte, sans modération ni
invitation. Aucune promotion n'est faite : les utilisateurs
arrivent par bouche à oreille ou découverte spontanée.

## Objectif

Permettre à un visiteur de créer son propre espace personnel
dans Abomey en un clic, et garantir que les données de chaque
utilisateur restent strictement isolées des autres.

## Scénario principal

1. Un visiteur arrive sur Abomey sans être connecté.
2. La page d'accueil propose un bouton de connexion unique.
3. En cliquant, il est dirigé vers une page d'authentification
   déléguée qui présente les fournisseurs d'identité
   disponibles (Google et Apple). Il choisit l'un d'eux, donne
   son consentement au partage de son nom et de son email avec
   Abomey, et revient sur Abomey.
4. Si c'est la première fois, son espace personnel est créé
   automatiquement à partir des informations transmises
   (identifiant unique stable, nom, email). Aucune saisie
   supplémentaire n'est demandée.
5. Il atterrit sur son espace personnel, qui présente ses
   Joueurs et ses Parties. Au premier passage, cet espace est
   vide.
6. Il peut se déconnecter à tout moment via un bouton visible
   en permanence quand il est connecté.

## Variantes et cas limites

- **Reconnexion après expiration.** La session reste valide
  pendant 30 jours d'inactivité. Au-delà, l'utilisateur est
  redirigé vers la page d'accueil et invité à se reconnecter
  via Google ou Apple.
- **Accès à une URL profonde sans être connecté.** Un visiteur
  non connecté qui demande une page protégée (lien partagé,
  marque-page, redirection interne) est ramené à la page
  d'accueil pour se reconnecter, puis automatiquement renvoyé
  vers l'URL initialement demandée après authentification.
- **Connexion alternée Google et Apple.** Un utilisateur qui
  choisit Google sur la page d'authentification une fois, puis
  Apple à une connexion ultérieure, obtient deux espaces
  distincts. Aucune fusion, automatique ou manuelle, n'est
  proposée.
- **Modification du nom ou de l'email chez le fournisseur.** Les
  informations affichées et stockées sont resynchronisées à
  chaque connexion. Le fournisseur d'identité fait foi.
- **Abandon en cours d'authentification.** Un utilisateur qui
  annule l'authentification côté Google ou Apple, ou qui ferme
  l'onglet, revient à l'état précédent : aucun espace créé,
  aucune session ouverte.
- **Erreur côté fournisseur.** En cas d'indisponibilité de
  Google, Apple ou du service intermédiaire d'authentification,
  l'utilisateur est ramené à la page d'accueil avec un message
  d'erreur explicite.
- **Révocation de l'accès chez le fournisseur.** Si
  l'utilisateur révoque l'autorisation depuis ses paramètres
  Google ou Apple, sa prochaine tentative de connexion échoue
  avec un message générique. Son espace existe toujours mais
  reste inaccessible tant que l'autorisation n'est pas
  redonnée.
- **Email masqué par Apple.** Si l'utilisateur choisit l'option
  « Masquer mon adresse email » d'Apple, Abomey reçoit l'email
  relais fourni par Apple, qui est traité comme un email
  standard.
- **Suppression du compte.** L'utilisateur peut à tout moment
  supprimer son compte depuis une page dédiée de son espace,
  après confirmation simple. La suppression efface toutes ses
  données (profil, Joueurs, Parties) définitivement et sans
  recours. L'effacement est également propagé au fournisseur
  d'identité intermédiaire pour empêcher la recréation
  silencieuse à la prochaine connexion.
- **Affichage du nom.** Le nom affiché est le nom complet
  transmis par le fournisseur d'identité. Si aucun nom n'est
  transmis, Abomey affiche « Anonyme ».

## Règles métier

**R1 — Isolation totale des données.** Un utilisateur n'a
aucun moyen d'accéder, en lecture comme en écriture, aux
Joueurs ou aux Parties d'un autre utilisateur. Ni directement
via l'interface, ni indirectement en construisant ou en
devinant une URL.

**R2 — Identité stable.** L'identifiant interne d'un
utilisateur dans Abomey est dérivé de l'identifiant unique
stable fourni par le service intermédiaire d'authentification.
Cet identifiant ne change pas, même si l'utilisateur modifie
son nom ou son email chez son fournisseur d'identité externe.

**R3 — Indépendance des fournisseurs.** Le service intermédiaire
d'authentification attribue un identifiant stable distinct par
identité sociale (Google ou Apple). En conséquence, un même
humain qui se connecte une fois via Google puis une fois via
Apple obtient deux espaces personnels distincts dans Abomey,
sans aucun lien automatique entre eux.

**R4 — Suppression dure et complète.** Lorsque l'utilisateur
supprime son compte, toutes ses données (profil, Joueurs,
Parties) sont effacées immédiatement et définitivement. Son
identité est également effacée chez le fournisseur d'identité
intermédiaire géré par Abomey. Aucune trace résiduelle n'est
conservée côté Abomey.

**R5 — Minimum de données demandées.** Les seules informations
demandées aux fournisseurs d'identité sont l'identifiant unique
stable, l'email et le nom complet. Aucun autre accès n'est
sollicité (contacts, fichiers, etc.).

**R6 — Authentification requise.** Toute page autre que la
page d'accueil, les mentions légales et la politique de
confidentialité requiert un utilisateur connecté. Un visiteur
non connecté qui demande une page protégée est redirigé vers
la page d'accueil, et ramené automatiquement à la page
initialement demandée après authentification.

**R7 — Conformité RGPD.** Abomey expose des mentions légales
et une politique de confidentialité accessibles librement. Le
consentement à la création du compte est tracé. Les droits
d'accès et d'effacement sont effectivement exerçables depuis
l'espace personnel de l'utilisateur, sans intervention humaine.

## Hors-scope

- **Authentification par email et mot de passe.** L'application
  délègue intégralement l'authentification aux fournisseurs
  externes. Quelqu'un sans compte Google ni Apple ne peut pas
  utiliser Abomey.
- **Authentification multi-facteurs (MFA, 2FA).** Pas un besoin
  pour une application de loisir, coût significatif côté
  fournisseur d'identité tiers.
- **Authentification entreprise (SSO, SAML).** Hors du public
  visé.
- **Liaison ou fusion de comptes** entre Google et Apple pour
  un même utilisateur. Simplification volontaire.
- **Récupération de compte** si l'utilisateur perd l'accès à
  ses deux fournisseurs. Conséquence acceptée de la délégation
  totale.
- **Modification manuelle du nom affiché** dans Abomey. Le nom
  suit le fournisseur d'identité, l'utilisateur le change là-bas
  si nécessaire.
- **Notifications par email.** L'application ne contacte pas
  ses utilisateurs par email. L'email est conservé uniquement
  à des fins d'identification et de conformité RGPD.
- **Autres fournisseurs d'identité** (Microsoft, GitHub, etc.).
  Google et Apple suffisent au lancement.
- **Validation manuelle des emails.** L'application fait
  confiance à la validation déjà effectuée par les fournisseurs.
- **Soft delete et corbeille.** La suppression de compte est
  immédiate et irréversible.
- **Statistiques cross-utilisateurs ou partage de données**
  entre utilisateurs. Conséquence directe de l'isolation totale.
- **Outils d'analytics ou de tracking comportemental.** Seuls
  les cookies techniquement nécessaires (session, protection
  CSRF) sont utilisés.
- **Panel d'administration multi-utilisateurs.** Personne
  n'administre les comptes ; chaque utilisateur gère le sien.

## Critère de succès

1. Un visiteur peut, depuis la page d'accueil d'Abomey, créer
   son espace personnel via Google ou Apple sans aucune autre
   saisie qu'un choix de fournisseur sur la page
   d'authentification déléguée.
2. Deux utilisateurs distincts qui utilisent Abomey ne voient
   en aucune circonstance les Joueurs ou les Parties l'un de
   l'autre.
3. Un utilisateur peut se déconnecter et supprimer son compte
   depuis son espace personnel. La suppression est définitive
   et complète.
4. Les mentions légales et la politique de confidentialité
   d'Abomey sont en place, accessibles librement, et le
   consentement à la création du compte est tracé.

## Découpage en incréments

### Tranche 1 — Walking skeleton : connexion (Google et Apple) et déconnexion

Premier flux de bout en bout. Un visiteur peut s'inscrire et se
connecter via la page d'authentification déléguée, en
choisissant Google ou Apple, et se déconnecter. Les deux
fournisseurs sont couverts d'emblée parce que la délégation à
la page d'authentification rend leur ajout simultané sans
surcoût applicatif.

- **Critère d'acceptation** : depuis la page d'accueil, un
  visiteur clique sur « Se connecter », arrive sur la page
  d'authentification déléguée, choisit Google ou Apple,
  s'authentifie, revient sur Abomey. Au premier passage, un
  compte est créé automatiquement et il atterrit sur un écran
  de bienvenue affichant son nom complet. Un bouton « Se
  déconnecter » détruit sa session et le ramène à la page
  d'accueil. À la connexion suivante avec le même compte, il
  retrouve le même espace. Le mécanisme fonctionne y compris
  quand Apple masque l'email réel derrière un email relais. Un
  même humain qui se connecte une fois via Google puis revient
  via Apple obtient deux espaces distincts, sans fusion.
- **Règles métier couvertes** : R2 (identité stable),
  R3 (indépendance des fournisseurs), R5 (minimum de données
  demandées), R6 partiellement (authentification requise pour
  l'espace personnel, sans redirection vers URL d'origine
  encore).
- **Reporté** : isolation des Joueurs et Parties, suppression
  de compte, mentions légales, redirection vers URL initiale
  après login, traitement explicite des erreurs côté fournisseur.

### Tranche 2 — Isolation des Joueurs par utilisateur

L'aggregate Joueur (déjà implémenté dans le code, sans notion
de propriétaire à ce jour) reçoit un propriétaire. Toute
opération sur les Joueurs est filtrée par l'utilisateur
connecté.

- **Critère d'acceptation** : un Joueur créé par un utilisateur
  est visible et modifiable uniquement par cet utilisateur. Une
  tentative d'accès direct à un Joueur d'un autre utilisateur
  (URL devinée, ID forgé) renvoie une réponse équivalente à
  « ressource introuvable », sans révéler l'existence du
  Joueur. La même logique sera appliquée par défaut à toute
  ressource utilisateur introduite par les sujets ultérieurs
  (Parties, etc.).
- **Règles métier couvertes** : R1 (isolation totale), appliquée
  aux Joueurs comme première ressource utilisateur d'Abomey.
- **Reporté** : isolation des Parties (l'aggregate n'existe pas
  encore — sera couvert par le sujet « création d'une Partie »),
  suppression de compte.

### Tranche 3 — Mentions légales, politique de confidentialité et consentement

Mise en conformité RGPD minimale : pages publiques et
traçabilité du consentement à l'inscription.

- **Critère d'acceptation** : un visiteur peut consulter les
  pages « Mentions légales » et « Politique de confidentialité »
  sans être connecté, depuis un lien permanent en pied de page.
  Au premier passage d'un nouvel utilisateur (après son retour
  du fournisseur d'identité, avant la création effective de son
  espace), il doit accepter explicitement la politique de
  confidentialité. La date du consentement et la version du
  document acceptée sont enregistrées.
- **Règles métier couvertes** : R7 (conformité RGPD).
- **Reporté** : suppression de compte, redirection vers URL
  initiale.

### Tranche 4 — Suppression du compte

L'utilisateur peut exercer son droit à l'effacement depuis son
espace.

- **Critère d'acceptation** : un utilisateur connecté accède à
  une page « Mon compte » qui présente un bouton « Supprimer
  mon compte ». Une confirmation simple est demandée. Après
  validation, tout son contenu (profil, Joueurs) est effacé
  immédiatement et son identité est également supprimée chez
  le fournisseur d'identité intermédiaire. L'utilisateur peut
  se réinscrire avec le même compte Google ou Apple : il
  repart d'un espace vierge.
- **Règles métier couvertes** : R4 (suppression dure et
  complète).
- **Reporté** : redirection vers URL initiale.

### Tranche 5 — Redirection vers l'URL initiale après authentification

Dernier élément pour fermer la spec : l'utilisateur qui arrive
sur une URL profonde sans être connecté est ramené à cette URL
après login.

- **Critère d'acceptation** : un visiteur non connecté qui
  demande une page protégée (lien partagé, marque-page,
  redirection interne) est redirigé vers la page d'accueil
  pour se connecter, puis automatiquement ramené à la page
  initialement demandée après authentification. Le mécanisme
  fonctionne quel que soit le fournisseur choisi.
- **Règles métier couvertes** : R6 (authentification requise,
  dans son intégralité).
- **Reporté** : —
