---
path: "tutorials/deploy-laravel-on-hetzner-cloud"
date: "2019-03-08"
title: "Deploy Laravel App on Hetzner Cloud"
short_description: ""
tags: ["Hcloud","Cloud","Laravel","Deploy"]
author: "Ahmed Gad"
author_link: "https://github.com/engahmedgad"
author_img: "https://avatars3.githubusercontent.com/u/25646168"
author_description: ""
header_img: ""
---

# Deploying Laravel app on Hetzner cloud

## Introduction
In this tutorial I'll show you how to deploy Laravel application on Hetzner cloud servers

## Step 1 - Create your first instance
* Login to your cloud dashboard from [https://console.hetzner.cloud]
* Create new project and name it whatever you want
* Choose your server location it's up to you
* Click `Add Server` and select `Ubuntu 18.04` from Image
* Choose the resources you need from Type
* Add your local machine SSH Key 
    * You can read [this](https://help.github.com/en/enterprise/2.16/user/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent) article to know how to generate SSH key
* Write your server hostname in `name` input
* Click `Create & Buy Now`
 

## Step 2 - Login into you server
* Go to servers list in console cloud and copy your servers IP address (e.g. 10.0.0.1)
* Open your terminal and write `# ssh root@<IP Address>`
* You will see now welcome message from your server

## Step 3 - Installing Apache
 type these commands: 
* `# apt-get update` to update Ubuntu package manager
* `# apt-get install apache2` to install Apache 
* `# service apache2 status` to make sure that the apache is working as a service if you see ` active (running)` then everything is ok

## Step 4 - Install MySQL 
* `# apt-get install mysql-server`  to install mysql 
* `# mysql_secure_installation` to set your mysql password.
    * the prompt will ask you few question 
        * Would you like to setup VALIDATE PASSWORD plugin? : Y
        * Enter password validation policy : 0
        * New password: `your password`
        * Re-enter new password: `your password again`
        * Do you wish to continue with the password provided? : Y
        * Remove anonymous users? : Y
        * Disallow root login remotely? : Y
        * Remove test database and access to it? : Y
        * Reload privilege tables now? : Y     
* `# mysql -u root -p` it will ask you for your password if you see `Welcome to the MySQL monitor` then everything is ok

## Step 5 - Install php 
* `# apt-get install software-properties-common`
* `# add-apt-repository ppa:ondrej/php` to add php7.1 repository 
* `# apt-get update` to update your package manager
* Install php and required extensions 
```
# apt-get install php7.1 php7.1-xml php7.1-mbstring php7.1-mysql php7.1-json php7.1-curl php7.1-cli php7.1-common php7.1-mcrypt php7.1-gd libapache2-mod-php7.1 php7.1-zip php7.1-dom
``` 
## Step 6 - Install Composer
* First download Composer installer 
    * `# curl -sS https://getcomposer.org/installer -o composer-setup.php`
* Install Composer globally
    * `# php composer-setup.php --install-dir=/usr/local/bin --filename=composer`
* Now let's verify that is Composer is installed successfully type in your terminal `# composer` if you see something like `Composer version 1.8.4 2019-02-11 10:52:10
` then everything is ok

## Step 7 - Install Laravel project
* `# cd /var/www/html`
* `# composer create-project --prefer-dist laravel/laravel blog "5.6.*"`
* Now let's move the files from `blog/` directory to Apache's root directory `# cd blog/ && mv * ../ && mv .env ../`
* Go to `http://10.0.0.1/public` and you will see your app now


## Conclusion
We have learned now how to use Hetzner Cloud Console and create new server 
and install Laravel -LAMP stack- environment.
