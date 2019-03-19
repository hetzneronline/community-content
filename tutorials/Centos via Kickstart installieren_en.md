# CentOS/Fedora via Kickstart installieren
## Introduction
This article explains the automatic installation of CentOS via Kickstart.

* The Hetzner.Kickstarter-Script prepares an installation environment on the first hdd
* The Kickstart-Configuration-file determines the components to install and automatically carrys out the installation
* There is no need for a remote console( e.g. KVM-console)

## Preparations

* Create the Kickstart-Configuration-File. __Caution:__There must not be a single error in this file! 
*  deposit the Kickstart-Configuration-File on your server
* Alternatively pick one of the [Hetzner Kickstart Examples](https://wiki.hetzner.de/index.php/Hetzner_Kickstart_Examples)
* Edit the Reverse-DNS-Entry for your server in Robot

## Configuring the installation environment

* Activate the Resuce-Mode(64bit) in Robot
* perform a reset (automatical hardware reset)
* log in to your server via SSH
* Download the Hetzner.Kickstarter-Script 

`wget https://wiki.hetzner.de/images/2/24/Hetzner.Kickstarter.txt`

* execute the Hetzner.Kickstarter-Scripts 

`sh Hetzner.Kickstarter.txt https://wiki.hetzner.de/images/9/91/Kickstart.cfg.txt`

* Reboot the server 

`reboot`

## Automatic Installation

* Installation will start directly after the reboot and should finish remotely
* A reboot will happen once the installation is finished
* The Server should be accessible via ssh after 10-20 minutes 

### Optional: Watch the installation via VNC

You can watch the installation via VNC. VNC (NAT Port 5500) will start on its own, if a VNC Listener is active and configured accordingly. 
Alternatively you can start VNC manually, like during [Hetzner VNC Installation](https://wiki.hetzner.de/index.php/VNC-Installationen/en)

## Further Information
* [Reverse DNS](https://wiki.hetzner.de/index.php/DNS-Reverse-DNS/en)
* Deposit a key at Hetzner
* VNC listener behind NAT-Router

## Conclusion
By now you should have installed CentOS via kickstart on your server.