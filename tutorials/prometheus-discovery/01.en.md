---
SPDX-License-Identifier: MIT
path: "/tutorials/prometheus-discovery"
slug: "prometheus-discovery"
date: "2020-09-15"
title: "Prometheus Service Discovery"
short_description: "This tutorial will help you get started with Prometheus Service Discovery"
tags: ["Hetzner Official", "Hetzner Cloud", "hcloud", "Prometheus"]
author: "Hetzner Online"
author_link: "https://github.com/hetzneronline"
author_img: "https://avatars3.githubusercontent.com/u/30047064"
author_description: ""
language: "en"
available_languages: ["en"]
header_img: "header-6"
---

## Introduction

[Prometheus](https://prometheus.io/) is a modern open-source metrics and alerting/monitoring solution. This tutorial will help you to configure the Prometheus Hetzner Service Discovery for Dedicated and Cloud servers.

The service discovery will talk to the Hetzner Robot Webservice and Hetzner Cloud API to get a list of your servers and automatically add them as scrape targets to collect metrics.

**Prerequisites**

You need at least Prometheus version 2.21.0, which you can download on [prometheus.io](https://prometheus.io/download/).
This tutorial does not cover the installation of Prometheus itself.

We assume you have installed Prometheus and all your servers are running the [node_exporter](https://github.com/prometheus/node_exporter) on Port 9100.

For general help with installing and configuring Prometheus see the [excellent documentation](https://prometheus.io/docs/introduction/first_steps/).

## Step 1 - Generate Credentials

Depending on which servers you would like to "discover", you will need credentials for the appropriate access.

For Robot servers, you will need Robot Webservice credentials. To get these, log in to [Robot](https://robot.your-server.de/) and go to "Settings" by clicking the user menu icon in the upper right corner.

For Cloud servers, you will need a Hetzner Cloud API token. To create one, log in to the [Cloud Console](https://console.hetzner.cloud), select your project and go to the "Security" tab.

## Step 2 - Add Service discovery to Prometheus config

Extend your scrape_config in prometheus.yml like in the example below.

* The "robot" role is used for discovering Hetzner Dedicated Root servers, managed via the Robot web interface.
* For Hetzner Cloud servers use the "hcloud" role.
* Remove the roles you don't need.

For more details on the configuration of the service discovery you can check the [official Prometheus documentation](https://prometheus.io/docs/prometheus/latest/configuration/configuration/#hetzner_sd_config).

```text
scrape_configs:
  - job_name: hetzner_service_discovery
    hetzner_sd_configs:
      - role: "hcloud"
        bearer_token: "<Your Hetzner Cloud API Token>"
        port: 9100
      - role: "robot"
        basic_auth:
          username: "<Your Hetzner Robot Webservice Username>"
          password: "<Your Hetzner Robot Webservice Password>
        port: 9100
```

The node_exporter uses port 9100 by default.

## Step 3 - Restart Prometheus and check discovered servers

Restart Prometheus and check the Prometheus Dashboard at `http://<your-prometheus-ip>/service-discovery`
After a few seconds you should see all your discovered Hetzner Robot and Hetzner Cloud servers there.
The following labels are automatically added to each discovered target:

```text
__meta_hetzner_server_id: the ID of the server
__meta_hetzner_server_name: the name of the server
__meta_hetzner_server_status: the status of the server
__meta_hetzner_public_ipv4: the public ipv4 address of the server
__meta_hetzner_public_ipv6_network: the public ipv6 network (/64) of the server
__meta_hetzner_datacenter: the datacenter of the server
```

The labels below are only available for targets with the role set to hcloud:

```text
__meta_hetzner_hcloud_image_name: the image name of the server
__meta_hetzner_hcloud_image_description: the description of the server image
__meta_hetzner_hcloud_image_os_flavor: the OS flavor of the server image
__meta_hetzner_hcloud_image_os_version: the OS version of the server image
__meta_hetzner_hcloud_image_description: the description of the server image
__meta_hetzner_hcloud_datacenter_location: the location of the server
__meta_hetzner_hcloud_datacenter_location_network_zone: the network zone of the server
__meta_hetzner_hcloud_server_type: the type of the server
__meta_hetzner_hcloud_cpu_cores: the CPU cores count of the server
__meta_hetzner_hcloud_cpu_type: the CPU type of the server (shared or dedicated)
__meta_hetzner_hcloud_memory_size_gb: the amount of memory of the server (in GB)
__meta_hetzner_hcloud_disk_size_gb: the disk size of the server (in GB)
__meta_hetzner_hcloud_private_ipv4_<networkname>: the private ipv4 address of the server within a given network
__meta_hetzner_hcloud_label_<labelname>: each label of the server
```

The labels below are only available for targets with the role set to robot:

```text
__meta_hetzner_robot_product: the product of the server
__meta_hetzner_robot_cancelled: the server cancellation status
```

## Conclusion

You can find more information about running and configuring Prometheus in the [official documentation](https://prometheus.io/docs/introduction/overview/).

##### License: MIT

<!--

Contributor's Certificate of Origin

By making a contribution to this project, I certify that:

(a) The contribution was created in whole or in part by me and I have
    the right to submit it under the license indicated in the file; or

(b) The contribution is based upon previous work that, to the best of my
    knowledge, is covered under an appropriate license and I have the
    right under that license to submit that work with modifications,
    whether created in whole or in part by me, under the same license
    (unless I am permitted to submit under a different license), as
    indicated in the file; or

(c) The contribution was provided directly to me by some other person
    who certified (a), (b) or (c) and I have not modified it.

(d) I understand and agree that this project and the contribution are
    public and that a record of the contribution (including all personal
    information I submit with it, including my sign-off) is maintained
    indefinitely and may be redistributed consistent with this project
    or the license(s) involved.

Signed-off-by: Hetzner Online <tutorials@hetzner.com>

-->
