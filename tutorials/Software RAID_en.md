# Software RAID
## Introduction

Software RAID is when the interaction of multiple drives is organized completely by software.

RAID Level 1 (mirroring) achieves increased security since even if one drive fails, all the data is still stored on the second drive. RAID Level 0 (striping) leads to double the capacity (with two drives) and increased reading spead compared to RAID 1 - but all data will be lost if even one of the drives fails.

Our [installimage](https://wiki.hetzner.de/index.php/Installimage/en), which is used to manually install an operating system, can be used to configure several raid levels. Also, the software RAID can be combined with LVM.

Servers pre-installed by Hetzner are configured with RAID superblocks (version 1.2) when this is supported by the operating system (so all systems with Grub2 as the boot loader). When installing via VNC it can occur that the installer uses other Metadata versions.

## Email notification when a drive in software RAID fails

Requirement: installed and configured mail server

### Debian/Ubuntu/CentOS

Edit `/etc/mdadm/mdadm.conf` or `/etc/mdadm.conf` (CentOS) and change the following line:

`MAILADDR root`

Here a destination address can be specified directly. Alternatively, all emails sent to root can be forwarded to a specific email address using `/etc/aliases`.

You can also optionally configure the sending email address:

`MAILFROM mdadm@example.com`

For Debian and Ubuntu it is important that you set `AUTOCHECK` in the file `/etc/default/mdadm` to `true`:

```
# grep AUTOCHECK= /etc/default/mdadm
AUTOCHECK=true
```

For CentOS, you must enable the check in the file `/etc/sysconfig/raid-check`:

```
# grep ENABLED /etc/sysconfig/raid-check
ENABLED=yes
```

### openSUSE
Edit `/etc/sysconfig/mdadm` and add the email address that you want the notification sent to next to the variable `MDADM_MAIL`:

`MDADM_MAIL="example@example.com"`

## Removing a software RAID
In order to remove a software RAID you can issue the following commands in the Rescue System:

```
mdadm --remove /dev/md0
mdadm --remove /dev/md1
mdadm --remove /dev/md2
```

```
mdadm --stop /dev/md0
mdadm --stop /dev/md1
mdadm --stop /dev/md2
```

After that, the drive can be formatted normally again (e.g. with ext3):

``
mkfs.ext3 /dev/sda
mkfs.ext3 /dev/sdb
```

The result can be checked with:

`fdisk -l`

The software RAID should be gone.

Then [installimage](https://wiki.hetzner.de/index.php/Installimage/en) can be used in order to install a new operating system.

If an OS is installed and software RAID is activated on the server, then purely running installimage and installing a new OS without software RAID won't work. The server won't boot in this case.

## Additional Information

[Installing operating systems with installimage](https://wiki.hetzner.de/index.php/Installimage/en) incl. software RAID, OS independent

[Replacing a faulty drive in software RAID](https://wiki.hetzner.de/index.php/Festplattenaustausch_im_Software-RAID/en)

## Conclusion
By now you should be able to add and/or remove a software RAID and get notified via mail if there is a issue. 