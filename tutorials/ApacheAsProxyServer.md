# Apache as proxy server on Debian/Ubuntu

## Introduction

With this tutorial, you will get an idea how to set up Apache as a proxy server (e. g. for bringing websites online with IPv4 that are directly reachable just via IPv6)

**Prerequisites**
* Server with Debian or Ubuntu already installed
* Up-to-date system - the following commands will update your complete system:
```bash
apt update ; apt upgrade
```
* Running SSH session as root.

## Step 1 - Install Apache web server

The first step you need to do is to install the Apache web server (e. g. using "apt"):
```bash
apt install apache2
```
If you're asked if you want to continue, please select "Y".

When this process is finished, please try to reach your IP address inside your web browser (e. g. http://10.0.0.1). You should now be able to see the default website of the Apache web server.

## Step 2 - Activate mod_proxy_html

The next step would be activating the proxy module for Apache. This module was already installed when running the installation of Apache as described in Step 1.
```bash
a2enmod proxy
a2enmod proxy_html
a2enmod proxy_http
```

If everything worked fine without any errors, you need to restart the Apache web server to load the modules that you have activated before.
```bash
systemctl restart apache2
```

You can check if the modules were loaded properly by running the following command:
```bash
apache2ctl -M
```
Please check if you can find the following lines inside the output:
```bash
[...]
proxy_module (shared)
proxy_html_module (shared)
proxy_http_module (shared)
[...]
```

## Step 3 - Set up a basic virtual host

At first create a new empty file:
```bash
touch /etc/apache2/sites-available/001-source.example.com.conf
```

Open the file you have just created inside your favorite editor. We'll use vim in that tutorial.