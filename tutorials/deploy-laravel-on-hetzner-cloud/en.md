---
path: "tutorials/deploy-laravel-on-hetzner-cloud"
date: "2019-03-08"
title: "Deploy Laravel App on Hetzner Cloud"
short_description: ""
tags: ["hcloud","cloud","laravel","deploy"]
author: "Ahmed Gad"
author_link: "https://github.com/engahmedgad"
author_img: "https://avatars3.githubusercontent.com/u/25646168"
author_description: ""
header_img: ""
---

# Deploying Laravel App on Hetzner Cloud

## Introduction
In this tutorial I'll show you how to deploy laravel application on Hetzner cloud servers

## Step 1 - Create Your first instance
* Login to your Cloud Dashboard from [https://console.hetzner.cloud]
* Create New Project And name it whatever you want
* Choose your server location it's up to you
* Click `Add Server` and select `Ubuntu 18.04` from Image
* Choose the resources you need from type
* Add your local machine SSH Key 
    * You can read [This](https://help.github.com/en/enterprise/2.16/user/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent) article to know how to generate SSH Key
* Write your server hostname in `name` Input
* Click `Create & Buy Now`
 

## Step 2 - login into you server
* Go to servers list in console cloud and copy your server  `IP Address` 
* Open your  terminal and write ` ssh root@<IP Address>`
* You will see now welcome message from your server

## Step 3 - Installing Apache
 type these commands: 
* `sudo apt-get update` to update ubuntu package manager
* `sudo apt-get install apache2` to install apache 
* `sudo service apache2 status` to make sure that the apache is working as a service if you see ` active (running)` then everything is ok

## Step 4 - Install MySQL 
* `sudo apt-get install mysql-server`  to install mysql 
* `sudo mysql_secure_installation` to set your mysql password.
    * the prompt will ask you few question 
        * Would you like to setup VALIDATE PASSWORD plugin? : Y
        * Enter password validation policy : 0
        * New password: `Your Password`
        * Re-enter new password: `Your Password Again`
        * Do you wish to continue with the password provided? : Y
        * Remove anonymous users? : Y
        * Disallow root login remotely? : Y
        * Remove test database and access to it? : Y
        * Reload privilege tables now? : Y     
* `mysql -u root -p` it will ask you for your password if you see `Welcome to the MySQL monitor` then everything is ok

## Step 5 - Install PHP 
* `sudo apt-get install software-properties-common`
* `sudo add-apt-repository ppa:ondrej/php` to add php7.1 repository 
* `sudo apt-get update` to update your package manager
* install php and required extensions 
```
sudo apt-get install php7.1 php7.1-xml php7.1-mbstring php7.1-mysql php7.1-json php7.1-curl php7.1-cli php7.1-common php7.1-mcrypt php7.1-gd libapache2-mod-php7.1 php7.1-zip php7.1-dom
``` 
## Step 6 - Install Composer
* `sudo apt-get install composer`

## Step 7 - Install Laravel Project
* `cd /var/www/html`
* `composer create-project --prefer-dist laravel/laravel blog "5.6.*"`
* Now let's move the files from `blog/` directory to apache's root directory ` cd blog/ && mv * ../ && mv .env ../`
* Go to `http://<IP Address>/public` and you will see your app Live now 


## Conclusion
We have learned now how to use Hetzner Cloud Console and create new server 
and install laravel -LAMP stack- environment.