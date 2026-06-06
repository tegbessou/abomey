# Suivi des sujets produit — Abomey

## #003 — Saisie et calcul des Donnes
- **Ouvert le** : 2026-05-23
- **Dernière touche** : 2026-06-06
- **Échéance** : —
- **Contexte** : Permettre à un Utilisateur de saisir en
  direct les Donnes successives d'une Partie, avec calcul
  automatique des scores. Sujet qui débloque toute la valeur
  d'Abomey : sans saisie de Donnes, l'investissement #001 et
  #002 reste sans usage et l'utilisateur retourne à l'app
  payante existante.
- **Prochaine action** : attaquer la Tranche 3 (Mort
  manuel). T0, T1, T2 (a+b+c+d) livrées. Branche de
  travail : `feat/003-saisie-donnes` (commits empilés
  jusqu'à fin de l'US, push à la fin).
- **Spec** : `product/saisie-donnes.md`
- **Notes** :
  - 2026-05-23 — problème validé en phase 1.
  - Posture acceptée : saisie en direct entre deux Donnes
    (pas en différé après la soirée).
  - Cadrage du problème : substituer une app dédiée payante
    qui se complexifie sans raison ; différenciateurs visés
    = gratuité, simplicité (par soustraction), persistance
    par Utilisateur.
  - Alternatives écartées : (A) rester sur l'app payante,
    rejet implicite ; (B) saisie de score libre sans calcul
    auto, rejetée car régression par rapport à l'app cible et
    contraire à `docs/domain.md` (« calcul automatique »).
  - Posture retenue pour la phase 4 : périmètre minimal
    d'abord, étendu ensuite (Alt. C). Acte explicite qu'on
    s'autorise à livrer du « pas complet ».
  - Critère de succès : une soirée complète (~15-30 Donnes)
    tenue sur Abomey sans recours à l'app existante ni
    calcul manuel, avec scores corrects, sans abandon en
    cours de soirée. Dogfooding par moi en première instance.
  - 2026-05-23 — incohérence préexistante résolue en
    ouverture de sujet : `docs/glossary.md` mentionnait une
    « Partie close » contradictoire avec `docs/domain.md`.
    Aligné sur `domain.md` : pas d'état clos dans Abomey,
    fin de Partie décidée par les joueurs physiques.
  - 2026-05-23 — phase 2 explorée par Example Mapping
    (Matt Wynne). Règles D1 à D26 identifiées :
    saisie classique, saisie Vachette (en classement
    strict, pas en points bruts), primes (PAB, Poignées
    multi avec annonceur, Chelem, Misères Atouts/Tête en
    +10×(N−1)/−10), gestion du Mort manuelle, correction
    limitée à la dernière Donne, Scores cumulés dérivés.
    Compromis assumé : pas de correction rétroactive.
  - 2026-05-23 — phase 3 formalisée :
    `product/saisie-donnes.md` créé. Dépendance préalable
    explicitement notée : `docs/scoring.md` doit exister
    avant la phase 4 (formules FFT, barème Vachette,
    application des primes). Trois questions UX
    (Qo4, Qo9, Qo11) reportées à la phase 4.
  - 2026-05-23 — phase 4 découpée en **10 tranches**
    verticales : T1 walking skeleton (classique à 4,
    tablée = Mode, sans primes) ; T2a Petit au Bout ;
    T2b Chelem ; T2c Poignée(s) ; T2d Misère(s) ;
    T3 Mort manuel ; T4 Tarot à 5 (Partenaire / Preneur
    seul) ; T5 Tarot à 3 ; T6 Donne Vachette ;
    T7 correction de la dernière Donne. Critère de succès
    du sujet atteint à la fin de T7. Posture de
    validation : T1 et T2a validées par l'auteur après
    livraison ; T2b/c/d enchaînées en série sans
    revalidation systématique. `docs/scoring.md` créé en
    pré-requis intégré, complété tranche par tranche.
  - 2026-05-23 — `docs/scoring.md` créé (partie classique
    sans primes : multiplicateurs Garde/Garde Sans/Garde
    Contre = 1/2/4, buts 56/51/41/36, formule
    `(25+|E|)×M`, convention « réalisé = but → Preneur
    gagne », répartition à 4 joueurs ±3×Score / ∓Score, 4
    exemples chiffrés). Pré-requis T1 acquis.
  - 2026-05-24 — découpage révisé en **11 tranches** :
    ajout d'une **T0 (Liste des Parties et navigation)** en
    pré-requis de T1. Motivation : sans liste des Parties,
    l'Utilisateur n'a pas d'accès stable à ses Parties
    d'un jour à l'autre, et T1 n'est pas utilisable en
    conditions réelles. T0 traite aussi la dette 1 du
    suivi #002 (test d'isolation sur la liste et sur la
    page détail). Contenu T0 : navbar enrichie pour les
    connectés (Mes Parties / Créer / Compte / Déconnexion),
    redirection `/` → `/games` pour les connectés,
    liste sous forme de cartes (nom, Mode, participants,
    état « pas encore de manche jouée »), empty state,
    tests isolation. La carte est enrichie en T1 avec
    nombre de Donnes et score cumulé par Joueur.
  - 2026-05-24 — **T0 livrée**. Domain : `Game.createdAt`
    + getter. Application : `CreateGameCommandHandler`
    injecté avec `ClockInterface` (PSR-20) ; nouveau
    `ListMyGamesQueryHandler` + `ListMyGamesQuery` +
    `GameSummaryView`. Infra : `GameRepository::ofOwner`
    sur l'interface, implémenté par `DoctrineGameRepository`
    (`ORDER BY g.createdAt DESC`) et
    `InMemoryGameRepository` (tri usort). Migration
    `Version20260524120000` (ALTER TABLE games ADD
    created_at). UI : `ListGamesController` (`/games`),
    template `games/index.html.twig` (cartes / empty state),
    navbar dans `base.html.twig` (4 liens connectés),
    `HomeController` redirige `/` → `/games` pour
    connectés, `home.html.twig` allégé en landing
    publique, `LogtoAuthenticator` fallback
    `app_games_index`. CSS cartes (grille responsive,
    hauteur uniforme, hover ombré, badge mode). Tests : 1
    unit Domain (`createdAt`), 1 unit Application
    (`itStampsTheGameWithTheClockInstantAtCreation`), 3
    unit Application sur `ListMyGames` (vide, mapping +
    isolation, participantNames, tri), 2 integration
    `DoctrineGameRepository::ofOwner` (tri, isolation), 4
    e2e WebTestCase `ListGamesTest` (empty state, cartes,
    isolation liste, 404 URL forgée) +
    `HomePageTest::theHomePageRedirectsConnectedUsersToTheirGames`.
    Total : 59 unit + 16 integration + 15 e2e, quality 0
    violation. Dette 1 du suivi #002 fermée.
  - 2026-05-24 — préférences capturées en mémoire
    auto-memory : **lisibilité avant concision** (variables
    nommées, foreach explicites, pas de first-class
    callable `X::method(...)`, max 1 niveau d'imbrication
    par fonction) ; **réponses concises** (recommandation
    plutôt qu'options exhaustives). À appliquer
    systématiquement.
  - 2026-05-24 — convention exception command handler
    actée et capturée en mémoire : aggregate introuvable
    = `DomainException` dédiée (ex. `GameNotFoundException`),
    pas `\LogicException`. **Dette transverse identifiée**
    sur les handlers déjà livrés à harmoniser hors-T1 :
    `AcceptPrivacyPolicyCommandHandler`,
    `DeleteAccountCommandHandler` (Account, both throw
    `\LogicException` quand user introuvable) ;
    `GetUserDisplayNameQueryHandler` (Account, query
    devrait retourner null plutôt qu'exception). À
    cadrer comme tranche de refacto dédiée après T1, ou
    sujet #004 « Harmonisation des exceptions au boundary ».
    Doc à ajouter : convention « handler not found »
    explicite (dans `symfony-conventions` skill, ADR
    dédiée, ou CLAUDE.md projet — à arbitrer).
  - 2026-06-06 — **T1 livrée** (walking skeleton : Donne
    classique à 4 joueurs, tablée = Mode, sans primes).
    Domain : `Deal` Entity interne à l'Aggregate `Game`
    (validation D2/D5 + calcul FFT `pointsByPlayer`),
    enums `Contract` (multiplicateur via method) et `Bouts`
    (but FFT via method), `Game::recordClassicDeal` (verrou
    D9 temporaire `DeadPlayersNotYetSupportedException`,
    incrémentation auto de la position). Application :
    `RecordClassicDealCommandHandler` (avec
    `GameNotFoundException` dédiée, pas `\LogicException`),
    `ShowGameQueryHandler` enrichi (deals + cumulatives par
    participant via `ParticipantSummaryView`),
    `ListMyGamesQueryHandler` enrichi (dealCount + scores
    cumulés sur la carte). Read model partagé
    `App\Tarot\Application\Shared\ParticipantSummaryView`
    (id, name, cumulativeScore) consommé par les deux
    queries. Infra : OneToMany cascade `Game` → `Deal` avec
    `OrderBy position ASC`, `GameRepository::update`,
    migration `Version20260525120000` (table `deals`).
    UI : `RecordClassicDealController`
    (`/games/{id}/deals/new`, GET/POST), form symfony
    classique, template `new_deal.html.twig`, `show.html.twig`
    enrichi (tableau cumulatif Donnes en lignes / Joueurs
    en colonnes / Total en pied), carte de liste enrichie
    (scores cumulés au lieu de « pas encore de manche »
    quand `dealCount > 0`). CSS pour deals-table et
    game-card__scores. Tests : 8 unit Domain `Deal` (formule
    FFT sur 4 cas, validations D2/D5), 2 unit Domain `Game`
    (happy + verrou D9), 2 unit Application
    `RecordClassicDealCommandHandler`, 3 unit Application
    `ShowGameQueryHandler`, 1 unit Application
    `ListMyGamesQueryHandler` (cumulatives), 1 integration
    `DoctrineGameRepository` (round-trip deals), 2 e2e
    `RecordClassicDealTest`. Total : 74 unit + 17
    integration + 17 e2e, quality 0 violation.
  - 2026-06-06 — **T2 complète livrée** (T2a Petit au Bout,
    T2b Chelem, T2c Poignée(s), T2d Misère(s)).
    - Domain : enums `PetitAuBout`, `Chelem`,
      `PoigneeSize`, `MisereType` avec methods métier
      (`bonus`, `multiplier`, etc.). VOs `Poignee`,
      `Misere` (immutables). Exceptions dédiées
      (`PoigneeAnnouncerNotActiveException`,
      `MisereAnnouncerNotActiveException`,
      `DuplicateMisereException`). Mécaniques de calcul
      intégrées dans `Deal::pointsByPlayer` : PAB / Chelem
      / Poignées dans `score_net` (multiplié par
      defendersCount pour Preneur, à somme nulle) ;
      Misères en post-traitement (annonceur reçoit
      +10×(Mode−1), autres actifs −10, somme nulle par
      Misère).
    - Convention métier actée pour le Chelem : option (c)
      « grosse récompense » — bonus multiplié par
      defendersCount pour le Preneur, cohérent avec la
      mécanique des autres primes.
    - Migrations : `Version20260606120000` (petit_au_bout),
      `Version20260606130000` (chelem),
      `Version20260606140000` (poignees JSON),
      `Version20260606150000` (miseres JSON).
    - UI : `RecordClassicDealFormType` enrichi avec
      ChoiceType PAB/Chelem, CollectionType pour
      Poignées/Misères. `PoigneeFormType` et
      `MisereFormType` sub-forms. Modale `<dialog>` natif
      par collection avec liste compacte et icône `×`.
    - Stimulus `form_collection_controller` **générique**
      (refactor T2d) : targets `field` + `data-field-name`,
      utilisable pour Poignée et Misère. Pattern réutilisable
      pour futurs sujets multi-entrées.
    - Refacto symfony-conventions §3 : split du
      `RecordClassicDealController` en deux —
      `ShowRecordClassicDealFormController` (GET) et
      `RecordClassicDealController` (POST). Plus de mélange
      Query/Command dans un seul `__invoke`. Routes
      `app_game_deal_new` (GET) et `app_game_deal_record`
      (POST) sur le même path `/games/{id}/deals/new`.
    - Refacto qualité de code : `Deal::$poignees` →
      `Deal::$poigneesData` et idem Misères (signaler
      stockage Doctrine, pas VOs métier directs).
      `applyMiseres` → `withMiseresApplied` (immutable
      retournant un nouveau tableau, fin de la mutation
      par référence).
    - Tests : 4 unit Deal pour PAB (4 scénarios via data
      provider), 2 unit Deal pour Chelem, 3 unit Deal pour
      Poignée(s) (single, multi, announcer not active),
      4 unit Deal pour Misère(s) (single, double type,
      duplicate, announcer not active), 1 test Panther
      `RecordClassicDealWithPoigneeTest`. Total : 84 unit +
      17 integration + 18 e2e + 2 Panther, quality 0
      violation.
  - 2026-06-06 — **dette identifiée à traiter en refacto
    dédiée** : `Deal::$poigneesData` et `Deal::$miseresData`
    sont stockées comme `array<int, array{...}>` (JSON) côté
    Doctrine et reconstruites en VOs `Poignee`/`Misere` à
    l'appel de `Deal::createClassic`. Le naming `*Data` a
    été choisi pour signaler qu'il s'agit de la
    représentation persistance plutôt que des VOs métier
    eux-mêmes. À refacto proprement : stockage des VOs
    directement (Doctrine embeddable list / custom JSON
    type), avec round-trip transparent. Acté hors T2.
  - 2026-06-06 — **dette exceptions handler fermée**.
    `UserNotFoundException` créée dans
    `App\Account\Domain\User\` (extends `\DomainException`).
    `AcceptPrivacyPolicyCommandHandler` et
    `DeleteAccountCommandHandler` lèvent désormais
    `UserNotFoundException` au lieu de `\LogicException`.
    Code mort `GetUserDisplayName*` (Query + handler +
    test) supprimé : orpheline depuis T0 quand
    `HomeController` a basculé en redirection. Test
    characterization ajouté pour
    `DeleteAccountCommandHandler` (filet absent avant la
    refacto). `StubMessageBus` introduit dans
    `tests/Stub/Shared/` pour instancier `EventBus`
    sans dépendre du framework Messenger en unit test.
    Doc actée dans `~/teg/skills/symfony-conventions/`
    (thème 12, section « Exceptions métier côté handler »
    en amont du catch boundary).
  - **Choix non-triviaux T1** :
    - `Bouts` enum int-backed (la valeur = nombre de
      Bouts), `Contract` enum string-backed avec method
      `multiplier()` (sémantique métier). Asymétrie
      assumée.
    - `Deal::createClassic(Game, position, ...)` non
      nullable. Correction d'un compromis précédent
      (`?Game` pour préserver les tests Deal isolés) :
      les tests passent un Game via `GameBuilder` au prix
      d'un peu de boilerplate, le modèle reste propre.
    - `Deal` Doctrine Entity interne avec ID
      auto-incrémental (pas de DealId VO), pas de
      DealRepository (accès uniquement via `Game`).
    - Calcul des points à la lecture
      (`Deal::pointsByPlayer()` recalcule à chaque appel,
      pas stocké) — choix DDD strict : règles peuvent
      évoluer sans migration des Donnes existantes.
    - Verrou côté UI : bouton « Ajouter une Donne » visible
      seulement si `Mode == 4` et `tablée == Mode`. Verrou
      côté Domain : exception si `tablée > Mode`. Tarot à 3
      et 5 sans Partenaire passent gratuitement par la
      formule (mais hors-scope T1).

## #002 — Création d'une Partie
- **Ouvert le** : 2026-05-15
- **Dernière touche** : 2026-05-18
- **Échéance** : —
- **Contexte** : Permettre à un utilisateur authentifié et
  consentant de créer une Partie de tarot dans son espace
  personnel, en choisissant un Mode de tarot et un groupe de
  Joueurs (avec possibilité de créer des Joueurs à la volée
  dans le même flux si nécessaire). C'est la fonctionnalité qui
  débloque la valeur d'Abomey : sans Partie, l'utilisateur ne
  peut rien faire après son inscription.
- **Prochaine action** : sujet fonctionnellement livré.
  Ouvrir le sujet #003 (saisie des Donnes).
- **Spec** : `product/creation-partie.md`
- **Notes** :
  - 2026-05-15 — problème validé en phase 1
  - Confirmations clés : création de Joueurs autorisée dans le
    même flux ; après création, l'utilisateur atterrit sur la
    page détail de la Partie ; le Partenaire à 5 joueurs est
    défini par Donne (pas par Partie), cohérent avec domain.md.
  - 2026-05-15 — phase 2 explorée, spec formalisée en phase 3.
    Règles R1 à R8 (Mode immuable, effectif min, Joueurs figés,
    propriété, mêmes Joueurs que l'Utilisateur, distinction,
    effectif max = Mode + 2, nom non vide). Modale pour
    création de Joueurs à la volée. Pas de page dédiée
    Joueurs dans ce sujet.
  - 2026-05-17 — phase 4 non formalisée en tranches multiples :
    le sujet a été traité en une seule tranche verticale, du
    Domain à l'UI, dans un même commit
    (`e24bdf0`, « Add account management, game creation, and
    Panther E2E infrastructure »). À garder en tête pour les
    sujets suivants : un découpage plus fin (par exemple
    "création sans modale" puis "modale inline") aurait
    permis des revues plus ciblées.
  - 2026-05-17 — Domain `Game` : aggregate `final readonly`,
    `Mode` enum (3, 4, 5), `GameId` VO + générateur,
    `GameRepository`, exceptions métier
    (`EmptyGameNameException`, `DuplicateParticipantsException`,
    `TooFewParticipantsException`, `TooManyParticipantsException`,
    `ParticipantNotOwnedException`). R1 et R3 garantis
    structurellement (absence de mutateurs + `final readonly`).
    R2, R6, R7, R8 vérifiés dans `Game::create`.
  - 2026-05-17 — Application : `CreateGameCommandHandler` (R5
    vérifié via `PlayerRepository::ofIds` :
    `ParticipantNotOwnedException` si un participant n'appartient
    pas au propriétaire) ; `ShowGameQueryHandler` + `GameView`
    pour la page détail ; `ListMyPlayersQueryHandler` +
    `PlayerView` pour alimenter la liste du formulaire.
  - 2026-05-17 — Infrastructure : `DoctrineGameRepository`,
    `GameIdType` (custom Doctrine type), `SymfonyGameIdGenerator`,
    migration `Version20260515152753` (table `games`,
    `participant_ids` en JSON).
  - 2026-05-17 — UI : `CreateGameController` (`/games/new`),
    `ShowGameController` (`/games/{id}`), Live Component
    `CreateGameForm` (compteur en direct sur la plage du Mode),
    `CreateGameFormType` / `CreateGameFormData`. Infrastructure
    Panther mise en place (`AbomeyPantherTestCase`,
    `TestAuthenticator` via cookie, target `panther-test`
    dépendant de `assets-compile`).
  - 2026-05-18 — Modale inline de création de Joueur pendant la
    création de Partie (`20d661d`). Live Component
    `CreatePlayerForm` extrait, émet `player_created` ;
    `CreateGameForm` écoute via `LiveListener` et ajoute le
    Joueur fraîchement créé aux participants sélectionnés.
    Composant Twig partagé `Modal` (Stimulus + `<dialog>`
    natif) placé dans `Shared/UI` car générique.
    `Player::create` lève désormais `EmptyPlayerNameException`
    (domain) au lieu de `\InvalidArgumentException` ; `Player`
    devient `final`. La dette 4 du suivi #001 est partiellement
    résolue ; reste à harmoniser `readonly`.
  - 2026-05-18 — Tests pour ce sujet : 11 cas unit Domain
    (`GameTest`), 2 cas unit Application
    (`CreateGameCommandHandlerTest`, dont R5), 3 cas integration
    (`DoctrineGameRepositoryTest`), 1 cas e2e WebTestCase
    (`CreateGameTest`, empty state), 1 cas Panther E2E
    (`CreateGameTest`, parcours complet 4 Joueurs créés via la
    modale → atterrissage sur la page détail).
  - **Sujet « Création d'une Partie » fonctionnellement livré.**
  - **Critères de succès** :
    1. Création en formulaire unique avec modale à la volée :
       couvert par Panther E2E.
    2. Règles R1–R8 : R1, R2, R6, R7, R8 couvertes par
       `GameTest` ; R5 couverte par
       `CreateGameCommandHandlerTest` ; R3 et R4 structurelles.
    3. Atterrissage sur page détail : couvert par Panther E2E
       (`CreateGameForm::submit` redirige `app_game_show`).
    4. Isolation entre Utilisateurs côté affichage :
       `ShowGameQueryHandler` filtre par `ownerId`, **non
       couvert par un test explicite** « URL forgée d'un autre
       user → 404 » — voir dette 1 ci-dessous.
  - **Dettes identifiées** :
    1. Isolation côté lecture des Parties : aucun test direct
       du fait qu'un Utilisateur ne voit pas la Partie d'un
       autre. Couvert structurellement par
       `gameRepository->ofId(..., $ownerId)`. À ajouter en
       première intervention sur Tarot.
    2. `ShowGameQueryHandler` renvoie `'?'` pour un participant
       non trouvé. Comportement de fallback silencieux — à
       transformer en règle métier explicite quand on traitera
       la suppression de Joueurs (hors-scope actuel).
    3. `Player` est `final` mais pas `readonly` alors que
       `Game` est `final readonly`. Harmonisation cosmétique
       quand on retouche Tarot.
    4. Dette 5 du suivi #001 (subscriber de consentement
       supprimé, 403 brute possible) toujours ouverte ; pas
       adressée dans #002.

## #001 — Comptes utilisateurs et isolation des données
- **Ouvert le** : 2026-05-14
- **Dernière touche** : 2026-05-14
- **Échéance** : —
- **Contexte** : Abomey devient multi-utilisateur. Inscription
  publique ouverte via OAuth Google et Apple, isolation totale
  des données par utilisateur (chaque utilisateur a ses propres
  Joueurs et Parties, pas de partage). Authentification déléguée
  à Logto (service tiers OIDC, free tier).
- **Prochaine action** : étape B (configuration Logto) puis C
  (Symfony Security + UI + test e2e Playwright). Étape A
  (infrastructure persistance) terminée le 2026-05-14.
- **Spec** : `product/comptes-utilisateurs.md`
- **Notes** :
  - 2026-05-14 — problème validé en phase 1
  - 2026-05-14 — sujet "création d'une Partie" suspendu en
    attendant que celui-ci soit cadré et livré
  - 2026-05-14 — décision technique à formaliser en ADR :
    Logto Cloud comme provider OIDC, SDK PHP officiel à adapter
    à Symfony
  - 2026-05-14 — quoi exploré en phase 2, spec formalisée en
    phase 3
  - 2026-05-14 — découpage en 6 tranches verticales validé en
    phase 4. Walking skeleton = connexion Google + déconnexion ;
    Apple, isolation Joueurs, RGPD, suppression, redirection
    URL initiale dans les tranches suivantes
  - 2026-05-14 — décision en cours d'implémentation : passage
    de "deux boutons (Google, Apple) sur l'accueil" à "un seul
    bouton de connexion qui mène à la page d'authentification
    déléguée où l'utilisateur choisit son fournisseur".
    Motivation : simplification (pas de paramètre `provider`
    à transmettre, un seul authenticator), évolutivité (ajouter
    un fournisseur ne demande qu'une config Logto). Conséquence :
    fusion des anciennes tranches 1 et 2 en une seule tranche 1
    "Walking skeleton + Google + Apple" ; renumérotation des
    tranches suivantes (5 tranches au total). Spec mise à jour.
  - 2026-05-15 — découverte au moment d'intégrer le SDK Logto :
    les claims OIDC standard ne distinguent pas Google/Apple.
    Le `sub` retourné est l'identifiant Logto interne (Logto
    crée un compte distinct par identité sociale). En
    conséquence, refonte simplifiée du modèle domain : suppression
    de l'enum `Provider` et du VO `ExternalIdentity`. `User` stocke
    désormais directement un `LogtoSubject`. R2 et R3 reformulés
    dans la spec : R3 est garanti par Logto (sub distinct par
    identité sociale), sans modélisation explicite du provider
    côté Abomey.
  - 2026-05-15 — clarification position bounded contexts : les
    namespaces `Account/` et `Tarot/` sont actés comme deux BCs
    DDD distincts (et non « deux namespaces, un seul BC »
    comme initialement formulé en B). Conséquences : pas de FK
    BDD entre tables des deux BCs, cohérence référentielle au
    niveau applicatif, cascades inter-BCs via domain events.
    `docs/domain.md` mis à jour pour refléter cette position.
    À surveiller : la section ADR-001 « Choix de modélisation »
    cite Vernon — cohérent.
  - 2026-05-15 — T2 livré (Isolation des Joueurs). T2.1 :
    `Player` reçoit `string $owner`, `PlayerRepository::ofId`
    scopé par owner, command + handler adaptés. T2.2 :
    `DoctrinePlayerRepository` filtre par owner_id, migration
    `Version20260515130415` (ALTER TABLE players ADD owner_id),
    4 tests d'intégration dont deux explicitement dédiés à
    l'isolation par owner. Pas d'UI Players (reportée au sujet
    « création de Partie »), donc critère « URL forgée → 404 »
    reporté à ce moment-là. Total 25 tests unit + 9 intégration.
  - 2026-05-15 — T3 livré (RGPD + consentement) en 4 sous-batchs.
    T3.1 pages statiques + footer + Pico.css + traductions FR
    (default_locale: fr). T3.2 modèle de consentement (VO
    `PrivacyConsent`, attributs nullable sur `User`, migration
    `Version20260515133726`). T3.3 flux d'acceptation
    (`PrivacyConsentVoter`, `PrivacyConsentRequiredSubscriber`,
    `WelcomeController`, command + handler avec `ClockInterface`).
    T3.4 7 tests e2e (HomePageTest, LegalPagesTest, WelcomeFlowTest).
    Style ambré/doré avec logo SVG inline (carte de tarot + A).
  - 2026-05-15 — T4 livré (suppression de compte). `EventRecording`
    trait dans `Shared/Domain`, `UserDeleted` event,
    `User::delete()`, cascade cross-BC via domain event
    (`WhenUserDeletedHandler` dans Tarot consomme), `DeleteAccount`
    command + handler, `AccountController`. Tests : unit (User,
    handler Tarot), integration (delete, deleteAllOf), e2e
    (AccountDeletionTest). `Makefile` : memory_li 
  - 2026-05-15 — T5 livré (redirection URL initiale).
    `LogtoAuthenticator` utilise `TargetPathTrait`, `start()`
    sauvegarde la cible et redirige `app_home` (au lieu de
    `app_login`, conforme R6), `onAuthenticationSuccess()`
    lit et nettoie la cible. Tests e2e `TargetPathRedirectTest`.
  - **Sujet « Comptes utilisateurs et isolation des données »
    fonctionnellement livré.** Total tests : 53 (30 unit + 11
    integration + 12 e2e), quality 0 violation.
  - **Dettes identifiées** :
    1. Suppression côté Logto non implémentée (T4 D1=B). Le
       `sub` reste actif côté Logto, l'utilisateur peut se
       réinscrire sans soucis mais l'identité reste indexée
       chez Logto. À traiter via API Management Logto + M2M
       client si conformité totale R4 souhaitée.
    2. Target path non propagée à travers le flux de
       consentement. Un user sans consent qui demande une page
       profonde se voit redirigé sans mémorisation de la cible
       (cf. dette 5 ci-dessous : la 403 n'est plus interceptée).
    3. Test e2e du redirect via Voter non couvert (T3.4) : le
       router Symfony s'exécute avant `access_control`, donc
       une URL inexistante donne 404. À couvrir en unit test
       du Voter ou quand une route réellement protégée
       existera.
    4. `Player` non-`final` et `Player::create` lève
       `\InvalidArgumentException` SPL — écarts avec
       `php-conventions`. À harmoniser si on retouche Tarot.
    5. `PrivacyConsentRequiredSubscriber` supprimé le 2026-05-15.
       Le `PrivacyConsentVoter` reste actif et bloque
       l'accès aux pages protégées pour les users sans consent.
       Sans subscriber d'interception, Symfony renvoie une
       **403 Forbidden brute** au lieu de rediriger vers
       `/welcome`. UX dégradée : un user authentifié sans
       consent qui touche `/account` (par exemple) atterrit
       sur une page d'erreur. Mitigation possible si réintégré
       plus tard : `access_denied_handler` sur le firewall,
       plus idiomatique que le subscriber kernel.exception.
  - 2026-05-14 — `CLAUDE.md` projet, `docs/domain.md` et
    `docs/glossary.md` mis à jour : concept Utilisateur ajouté
    comme troisième Aggregate Root, isolation totale documentée
  - 2026-05-14 — ADR 001 rédigée : Logto Cloud, région EU,
    intégration SDK PHP officiel adapté à Symfony Security
    (`adr/001-fournisseur-authentification.md`)
  - 2026-05-14 — bounded context `Account/` créé (namespace
    distinct de `Tarot/`, conceptuellement même BC DDD)
  - 2026-05-14 — TDD complet du Domain et Application de T1 :
    `Provider` enum, VO `ExternalIdentity`, `Email`, `UserId`,
    aggregate `User`, handler `RegisterOrSyncUserCommandHandler`.
    28 tests / 41 assertions. Interfaces `UserRepository` et
    `UserIdGenerator` posées, fake `InMemoryUserRepository` et
    stub `StubUserIdGenerator` en place.
  - 2026-05-14 — Étape A (infrastructure persistance) terminée :
    annotations Doctrine sur `User` (école 1), `ExternalIdentity`
    en `#[Embeddable]` (perte du `readonly` école 1, `Email` le
    conserve via custom type), `UserIdType`, `EmailType`,
    `DoctrineUserRepository`, `SymfonyUserIdGenerator`, exclusion
    Domain dans `services.yaml` avec alias explicites (Account +
    Tarot), migrations `users` + `messenger_messages`, 4 tests
    d'intégration `DoctrineUserRepositoryTest`. Total 34 tests
    / 55 assertions, quality 0 violation.
  - **Choix techniques à harmoniser ou traiter à part** :
    `User` est `final`, `Player` ne l'est pas (écart à harmoniser
    sur Player) ; `services.yaml` ne contient pas encore
    l'exclusion `src/*/Domain/` (à corriger en passant) ;
    `Player::create` utilise `\InvalidArgumentException` SPL
    plutôt qu'une exception métier dédiée (à harmoniser).
  - **Incohérence préexistante à trancher hors de ce sujet** :
    `glossary.md` mentionne « Une Partie close ne peut plus
    accueillir de nouvelle Donne » alors que `domain.md`
    affirme « Une Partie n'a pas d'état "clos" dans Abomey ».
    À résoudre indépendamment
  - 2026-05-14 — à mettre à jour après livraison du sujet :
    `CLAUDE.md` projet, `docs/domain.md` (sections "Le projet",
    "Périmètre fonctionnel", "Ce qu'Abomey fera peut-être plus
    tard"), et l'aggregate `Player` qui n'a pas encore de
    notion de propriétaire
