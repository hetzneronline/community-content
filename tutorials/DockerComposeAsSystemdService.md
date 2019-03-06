# Run multiple Docker Compose services on Debian/Ubuntu

## Introduction

This tutorial will show you how you can run multiple Docker Compose services via a systemd service template.

**Prerequisites**
* Server with Debian/Ubuntu
  * [Tutorial for Debian](https://www.debian.org/releases/stable/amd64/)
  * [Tutorial for Ubuntu](https://help.ubuntu.com/lts/installation-guide/amd64/index.html)
* Docker already installed and running
  * [Tutorial for Debian](https://docs.docker.com/install/linux/docker-ce/debian/)
  * [Tutorial for Ubuntu](https://docs.docker.com/install/linux/docker-ce/ubuntu/)
* Docker Compose installed at `/usr/local/bin/docker-compose` as instructed in the [official tutorial](https://docs.docker.com/compose/install/) (otherwise you may need to adapt the path in the examples)

## Step 1 - Create Docker Compose files

For this tutorial we will store our Docker Compose service configurations under `/etc/docker-compose`.

```bash
mkdir /etc/docker-compose
```

Just as an example, let's say we want to run [watchtower](https://hub.docker.com/r/v2tec/watchtower/) to keep our running containers up to date. We have to create a directory for that service in `/etc/docker-compose`:

```bash
mkdir /etc/docker-compose/watchtower
```

And we need the according Docker Compose file at `/etc/docker-compose/watchtower/docker-compose.yml`:

```
version: "3"

services:
  watchtower:
    image: v2tec/watchtower
    restart: unless-stopped
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
    command: --cleanup --no-pull
```

For more info about the options specified under `command` take a look at [watchtower](https://hub.docker.com/r/v2tec/watchtower/).
Here we specify that old images should new removed after updating to a new image (`--cleanup`).
We also tell watchtower that it should not check for newer images itself (`--no-pull`). That is because it's actually recommended to use this option if you are building your own images (without pushing them to the Docker Registry). We will take care of pulling new images regularly later. If you don't plan on building your own images then you can just omit `--no-pull` and also skip step 3.

## Step 2 - Create the systemd service template

Now we create the [systemd service template](https://www.freedesktop.org/software/systemd/man/systemd.service.html#Service%20Templates) at `/etc/systemd/system/docker-compose@.service`:

```
[Unit]
Description=docker-compose %i service
Requires=docker.service network-online.target
After=docker.service network-online.target

[Service]
WorkingDirectory=/etc/docker-compose/%i
Type=simple
TimeoutStartSec=15min
Restart=always

ExecStartPre=/usr/local/bin/docker-compose pull --quiet --ignore-pull-failures
ExecStartPre=/usr/local/bin/docker-compose build --pull

ExecStart=/usr/local/bin/docker-compose up --remove-orphans

ExecStop=/usr/local/bin/docker-compose down --remove-orphans

ExecReload=/usr/local/bin/docker-compose pull --quiet --ignore-pull-failures
ExecReload=/usr/local/bin/docker-compose build --pull

[Install]
WantedBy=multi-user.target
```

This service template will:
* try to pull new versions of the used Docker images on startup and reload of the systemd service
* try to build the images if it's configured this way in the `docker-compose.yml` on startup and reload of the systemd service
* remove orphan containers (e.g. after you changed a containers name or removed a container from the `docker-compose.yml`)

If you wonder why the start timeout is a bit long, some Docker images may need some time to be built which is done when starting the service.

Now we make systemd reload the service files:

```bash
systemctl daemon-reload
```

## Step 3 - Regularly pull and build new Docker images (optional)

Since in the service template we configured it to pull and build our images on a reload of the systemd service we can also create a cronjob for this so we will regularly pull/build new images:

```bash
echo '0  4    * * *   root    /bin/systemctl reload docker-compose@*.service' >> /etc/crontab
```

Please note that this only works as intended in combination with 'watchtower' as shown above as new images will not automatically be used.

## Step 4 - Start Docker services

With this setup we can now start a Docker Compose service (in this case 'watchtower'):

```bash
systemctl start docker-compose@watchtower
```

And enable it to start on boot:

```bash
systemctl enable docker-compose@watchtower
```

## Conclusion

You have now setup an environment where you can easily start different Docker Compose services as systemd services. For each additional service you just need to:
* create the according `/etc/docker-compose/servicename` directory
* create at least a `/etc/docker-compose/servicename/docker-compose.yml` file (and whatever else you need for the service)
* start the service via `systemctl start docker-compose@servicename`
* (optional) start the service on boot with `systemctl enable docker-compose@servicename`
