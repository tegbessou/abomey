# Suivi des sujets produit — Abomey

## #002 — Création d'une Partie
- **Ouvert le** : 2026-05-15
- **Dernière touche** : 2026-05-15
- **Échéance** : —
- **Contexte** : Permettre à un utilisateur authentifié et
  consentant de créer une Partie de tarot dans son espace
  personnel, en choisissant un Mode de tarot et un groupe de
  Joueurs (avec possibilité de créer des Joueurs à la volée
  dans le même flux si nécessaire). C'est la fonctionnalité qui
  débloque la valeur d'Abomey : sans Partie, l'utilisateur ne
  peut rien faire après son inscription.
- **Prochaine action** : découper en incréments (phase 4)
- **Spec** : `produit/abomey/creation-partie.md`
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
- **Spec** : `produit/abomey/comptes-utilisateurs.md`
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
    (AccountDeletionTest). `Makefile` : memory_limit phpstan
    monté à 512M.
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
