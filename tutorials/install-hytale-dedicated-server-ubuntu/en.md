---
title: Installing a Hytale Dedicated Server on Ubuntu
description: Learn how to set up a Hytale server with a dedicated user, Java 25, and automated backups on Hetzner Cloud or Dedicated servers.
date: 2026-01-16
author: Rez2h
tags: [Gaming, Hytale, Ubuntu, Java, Server]
---

## Introduction

This tutorial explains how to install and run a **Hytale Dedicated Server** on **Ubuntu 22.04 or 24.04**. The server runs under a dedicated system user, uses **Java 25**, and is managed by **systemd** for automatic startup and reliable operation. With this setup, you will have a production-ready Hytale server with automated backups and proper resource management.

**Prerequisites**

* **Ubuntu 22.04 LTS** or **24.04 LTS** (fresh installation recommended)
* At least **4 GB RAM** (8 GB or more recommended for better performance)
* Root or sudo access to the server
* Basic knowledge of Linux command line operations
* A Hytale account with the Standard Edition of the game

**Example terminology**

* Username: `hytale`
* Installation directory: `/opt/hytale`

## Step 1 - System Update and Firewall Configuration

First, update your system and install the required tools. This ensures all packages are up to date and you have the necessary utilities for the installation.

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y zip unzip curl screen wget
```

### Step 1.1 - Configure UFW Firewall

Hytale uses UDP port 5520 for the QUIC protocol, which needs to be accessible from the internet. You should also allow SSH access to maintain remote administration capabilities.

```bash
sudo ufw allow 22/tcp
sudo ufw allow 5520/udp
sudo ufw enable
```

When prompted to proceed with the firewall activation, type `y` and press Enter.

## Step 2 - Create a Dedicated User

Running the Hytale server under a dedicated user account improves security by limiting potential damage if the server is compromised. This follows Linux best practices for running services.

```bash
sudo adduser hytale
sudo usermod -aG sudo hytale
```

You will be prompted to set a password and enter optional user information. You can skip the optional fields by pressing Enter.

Next, create the installation directory and set proper ownership:

```bash
sudo mkdir -p /opt/hytale
sudo chown -R hytale:hytale /opt/hytale
```

## Step 3 - Install Java 25

Hytale requires Java 25 to run. We will use SDKMAN, a tool that simplifies the installation and management of Java versions.

Switch to the `hytale` user account:

```bash
su - hytale
```

Install SDKMAN and Java 25:

```bash
curl -s "https://get.sdkman.io" | bash
source "$HOME/.sdkman/bin/sdkman-init.sh"
sdk install java 25.0.1-tem
```

SDKMAN will download and install Java 25. After the installation completes, you can verify it with:

```bash
java -version
```

Exit back to your administrative user:

```bash
exit
```

## Step 4 - Download and Authenticate

To run a Hytale server, you need an account that already owns the **Standard Edition** of the game (currently approx. 23,79$ in Germany). You can purchase the game and download the necessary tools at [hytale.com/download](https://hytale.com/download).
Now you need to download the Hytale server files and authenticate your server with your Hytale account.

Switch to the hytale user and navigate to the installation directory to download the server tools:

```bash
su - hytale
cd /opt/hytale
```

## Step 4.1 - Download and Extract

Download and run the official downloader:

```bash
wget https://downloader.hytale.com/hytale-downloader.zip
unzip hytale-downloader.zip
chmod +x hytale-downloader-linux-amd64
./hytale-downloader-linux-amd64
```

The downloader fetches a version-specific zip file (e.g., 2026.01.17-4b0f30090.zip). You must identify and unzip it manually:

1. List the files to find the name:

```bash
ls
```

2. Unzip the identified file:

```bash
# Replace with your actual filename
unzip 2026.01.17-4b0f30090.zip
```

Verify that the Server/ folder and Assets.zip are present:

```bash
ls -F
```

### Step 4.2 - Authenticate the Server

Start the server for the first time to perform authentication:

```bash
java -Xms4G -Xmx4G -jar Server/HytaleServer.jar --assets Assets.zip
```

In the server console, execute the following command:

```bash
/auth login device
```

This will provide you with a URL and a code. Open the URL in your web browser and enter the code to authenticate the server with your Hytale account.

After successful authentication, run:

```bash
/auth persistence Encrypted
```

This saves your authentication credentials securely. You can now stop the server by pressing `CTRL + C`, then type `exit` to return to your administrative user.

## Step 5 - Create a Systemd Service

Creating a systemd service allows the Hytale server to start automatically on system boot and provides easy management commands.

Create the service file as root or with sudo:

```bash
sudo nano /etc/systemd/system/hytale.service
```

Insert the following configuration:

```ini
[Unit]
Description=Hytale Dedicated Server
After=network.target

