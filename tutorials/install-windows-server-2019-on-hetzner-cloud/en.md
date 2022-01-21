---
SPDX-License-Identifier: MIT
path: "/tutorials/install-windows-server-2019-on-hetzner-cloud"
slug: "install-windows-server-2019-on-hetzner-cloud"
date: "2022-02-21"
title: "Install Windows Server 2019 on Hetzner Cloud"
short_description: "We will see how to install Windows Server 2019 on a Hetzner Cloud server (the procedure is almost identical for other versions of Windows Server)."
tags: ["Windows"]
author: "Dezeiraud Gaëtan"
author_link: "https://github.com/Brouilles"
author_img: "https://avatars.githubusercontent.com/u/1573282"
author_description: "I started development by creating mods on video games and it quickly became a real vocation. I like to experiment, discover and learn as and when my professional and personal projects. I develop as much for the Web as for software or Video Games."
language: "en"
available_languages: ["en"]
header_img: "header-x"
cta: "product"
---

## Introduction

We will see how to install Windows Server 2019 on a Hetzner Cloud server (the procedure is almost identical for other versions of Windows Server).

**Prerequisites**

You need your own official activation key for Windows Server 2019. You can follow the tutorial without it. But you need to activate Windows Server in order to enjoy the features that the system offers.

## Step 1 - Create the server

The first step is the same as for other servers. Go to your Hetzner Cloud dashboard and click on "Add a server".

Choose your location, server type and ubuntu for the operating system. I recommend at minimum a server type with at least 4GB of RAM. Below that, Windows Server can be laggy. Then "Create & Buy now".

## Step 2 - Install Windows Server 2019

Now, go to your server details. On the left menu, go to "ISO images". Here you can see a list of ISO for a lot of operating systems and also drivers. Search "Windows Server 2019 English" in the list (for me it is on the second page). The long name is "SW_DVD9_Win_Server_STD_CORE_2019_64Bit_English_DC_STD_MLF_X21-96581.iso".

So now click on **Mount** and open the console (see screenshot).

![hetzner cloud console](/images/hetznercloud-console.png)

Keeping the console pop-up open and restart the server (click on ON and OK and click again on the same button who is now OFF). We want it to boot to the Windows ISO. On the console pop-up, click on **Connect**.

Now the server boot on the Windows Server OOBE (Out Of the Box Experience). Like every Windows Server setup, follow the steps. If you want the desktop interface. Choose the version with (Desktop Experience).

At **Which type of installation do you want?** choose **Custom**. As you can see, the installer doesn't find any disk. Conserve the Console pop-up open and go back to your server details. Again in ISO images search **virtio-win-0.1.208** (first page for me, the version can be different, but it is not a problem) and click on **Mount**.

Return to the Console pop-up and click on **Load Driver**, under the refresh button. A new window open, choose **Browse** then **CD Drive virtio-win**. Search and extend the **vioscsi** folder then **2k19** (for Windows Server 2019, 2k16 is for Windows Server 2016) and click on **amd64** and **OK**. Click **Next** (it can take a long time).

You are redirected to the drive selection and now the main disk of your server is listed! So **Delete** it (we remove Ubuntu). And create a new drive with **New**.

Because we have mounted **virtio-win** we now need to mount again **Windows Server 2019 English** like the first time (don't close the console pop-up). When it is done, go back to the pop-up and selection **Drive 0 Partition 2** (Sometimes the installer does not offer the "Next" button when the correct partition is selected. Just click on System Reserved and select again *Drive 0 Partition 2*). Click **Next**.

This will take a while, Windows installation is in progress.

At the end of the installation, mount again **virtio-win-0.1.208**. In the console pop-up, you normally can see the Windows boot menu (with the logo). The first time, it asks you to configure the administrator account. When it is done, click on the "CTRL + ALT + DEL" button at the bottom right of the window and enter your Administrator password.

Good job! You are now on your Windows Server Desktop. But you can see we don't have internet on it.

Only one last step left. Open the **Device Manager**. After **other devices**, **Ethernet Controller** click on it and choose **Update Driver Software...**. Select **Browse my computer for driver software**, click on Browse and extand the **CD Drive virtio-win**, **NetKVM**; **2k19** and **amd64**. Click **OK** and **Next**. A Windows Security window open, check "Always trust" and **Install**.

Now Windows detects the network and open a right pane **Do you want to allow your PC to be discoverable...** choose **No**.

You can close the Device Manager.
In the **ISO images** menu of your server, click on **Unmount** at the top of the page.

## Conclusion

Activate it with an official key and voilà! Your Windows Server 2019 is now setup.

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

Signed-off-by: Dezeiraud Gaëtan<gaetan@dezeiraud.com>

-->
