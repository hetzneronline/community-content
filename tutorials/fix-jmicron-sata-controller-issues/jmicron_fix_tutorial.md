---
SPDX-License-Identifier: MIT
path: "/tutorials/fix-jmicron-sata-controller-issues"
slug: "fix-jmicron-sata-controller-issues"
date: "2026-06-21"
title: "Fixing JMicron SATA Controller I/O Errors on Hetzner SX135 Server with 22TB HDDs"
short_description: "Resolve I/O errors and instability on Proxmox Backup Server caused by JMicron SATA controllers with large disks on Hetzner SX135."
tags: ["Linux", "Storage", "Proxmox", "SX135", "Hardware", "Hetzner", "JMicron"]
author: "Yaroslav"
author_description: ""
language: "en"
available_languages: ["en"]
header_img: "header-3"

---

## Introduction

Hetzner offers the SX server series, which are equipped with 4 to 12 hard drives, each with a capacity of 16 or 22 TB. The SX135 server comes with 8 drives, each with a capacity of 22 TB, making it a good choice for backup storage. Unfortunately, after ordering this server and installing Proxmox Backup Server 4.2, I was only able to use 4 drives that were connected directly to the motherboard. The other 4 drives, connected to JMicron SATA controllers, returned errors even when tested using “hdparm”. Since Hetzner support replaced the controller and cables, hardware errors were ruled out. When testing the disks in the rescue system, all disks operated stably and without errors. Therefore, a comparison of the disk subsystem parameters between the Proxmox kernel and Debian (rescue system) was performed, and discrepancies were identified. The symptoms of the problem and how to resolve it are described step-by-step below.

The issue was observed on a **Hetzner SX135 server** with:

- 8 × 22 TB HDDs  
- Proxmox Backup Server 4.2

Disk layout:

- 4 disks connected directly to the motherboard SATA controller → stable  
- 4 disks connected via JMicron controller → I/O errors  

---

## Problem Description

### Symptoms

Under load, the system shows:

- Kernel errors:
  ```
  failed command: READ DMA EXT
  WRITE FPDMA QUEUED failed
  Emask 0x40 (internal error)
  ```
- Buffer I/O errors
- Unstable writes under load
- Errors only on disks connected via JMicron

---

### Example Test

```bash
hdparm -Tt /dev/sde
```
Result:

```
journalctl -f

 pbs kernel: ata7.00: status: { DRDY }
 pbs kernel: ata7.00: supports DRM functions and may not be fully accessible
 pbs kernel: ata7.00: supports DRM functions and may not be fully accessible
 pbs kernel: ata7.00: configured for UDMA/133
 pbs kernel: ata7: EH complete
 pbs kernel: ata7.00: exception Emask 0x0 SAct 0x0 SErr 0x0 action 0x0
 pbs kernel: ata7.00: failed command: READ DMA EXT
 pbs kernel: ata7.00: cmd 25/00:40:00:08:00/00:05:00:00:00/e0 tag 21 dma 688128 in
                                     res 50/00:00:30:03:00/00:00:00:00:00/a0 Emask 0x40 (internal error)
 pbs kernel: ata7.00: status: { DRDY }
 pbs kernel: ata7.00: supports DRM functions and may not be fully accessible
 pbs kernel: ata7.00: supports DRM functions and may not be fully accessible
 pbs kernel: ata7.00: configured for UDMA/133
 pbs kernel: sd 6:0:0:0: [sde] tag#21 FAILED Result: hostbyte=DID_OK driverbyte=DRIVER_OK cmd_age=0s
 pbs kernel: sd 6:0:0:0: [sde] tag#21 Sense Key : Aborted Command [current]
 pbs kernel: sd 6:0:0:0: [sde] tag#21 Add. Sense: No additional sense information
 pbs kernel: sd 6:0:0:0: [sde] tag#21 CDB: Read(16) 88 00 00 00 00 00 00 00 08 00 00 00 05 40 00 00
 pbs kernel: I/O error, dev sde, sector 2048 op 0x0:(READ) flags 0x4000 phys_seg 168 prio class 2
```

---

## JMicron SATA controllers

JMicron SATA controllers are described positively only on the manufacturer's website :( . On forums, users often report problems with them and almost always recommend replacing them with a different model. However, this option is not available, so after analyzing the technical specifications, it was found that JMicron SATA controllers:

- does not support 64-bit DMA  
- uses 32-bit DMA only  
- relies on additional memory translation  

---

### Default settings  by Proxmox kernel

Differences were also found in the following Linux kernel parameters

- large I/O sizes (`/sys/block/sde/queue/max_sectors_kb:4096`)  
- large read-ahead (`/sys/block/sde/queue/read_ahead_kb:8192`)  

---

### Command queueing (NCQ)

During testing, was also observed a negative impact of NCQ (`/sys/block/sde/device/queue_depth:32`) on speed and stability, especially during concurrent write operations.

---

## Solution Overview

To ensure stable operation of drives connected to a JMicron SATA controller, the following changes must be made:

- simplifying DMA handling  
- reducing I/O size  
- disabling NCQ  

---

## Step 1 – Disable IOMMU

Edit GRUB:

```bash
nano /etc/default/grub
```

Set:

```bash
GRUB_CMDLINE_LINUX_DEFAULT="quiet intel_iommu=off"
```

Apply:

```bash
proxmox-boot-tool refresh
```

---

## Step 2 – Reduce I/O Size

Create a udev rule:

```bash
nano /etc/udev/rules.d/60-jmicron-fix.rules
```

Add:

```
ACTION=="add|change", SUBSYSTEM=="block", KERNEL=="sd[e-h]", ATTR{queue/max_sectors_kb}="1280"
ACTION=="add|change", SUBSYSTEM=="block", KERNEL=="sd[e-h]", ATTR{queue/read_ahead_kb}="128"
```

---

## Step 3 – Disable NCQ

Add to the same file:

```
ACTION=="add|change", SUBSYSTEM=="block", KERNEL=="sd[e-h]", ATTR{device/queue_depth}="1"
```

Reload rules:

```bash
udevadm control --reload
udevadm trigger
reboot
```

---

## Verification

Check applied values:

```bash
cat /sys/block/sde/device/queue_depth
cat /sys/block/sde/queue/max_sectors_kb
cat /sys/block/sde/queue/read_ahead_kb
```

Result:

```
queue_depth = 1
max_sectors_kb = 1280
read_ahead_kb = 128
```

---

## Testing

### Hdparm test

```bash
hdparm -Tt /dev/sde
```

Result:

```
/dev/sde:
 Timing cached reads:   23658 MB in  2.00 seconds = 11858.28 MB/sec
 Timing buffered disk reads: 852 MB in  3.01 seconds = 283.50 MB/sec
```

---

## Results

After applying this configuration:

-  no kernel I/O errors  
-  stable operation under load  
-  sequential performance around 250 MB/s  
-  random write performance around 100 MB/s  

---

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

Signed-off-by: Yaroslav yaroslav.solovyov@gmail.com

-->
