variable "server_name" {
  description = "Nom du serveur et préfixe des ressources associées."
  type        = string
  default     = "abomey"
}

variable "server_type" {
  description = "Type de serveur Hetzner Cloud."
  type        = string
  default     = "cx23"
}

variable "image" {
  description = "Image système de base (hôte Docker)."
  type        = string
  default     = "debian-13"
}

variable "location" {
  description = "Datacenter Hetzner (région EU)."
  type        = string
  default     = "nbg1"
}

variable "ssh_key_name" {
  description = "Nom de la clé SSH enregistrée chez Hetzner."
  type        = string
  default     = "abomey-deploy"
}

variable "ssh_public_key_path" {
  description = "Chemin vers la clé publique SSH autorisée sur le serveur."
  type        = string
  default     = "~/.ssh/id_rsa_prod.pub"
}
