# VNC installation
## Introduction

Since June 2007 Hetzner Online offers VNC installations through the Robot.

VNC is software that shows the screen output of a remote computer (running the VNC server software) on a local computer (running the VNC viewer software), and it can send keyboard and mouse movements that are made on the local computer to the remote computer. Thus, the remote computer can be installed as though you are sitting directly in front of the remote computer and monitor.

During this process, all data is copied over the network. Since the needed data is located on Hetzner's download servers, the installation of the packets should not take longer then when installing from a CD or DVD.

However, you need a [VNC client](http://en.wikipedia.org/wiki/Virtual_Network_Computing) to access the installation routine.

## How to start a VNC installation?

To start a VNC installation, click on the `VNC` tab of your server in Robot. Here you will see a dropdown list of available operating systems, as well as the architecture and the language. After selecting the configuration you want, and pressing the activate button, a password will be displayed with which you can login to the VNC installation, a few minutes after you restart the server. VNC address would be:

`<IP Address>:1`

or

`<IP Address>:5901`

Example:
```
192.168.0.1:1
192.168.0.1:5901
```

Which operating systems and versions can be installed using VNC?

* CentOS 6.9
* CentOS 7.5
* Fedora 28
* openSUSE 42.3
* openSUSE 15.0 

## Specifics: openSuSE

The VNC installation of openSuSE is done in two steps. First, the languages​​, the time, the partitions, the packages, etc. are selected and the boot loader is installed. After installation, the system will reboot and automatically setup a VNC connection again. You can login (again) with the previously used password. In this second step, the installation is completed, which usually requires only minimal user interaction. The system then reboots normally and is accessible via SSH.

__Please note:__

By default a firewall is installed and active, blocking SSH! Thus, during the installation (first step) the proposed selection of packages should be reduced to the "extended basic package". This way no firewall will be installed. A firewall can then be easily installed and configured from YAST after the installation via VNC.

Attention: If a firewall gets installed during the initial step of the installation, the server will not be accessible afterwards.

__Please note the following for installations with software RAID:__

If `/boot` gets installed on its own RAID 1 partition, then the configuration of the boot loader must be changed. The boot loader must be started from within the MBR. openSuSE does not automatically change the configuration of the boot loader (GRUB), which can lead to the system failing to boot.

If the swap is also installed on its own RAID 1 partition, then it will not be active the first time the system is booted.

With `cat /proc/mdstat` the swap partition appears as `active (auto-read-only)`.

Depending on your configuration a resync could follow between the different RAID partitions. The progress can be followed with `cat /proc/mdstat`.

Once this is complete, you can activate the swap with the following commands:

```
swapoff -a
mdadm --readwrite /dev/mdX
```

Where X = the RAID index.

Now a resync of the swap RAID takes place. Subsequently, the system should be rebooted.

If RealVNC or UltraVNC are used as VNC clients, then under certain circumstances disconnections can happen. In these cases try the following configurations:

* Hextile Encoding
* Color Level Low (256 colors) 

## Specifics: Fedora

During the installation a firewall is activated automatically. By default, only a small handful of ports are approved - including SSH. Instead of iptables [firewalld](https://fedoraproject.org/wiki/FirewallD) is now the default firewall. Settings on the firewall can be changed using the following program:

`firewall-cmd`

Fedora 25 can not be installed on the Virtual server CX10 since more than 1GB RAM is required for the installation. 

## Conclusion

By now you should have installed a VNC software to remote control your server.
