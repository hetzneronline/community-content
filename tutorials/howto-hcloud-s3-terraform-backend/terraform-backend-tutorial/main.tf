terraform {
  backend "s3" {
   
    bucket = "$BUCKET_NAME"
    key    = "terraform-backend-tutorial/terraform.tfstate"
    endpoints = {
      s3 = "$ENDPOINT"
    }
    
    skip_requesting_account_id = true
    skip_credentials_validation = true
    skip_metadata_api_check     = true
    skip_region_validation      = true
    use_path_style            = true
  }
  required_providers {
    hcloud = {
      source  = "hetznercloud/hcloud"
      version = "1.59.0"
    }
  }
}

# Hetzner Cloud related configs
variable "hcloud_token" {
  sensitive = true
}
provider "hcloud" {
  token = var.hcloud_token
}

# Provision a single VM on Hetzner Cloud
resource "hcloud_server" "vm" {
  name        = "tutorial-vm"
  image       = "ubuntu-24.04"
  server_type = "cx23"
  location    = "nbg1"

  public_net {
    ipv4_enabled = true
    ipv6_enabled = false
  }

  labels = {
    managed_by = "terraform"
  }
}
output "vm_id" {
  description = "ID of the provisioned VM"
  value       = hcloud_server.vm.id
}

output "vm_ipv4" {
  description = "Public IPv4 address of the VM"
  value       = hcloud_server.vm.ipv4_address
}

output "vm_name" {
  description = "Name of the VM"
  value       = hcloud_server.vm.name
}