[Service]
User=hytale
Group=hytale
WorkingDirectory=/opt/hytale
ExecStart=/home/hytale/.sdkman/candidates/java/current/bin/java -Xms8G -Xmx16G -jar Server/HytaleServer.jar --assets Assets.zip --backup --backup-frequency 10 --backup-dir backups
KillSignal=SIGTERM
TimeoutStopSec=60
SendSIGKILL=no
Restart=always
RestartSec=10
StandardOutput=append:/var/log/hytale.log
StandardError=append:/var/log/hytale.log

[Install]
WantedBy=multi-user.target
```

**Note:** Adjust the memory allocation (`-Xms8G -Xmx16G`) according to your server's available RAM. The example above allocates 8 GB minimum and 16 GB maximum.

Save the file and exit the editor (in nano: press `CTRL + X`, then `Y` to confirm, then `Enter` to confirm the filename).

### Step 5.1 - Start and Enable the Service

Reload systemd to recognize the new service, enable it for automatic startup, and start it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable hytale
sudo systemctl start hytale
```

Check the service status to ensure it's running correctly:

```bash
sudo systemctl status hytale
```

You can view the server logs with:

```bash
sudo tail -f /var/log/hytale.log
```

## Step 6 - Automated Backups

Regular backups are essential to protect your world data from corruption or accidental deletion. This script will create daily backups and automatically remove backups older than 7 days.

Switch to the `hytale` user:

```bash
su - hytale
cd /opt/hytale
```

Create the backup script:

```bash
nano backup.sh
```

Insert the following content:

```bash
#!/bin/bash
TIMESTAMP=$(date +%F)
mkdir -p /opt/hytale/backups
tar -czf /opt/hytale/backups/world_$TIMESTAMP.tar.gz -C /opt/hytale universe/
find /opt/hytale/backups/ -mtime +7 -delete
```

Save and close the file. Make the script executable:

```bash
chmod +x /opt/hytale/backup.sh
```

### Step 6.1 - Setup Automated Backup Schedule

Configure a cron job to run the backup script daily at 4:00 AM:

```bash
(crontab -l 2>/dev/null; echo "0 4 * * * /opt/hytale/backup.sh") | crontab -
```

You can verify the cron job was added by running:

```bash
crontab -l
```

Exit back to your administrative user:

```bash
exit
```

## Step 7 – Server Management

Here are some useful commands for managing your Hytale server:

### Check server status:
```bash
sudo systemctl status hytale
```

Stop the server:
```bash
sudo systemctl stop hytale
```

Restart the server:
```bash
sudo systemctl restart hytale
```

View live logs:
```bash
sudo tail -f /var/log/hytale.log
```

View recent log entries from systemd:
```bash
sudo journalctl -u hytale -n 50
```

## Step 8 - Connecting to your Server

Once the server is running, you can join your world:
1. Start your Hytale client on your local computer.
2. Navigate to **Multiplayer**.
3. Click on **Direct Connect** (or Add Server).
4. Enter your server's **IP address**. The default port is `5520`, which is used automatically unless changed.
---

## Conclusion
Congratulations! Your Hytale dedicated server is now running securely under a dedicated user account with automatic startup, logging, and daily backups. The systemd service ensures your server will restart automatically if it crashes and will start on system boot.
You can now connect to your server using the server's IP address and the default Hytale port (5520).
