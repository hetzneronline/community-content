---
SPDX-License-Identifier: MIT
path: "/tutorials/install-apache-guacamole"
slug: "install-apache-guacamole"
date: "2023-07-04"
title: "Remote Desktop im Browser - Apache Guacamole"
short_description: "This tutorial explains how to install Apache Guacamole."
tags: ["Cloud", "Browser", "Cloudron"]
author: "Martin"
author_link: "https://github.com/Nature1Limited"
author_img: "https://avatars.githubusercontent.com/u/116512556?v=4"
author_description: ""
language: "en"
available_languages: ["en", "de"]
header_img: "header-8"
---

# Remote desktop in the browser - Apache Guacamole

## Introduction

Want to easily access all of your remote desktops from all of your devices? <br>
It's easy with Apache Guacamole. <br>
This post explains how to install Apache Guacamole and set up the connections.

<br>

### Requirements

+ a top-level domain, e.g. `example.com`
+ an email address
+ a server (at least CPX11, better with more resources)
+ a public IP address (IPv4 recommended)
+ Operating system: Ubuntu 22.04 LTS or newer
+ a connection to the server command line

<br>

## Step 1 - Install Cloudron

[Apache Guacamole](https://guacamole.apache.org/) must first be installed to ensure that you can call up and operate the graphical interface via the browser. However, since the configuration of Guacamole is a bit complicated, I will use a preconfigured version in this tutorial. Such a very well pre-configured variant is available with "Cloudron", a graphical web interface for Docker operated by the company of the same name.

To start the installation, connect to your server with SSH:

````bash
ssh root@[your IP address here]
````
Accept the new fingerprint by typing `yes`.

You are now connected to your server.

Before you can install Cloudron, you must first update your server. To do this, run the following command:

````bash
sudo apt-get full-upgrade
````

To then install Cloudron on your server, run the following commands:

````bash
# downloads an installation script
wget https://cloudron.io/cloudron-setup
# makes the script executable
chmod +x ./cloudron-setup
# runs the script
./cloudron-setup
````

(Note: Installation may take some time).

If the installation was successful, reboot the system.

## Step 2 - Set up Cloudron
Now call up the IP address of your server in your browser. You will be taken to a web page where you need to make some settings (if your browser shows a warning that the website is unsafe, you can ignore it. To do this, click on advanced and then on continue to...):

![the warning](images/Screenshot_warning.png)

![Cloudron's initial website](images/Screenshot_inital-site.png)

Cloudron requires a domain. Enter this in the top field (e.g. `example.com`).

Select `Hetzner` as DNS provider. Now you need an API token. You can get this token in the [DNS Console] (https://dns.hetzner.com).

In the DNS Console, click on `Mange API tokens`.
![the Hetzner DNS Console, the corresponding button is marked](images/Screenshot_dns-console.png)

In the submenu enter, a name (e.g. Cloudron) and click on `Create access token`.
![Access token tagged](images/Screenshot_access-token.png)

Copy the token to the clipboard and paste it into the field at Cloudron. Then continue (this may take a moment).

Now create the user account. To do this, fill in the fields:

![the Cloudron welcome screen](images/Screenshot_Cloudron-Welcome.png)

Congratulations! You are now the proud administrator of a Cloudron instance.

## Step 3 - Install Apache Guacamole

After logging into your Cloudron instance, you should get a message that no apps are installed yet. Visit the App Store to change this.

![Cloudron - the options for installing new apps have been marked](images/Screenshot_final-configurated-site.png)

When you visit the App Store, you will be prompted to create an account with Cloudron.io. Do this by filling out the dialog. (Or log in if you already have an account).

![image of configuration page](images/Screenshot_cloudronio-acount-setup.png)

Once you have done this, you can access the App Store. Search "guacamole" in the search bar at the top. Click on the entry, scroll down and click `Install`.

In the next window, enter the subdomain on which you want to access your Guacamole instance (type `guac` if you want to access the domain `guac.example.com`).

In your initial dashboard, when you see that the application has a status of 'Running', click on it. Read the information displayed, check the box and click `Open Guacamole`.

![the Cloudron information screen](images/Screenshot_Guacamole-information-screen.png)

Login to Guacamole (username + password = guacadmin).

Change the password immediately (!). To do this, click on `guacadmin` in the upper right corner. Then select Preferences and click on `Preferences`. Enter the old and the new password under `Change Password` and confirm the input.

![the initial screen of Guacamole - the corresponding button is highlighted](images/Screenshot_guacamole-inital-screen.png)

Congratulations! You have successfully installed Guacamole! I will explain how to store a connection in the next step.

## Step 3 - Set up a new connection

Using the example of the SSH connection of the server running Cloudron and Guacamole, I would like to demonstrate how to create a new connection.

To create a new connection, open the settings (see step 2).

Now select the `Connections` tab, click on `New Connection`.

![the settings page, the corresponding buttons are highlighted](images/Screenshot_Guacamole-settings-newconnections.png)

In the `New Connection` submenu, first enter a name for the entry (e.g. _Server - Guacamole_). Select _SSH_ for protocol. If you plan to give multiple people access to your instance, you should take a look at the 'Concurrency Limits' menu. If you are the only user, you can ignore the next categories up to `Parameters`.

The category `Parameters` is very important. <br>
Under the **Network** heading, enter the IP of your server in the _Hostname_ field. If you use the default port, you can ignore the other parameters.<br>
**Authentication** is also relevant. You can enter your login data in these fields. You can also leave the fields empty, but then you have to log in manually every time you connect. <br>
Under **Display**, you can change the appearance a bit if you like. I recommend just trying something out.

## Ending

In this tutorial, you learned how to easily set up a Guacamole instance using Docker and Cloudron. <br>

Of course, you can not only manage SSH connections, but also e.g. RDP and VNC. This allows you to easily set up a cloud PC. If you want more information about the configuration, I highly recommend the official [documentation](https://guacamole.apache.org/doc/gug/index.html). <br>
Nevertheless, I hope that I was able to help you a little with my little tutorial.



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

Signed-off-by: Martin <m6prca02w@mozmail.com>

-->

