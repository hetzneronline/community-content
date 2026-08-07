terraform {
  required_providers {
    hcloud = {
      source  = "hetznercloud/hcloud"
      version = "~> 1.68"
    }
  }
}

provider "hcloud" {
  token = var.hcloud_token
}

resource "hcloud_ssh_key" "admin_key" {
  name       = "jenkins-admin-key"
  public_key = file(var.controller_ssh_pub)
}

resource "hcloud_network" "jenkins_net" {
  name     = "jenkins-network"
  ip_range = "10.0.0.0/16"
}

resource "hcloud_network_subnet" "jenkins_subnet" {
  network_id   = hcloud_network.jenkins_net.id
  type         = "cloud"
  network_zone = "eu-central"
  ip_range     = "10.0.1.0/24"
}

resource "hcloud_firewall" "agent_fw" {
  name = "jenkins-agent-firewall"
  rule {
    direction = "in"
    protocol  = "tcp"
    port      = "22"
    source_ips = ["${hcloud_server.jenkins_controller.ipv4_address}/32"]
  }
}

resource "hcloud_firewall" "controller_fw" {
  name = "jenkins-controller-firewall"
  rule {
    direction = "in"
    protocol  = "tcp"
    port      = "22"
    source_ips = ["0.0.0.0/0"] # For production, restrict to your home/office IP
  }
}

resource "hcloud_server" "jenkins_controller" {
  name        = "jenkins-controller"
  image       = "ubuntu-24.04"
  server_type = "cx23"
  location    = "fsn1"
  ssh_keys    = [hcloud_ssh_key.admin_key.id]
  firewall_ids = [hcloud_firewall.controller_fw.id]

  network {
    network_id = hcloud_network.jenkins_net.id
    ip         = "10.0.1.100" #agents will get ips from 10.0.1.1 up to 10.0.1.99 -> space for 99 agents
  }


  user_data = <<-EOF
    #!/bin/bash
    set -e
    
    # Wait for network connectivity
    until curl -fsS https://pkg.jenkins.io/ >/dev/null; do
      echo "Waiting for network..."
      sleep 5
    done
    
    # Install Java 21 and Jenkins
    apt-get update
    apt-get install -y fontconfig openjdk-21-jre-headless

    curl -fsSL https://pkg.jenkins.io/debian/jenkins.io-2026.key | tee /usr/share/keyrings/jenkins-keyring.asc > /dev/null
    echo deb [signed-by=/usr/share/keyrings/jenkins-keyring.asc] https://pkg.jenkins.io/debian binary/ | tee /etc/apt/sources.list.d/jenkins.list > /dev/null

    apt-get update
    apt-get install -y jenkins
    systemctl enable --now jenkins
  EOF
}

output "jenkins_public_ip" {
  value       = hcloud_server.jenkins_controller.ipv4_address
  description = "The public IP of your Jenkins controller. Create an SSH tunnel to this IP, then access Jenkins at http://localhost:8080 (or your custom port)."
}
