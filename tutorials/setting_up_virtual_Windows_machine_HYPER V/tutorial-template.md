---
SPDX-License-Identifier: MIT
path: "/tutorials/setting_up_virtual_Windows_machine_HYPER V"
slug: "setting_up_virtual_Windows_machine_HYPER V"
date: "2019-05-24"
title: "Virtual Windows machine with Hyper V"
short_description: "Setting up an virtual Windows machine with Hyper V"
tags: ["Server", "virtualization", "Windows", "Hyper V", "OS"]
author: "TaktischerSpeck - Vincent"
author_link: "https://github.com/TaktischerSpeck"
author_img: "https://avatars2.githubusercontent.com/u/29396439"
author_description: "Passionate illiterate with a slight urge to drool.
Leidenschaftlicher Analphabet mit leichtem Drang zum Sabbern."
language: "en"
available_languages: ["en"]
header_img: ""
---

<!-- This where the actual tutorial begins. You don't need to write out the title again, having it in the frontmatter above is enough. -->

## Introduction

Hello, i gonna show you how to install your own virtual Windows Server on an existing Windows Server.

**Prerequisites**

Open an Webbrowser and head to "http://mirror.hetzner.de/bootimages/windows/".

Download your wanted Windows Version and download the .iso file.

Additionall IP (The buy process is gonna be included in the Tutorial)
Windows Server (This Tutorial only shows this on Windows Server 2016 other version could have differneces)
Remotedesktop Connection to the Windows Server

## Step 1 - Additionall IP

This step is only needed if you did not already bought an Additionall IP, if you did proccede with Step 2.

Login into you robot account (https://robot.your-server.de ), now Klick on Server and Klick on the server where you want to add the IP.

When you expanded your Server klick on "IPs" and then on the grey button in the left bottom corner "Order additional IPs / networks".

Now Chose "A paid IP (price (monthly): 1.00 € / Setup (once): 0.00 €)" and write as Reson for the ip "Virtual Server".

When your IP Order arrived proccede with step 2 

## Step 2 - Mac Address

klick on the Network icon right to the IP "Request separate MAC".

Now klick on the red "Request separate MAC" Button, now you should see an Mac Adress wich should look like this: MAC: 00:50:56:00:E5:7B

Copy and write down the MAC Address for later.

### Step 3 Hyper V Install

Login into your Windows Server via Remotedesktop.

Start your Server manager and klick on "Add Role or feature"

Skip every side until you came to Server role, there make an leftklick on the "Hyper V" box and klick Add Features.

Now Skip until you can press "install" after the install pleas restart your Server once.

## Step 4 Setting up the Virtual Server

Start Hyper V, if you are not autmaticlly connectet Right click on Hyper V Manager and Connect with your LOKAL Computer.

Klick at the right side on "New -> Virtual Computer".

Now Chose an Name, it can be any name, also you can assaing an path where the VM should be installed to.
Klick Next

Chose Generation 1.
Klick Next

Now you can Setup the RAM of the Server, also you can activate Dinamic RAM wich means that the Server does not use as example 16GB Ram everytime, the Server uses the RAM he needs + 20% Puffer if the server needs less RAM your main Server does need less RAM to.
I Chose for 16000GB ram with Dynamic RAM.
Klick Next

Leav the Network config on "Not Connected"
Klick Next

Now you can Chose wich size your harddive gonna have, windows needs about 25GB.
Also you can edith the Path.
Klick Next

Now klick on "Install OS Later"
Klick Next

Klick on "Finish"

Right Click on your new Virtual Machine and klick on Settings.

Now you can edit your Hardware of the Server.

I would recommend to higher the core count for the install at least.

Head to Networkcard and chose your Virtual Switch connection.

After this expand "networkcard" by klicking und the plus and go to extendet Features.

Klick on Static Mac Address and fill in your previous copied MAC Adrres then Click on "Apply" and "OK"

You can now change how many cores your VM Should have aswell, i would give it the maximum amount of cores (or cores -1 vor the Host system) that the install is short as possible, also you could do this for the RAM (8-12 GB should be more then enough).

Now head to "IDE-Controller 1" and klick on "DVD Drive".

Click on imagefile and now "search" the .iso you donwloaded before.

Click "apply" and "ok"

## Step 5 Setting up Windows

![Image of 2](https://imgur.com/a/0gxgAbg)

Double tab on the your VM Name and then click on "Start".

Now you should see the Hyper V Logo following of Loading files and a Windows Logo.

When the Windows Setup startet chose your Keyboard and language preferences you want and click on "continue".

Click on "Install now".

Chose the Windows Variant you would like for me its Windows Server 2016 Standart (IMPORTANT you need to install the version with (Desktop view) behind the name, without this you only get an command Shell, click on continue

Accept the License and click on continue

Now take custom and click on the Harddrive you want to install windows to (In my case there is only one), click continue

Now is your Server installing Windows you can now wait until its finished but dont Shutdown or suspend the VM or Host system.

In the next Step your Administrator password.

After this you can use your VM and have fun.



Yet more instructions.

### Terminology
* Username: `holu` (short for Hetzner OnLine User)
* Hostname: `<your_host>`
* Domain: `<example.com>`

Server:
* IPv4: `<10.0.0.1>`
* IPv6: `<2001:db8:1234::1>`

Gateway:
* IPv4: `<192.0.2.254>`
* IPv6: `<2001:db8:1234::ffff>`

Client private:
* IPv4: `<198.51.100.1>`
* IPv6 `<2001:db8:9abc::1>`

Client public:
* IPv4: `<203.0.113.1>`
* IPv6: `<2001:db8:5678::1>`

## Step N - <summary of step>

More instructions.

## Conclusion

A short conclusion summarizing what the user has done, and maybe suggesting different courses of action they can now take.

##### License: MIT

<!---

Contributors's Certificate of Origin

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

Signed-off-by: [submitter's name and email address here]

-->
