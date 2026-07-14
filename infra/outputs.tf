output "server_ipv4" {
  description = "IP publique IPv4 du serveur, à renseigner dans Kamal et le DNS."
  value       = hcloud_server.app.ipv4_address
}

output "server_ipv6" {
  description = "IP publique IPv6 du serveur."
  value       = hcloud_server.app.ipv6_address
}

output "server_status" {
  description = "État courant du serveur."
  value       = hcloud_server.app.status
}
