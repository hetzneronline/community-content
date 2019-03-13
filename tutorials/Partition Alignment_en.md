# Partition Alignment
## Introduction

By Partition Alignment we mean the proper alignment of partitions to the physical sector borders of a data storage device (eg. hard drive, SSD, RAID Volume). Correct partition alignment ensures optimal performance for accessing data. Faulty alignment of partitions can lead to reduced performance, especially for SSDs (with internal page sizes of 4,096 or 8,192 bytes for example), hard drives with 4 KB sectors (4,096 bytes) and RAID Volumes.

When operating systems are installed via the [Robot](https://robot.your-server.de/) or via the [installimage](https://wiki.hetzner.de/index.php/Installimage/en) in the [Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en), correct alignment is automatically carried out.

For unassisted installation via Remote Console ([KVM Console](https://wiki.hetzner.de/index.php/KVM-Console/en)) or via [VNC](https://wiki.hetzner.de/index.php/VNC-Installationen/en) (eg. CentOS 6.X) the alignment needs to be configured manually. As a rough guide, the more recent the operating system to be installed is, the higher the probability that the partitions will be correctly aligned by the installation program. Alignment can be checked using the following command:

```
sfdisk -uS -l
fdisk -u -l /dev/sdX
```

The boot sector for each partition should be divisible by at least 8 (8 * 512 bytes = 4 KB), and ideally by 2048 (2048 * 512 bytes = 1 MB).

## Background: Partitioning
Typical drives work with a physical sector size of 512 bytes. The first partition begins in the last sector of the first track with the (logical) block address 63. The size of such a (logical) sector also amounts to 512 bytes. As logical and physical sectors are of an equal size, there are no problems. Newer drives in [Advanced Format](http://en.wikipedia.org/wiki/Advanced_Format) work with a physical sector size of 4,096 bytes (4 KB). However, outwardly they emulate a sector size of 512 bytes (Advanced Format 512e). SSDs also work with a page size of 4 KB or 8 KB. The use of "classical" partitioning which starts at LBA address 63 is no longer recommended for these newer drives or SSDs.

Current file systems, such as ext4 or ntfs for example, use a 4 KB block size. The file system´s 4 KB blocks do not fit directly into the 4 KB sectors of the hard drive or 4 KB/8 KB SSD pages. When writing one single 4 KB file system block, two 4 KB sectors or pages need to be changed. This is further complicated by the need to retain the corresponding 512 byte blocks - which leads to a [Read/Modify/Write](http://en.wikipedia.org/wiki/Read-modify-write). This results in a significant reduction in performance.

![alt text](https://wiki.hetzner.de/images/thumb/d/df/Part_alignment_wrong.png/800px-Part_alignment_wrong.png "Logo Title Text 1")

Correct Partitioning
To avoid this problem, the recommended alignment is 1 MB - to be on the safe side in the long term. The current addressing in 512 byte-sized logical sectors amounts to 2,048 sectors.

![alt text](https://wiki.hetzner.de/images/thumb/c/cf/Part_alignment_right.png/600px-Part_alignment_right.png "Logo Title Text 1")


### Linux

#### fdisk (older versions)
For older fdisk versions, alignment may be achieved manually using the -S and -H parameters. There are various options for specific Number of Sectors per Track (S) and Number of Head (H) figures. With -S 32 -H 64 the partitions are aligned to 1 MB (32 Sectors per Track * 64 Heads * 512 bytes = 1,048,576 bytes = 1 MB). When setting up the first partition, you start with cylinder two. Not using special parameters leads to misalignment with fdisk.

#### fdisk in the Hetzner Rescue System
The fdisk version contained in the [Hetzner Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en) uses a 1 MB alignment provided that DOS Compatibility Mode is deactivated.

For newer fdisk versions it is recommended you:

* use the fdisk from util-linux-ng >= 2.17.2
* pay attention to the fdisk warnings
* deactivate DOS Compatibility Mode (-c Option)
* use the sectors as display units (-u Option)
* use +size{M,G} to show the end of a partition

#### Example of faulty alignment
The following example shows faulty alignment following a VNC Installation of CentOS 5.6:

```
[root@static ~]# fdisk -v
fdisk (util-linux 2.13-pre7)
[root@static ~]# fdisk -u /dev/hda

The number of cylinders for this disk is set to 10443.
There is nothing wrong with that, but this is larger than 1024,
and could in certain setups cause problems with:
1) software that runs at boot time (e.g., old versions of LILO)
2) booting and partitioning software from other OSs
   (e.g., DOS FDISK, OS/2 FDISK)

Command (m for help): p

Disk /dev/hda: 85.8 GB, 85899345920 bytes
255 heads, 63 sectors/track, 10443 cylinders, total 167772160 sectors
Units = sectors of 1 * 512 = 512 bytes

   Device Boot      Start         End      Blocks   Id  System
/dev/hda1   *          63      208844      104391   83  Linux
/dev/hda2          208845   167766794    83778975   8e  Linux LVM
```

#### Example of correct alignment
```
root@rescue ~ # fdisk -c -u /dev/sda

Command (m for help): p

Disk /dev/sda: 1500.3 GB, 1500301910016 bytes
255 heads, 63 sectors/track, 182401 cylinders, total 2930277168 sectors
Units = sectors of 1 * 512 = 512 bytes
Sector size (logical/physical): 512 bytes / 4096 bytes
I/O size (minimum/optimal): 4096 bytes / 4096 bytes
Disk identifier: 0x0004dc67

   Device Boot      Start         End      Blocks   Id  System
/dev/sda1            2048     4196351     2097152   fd  Linux raid autodetect
/dev/sda2         4196352     5244927      524288   fd  Linux raid autodetect
/dev/sda3         5244928  1465149167   729952120   fd  Linux raid autodetect
```

#### LVM and Software RAID
Although one needs to pay attention to correct alignment here as well, this is not explicitly necessary as at least 64 KB-sized blocks are used. Newer versions include patches which perform an additional alignment at 1 MB boundaries.

### Windows

All Windows versions starting with Windows Vista automatically perform an alignment at 1 MB boundaries on all data storage devices larger than 4 GB. Older versions such as Windows XP require manual alignment.

## Conclusion

By now you should have checked, wether your partition alignment is correct for your manual OS installation.