# Task 1 — Infra provisionnée en OpenTofu

> Sujet : [product/deploiement.md](../../product/deploiement.md)

## Objectif
Provisionner le VPS Hetzner et son réseau en **code**, de façon
reproductible, sans clic manuel non documenté. Kamal ne provisionne
pas les serveurs : cette tranche crée le serveur sur lequel il
déploiera ensuite.

## Livré
- Un dossier `infra/` avec la configuration OpenTofu (provider
  `hcloud`) : serveur, firewall, clé SSH.

## Critères de vérification
- `tofu apply` crée le serveur Hetzner sans intervention manuelle.
- Le serveur est joignable en SSH avec la clé déclarée.
- Le firewall n'ouvre que 22 (SSH), 80 et 443.
- `tofu destroy` puis `tofu apply` recrée un serveur équivalent
  (infra reproductible).

## Definition of Ready
- Compte Hetzner Cloud + token API disponible.

## Reporté
- Backend de state distant (state local au départ ; à externaliser
  si l'infra grandit ou se partage).
- DNS géré en code (le domaine et son enregistrement A restent
  manuels au départ — préalable externe du sujet).

## Plan technique
- **Provider** : `hcloud`, token via variable d'environnement
  `HCLOUD_TOKEN` (jamais commité).
- **Ressources** :
  - `hcloud_ssh_key` — la clé publique de déploiement.
  - `hcloud_server` — type `cx22` (ou équivalent ~4-6 €/mois),
    image Debian/Ubuntu stable, région EU (Nuremberg/Falkenstein),
    clé SSH attachée.
  - `hcloud_firewall` — règles entrantes 22/80/443, attaché au
    serveur.
- **Fichiers** : `infra/main.tf`, `infra/variables.tf`,
  `infra/outputs.tf` (exporter l'IP publique pour Kamal et le DNS).
- **State** : local (`infra/.terraform*` et `*.tfstate` ignorés par
  git), migrable vers un backend distant plus tard.
