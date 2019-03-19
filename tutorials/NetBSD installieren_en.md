# Installing NetBSD
## Introduction
NetBSD is a unix based OS.

## Requirements

When installing NetBSD we encounter a problem:
* The [Rescue-System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en) is linux based. BSD-Rescue on the other hand is based on FreeBSD.

* in linux one can mount the root-partiton this way:

  `mount -r -t ufs -o ufstype=44bsd /dev/sda1 /mnt/netbsd`

Additionally you need the following:

* a local UN*X System
* qemu 


## Installation
The first step is to create a blank image, for example with a size of 2GB:

`dd if=/dev/zero of=install.img bs=2048 count=1048576`

Now we start qemu. The image is the hdd, the NetBSD install-iso is CDROM, afterwards  boot from CDROM.

Install NetBSD on the image

* change nothing in the MBR ("use whole disk")! 
* one / Partition, 1024 MB, one Swap Partition 1024 MB
* /usr tmp etc not as separate partitions (see below)

Quit qemu, restart it and boot from the new image. Log in and only change the bare minimum:

* Create a non root user and add him to wheel (for su). Otherwise we could not log in via ssh(we do not want root-login right now!)

* In /etc/rc.conf hinzufügen: 

```
sshd=YES
dhclinet=YES
```
Hetzner's DHCP server is working as expected. The rest is being configured bei NetBSD automatically.

* create ssh keys : `/etc/rc.d/sshd start` (may take a while) 

Note:

* Disable unused services (sendmail...)
* quit qemu 
* pack the image (gzip -9) to image.gz 

Install the Image:

Option 1:

* upload the image to a fast and secure webspace => http://host/image.gz
* boot into the Resuce-System
* install the image (hdd being /dev/hda, otherwise change accordingly)

`wget -O - 'http://host/image.gz' | gzip -c -d > /dev/hda`

Option 2:

* Boot into the Resuce System
* Install local image via SSH: 
`ssh root@server 'gzip -c -d > /dev/hda' < image.gz`

Note:
Instead of /dev/hda cat one can use dd:

`dd of=/dev/hda bs=2048 count=1048576 conv=notrunc`

Example for the image being on `http://someotherhost/install.img.gz`:

```
wget -O - 'http://someotherhost/install.img.gz' | gzip -d -c
| dd of=/dev/sda bs=2048 count=1048576 conv=notrun
```

Reboot the server and log in.


## Additional Steps
Installation would be complete now, but if you encounter problems you could follow these steps:
Change HDD size:

`fdisk -u /dev/wd0`

Only change partition 0, then check:

`fdisk /dev/wd0`

Under `NetBSD disklabel disk geometry:`
Take a note of `cylinder` and `total sectors`.

__Attention:__ reboot now, just to be safe.

`disklabel -i wd0`

Change the geometry with `I`: edit  `cylinder` and `total sectors`accordingly to your notes. 

Note:
* Partition c =>full width
* Partition d => full width
* Partition e,f,g... => as you wish
* Use 'C' to make partition table contagious
* Write with 'W'  

__Attention:__ reboot now, just to be safe.

Format the new partitions with `newfs`, define mount-points, etc. You can copy `/usr`, `/home` etc. to the new partition, if you want.

Reboot one last time and you can start configuring.

## Weblinks

http://www.bsdforen.de/showthread.php?t=14574
http://www.daemonology.net/depenguinator/ 

## Conclusion
By now you should have NetBSD up and running on your server.