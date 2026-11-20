variable "hcloud_token" {
  description = "Your Hetzner Cloud API Token"
  type        = string
  sensitive   = true
}

variable "controller_ssh_pub" {
  description = "Path to the public key for the Jenkins Controller"
  default     = "~/.ssh/hetzner_controller_key.pub"
}
