---
SPDX-License-Identifier: MIT
path: "/tutorials/deploy-img-hetzner-rescue/en"
slug: "deploy-img-hetzner-rescue"
date: "2025-01-29"
title: "Deploying a `.img` File on a Cloud Server via Rescue System"
short_description: "This tutorial explains how to successfully deploy a `.img` file on a cloud server using the rescue system."
tags: ["Cloud", "Rescue System", "Linux"]
author: "tim.stich"
author_link: "https://github.com/T-stich"
author_img: "https://avatars.githubusercontent.com/u/83845082?v=4&s=50"
language: "en"
available_languages: ["en", "de"]
header_img: "header-deploy-img"
cta: "product"
---

## **Introduction**

This tutorial explains how to successfully deploy an `.img` file on a cloud server using the rescue system.  
This process is required when installing a custom operating system image on the server.

The following topics will be covered:

- Activating the rescue system and establishing an SSH connection
- Mounting a **volume** to store the `.img` file
- Uploading or downloading the `.img` file
- Deploying the image using `pv | dd` for progress monitoring or `dd` as the default method
- Restarting the server and verifying the boot process

### **Requirements**

- A cloud server
- Activated rescue system
- SSH access to the server
- A volume to temporarily store the `.img` file (as the primary disk will be overwritten)
- An operating system `.img` file (or `.qcow2`, see step 3)
- Basic Linux command-line skills

---

## **Step 1 - Activating and Connecting to the Rescue System**

### **Activating the Rescue System**

1. Log in to the **cloud provider's management console**.
2. Select the target server and activate the **rescue system**.
3. If required, add an **SSH key** for easier authentication.
4. Reboot the server to enter rescue mode.

### **Connecting to the Server**

Make an SSH connection to the rescue system:

```bash
ssh root@<your_server_ip>
```

After logging in, the rescue system banner will appear.

---

## **Step 2 - Identifying the Target Disk and Mounting the Volume**

Since the `.img` file cannot be stored directly on the primary disk, a **volume** is required as a storage location.  
To list all available volumes, use the following command:

```bash
lsblk
```

Example output:

```
NAME    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINTS
sda       8:0    0  76G  0 disk 
sdb       8:16   0  40G  0 disk 
```

Here, `sda` is the **primary disk** to be overwritten, while `sdb` is the **volume** to store the `.img` file.

### **Manually Mounting the Volume**

In the **rescue system**, additional **volumes are not mounted automatically**.  
Now create a **mount directory** and mount the volume:

```bash
mkdir -p /mnt/vol1
mount /dev/sdX /mnt/vol1
```

*Replace `/dev/sdX` with the actual volume name (e.g., `/dev/sdb`).*

If you are unsure which volume to use, check the partition table:

```bash
fdisk -l
```

---

## **Step 3 - Preparing the Image File**

### **Method 1: Upload via SCP (Command Line)**

If the `.img` file is stored locally, it can be uploaded using **SCP**:

```bash
scp /path/to/image.img root@<your_server_ip>:/mnt/vol1/
```

### **Method 2: Upload via SFTP (FileZilla)**

For users who prefer a graphical interface, **FileZilla** can be used:

1. Open **FileZilla**.
2. **Create a new connection:**
   - **Server**: `<your_server_ip>`
   - **Username**: `root`
   - **Password**: `<your_password>`
   - **Port**: `22`
   - **Connection Type**: **SFTP - SSH File Transfer Protocol**
3. Drag and drop the `.img` file into the `/mnt/vol1/` directory.

### **Method 3: Download using `wget`**

If the `.img` file is hosted externally, it can be downloaded directly to the server:

```bash
wget <URL_to_image.img> -O /mnt/vol1/image.img
```

### **If the Image is in `.qcow2` Format**

If the uploaded or downloaded image is in `.qcow2` format, it needs to be converted:

```bash
qemu-img convert -f qcow2 -O raw /mnt/vol1/source.qcow2 /mnt/vol1/destination.img
```

---

## **Step 4 - Writing the Image to the Target Disk**

### **Recommended Method: `pv | dd`**
Now, `pv | dd` is used for better visibility of progress:

```bash
pv /mnt/vol1/image.img | dd of=/dev/sda bs=4M status=progress
```

If `pv` is not installed, it can be added with:

```bash
apt update && apt install -y pv
```

### **Alternative: `dd` Directly**
If `pv` is not used, the image can be written directly with `dd`:

```bash
dd if=/mnt/vol1/image.img of=/dev/sda bs=4M status=progress
```

---

## **Step 5 - Reboot and Check the System**

Once the image has been successfully written, the server can be rebooted.

Use the following command to reboot into the installed operating system:

```bash
reboot
```

After the reboot, verify that the system has booted successfully and is accessible.

### **Troubleshooting Boot Problems**  
If the system does not boot correctly, consult the operating system documentation.  
In some cases, a bootloader check may be required.

For general bootloader configuration, see:
- **GNU GRUB - Official Documentation:**  
  [https://www.gnu.org/software/grub/manual/grub/grub.html](https://www.gnu.org/software/grub/manual/grub/grub.html)

- **UEFI Specification and Documentation:**  
  [https://uefi.org/specifications](https://uefi.org/specifications)

---

## **Note on Alternative Image Formats**  
Some alternative image formats can also be converted and used.  
However, there is a risk that they may not work properly due to driver or hardware compatibility issues.  

**Cloud servers are based on KVM virtualization**, so ensure that the operating system you choose is KVM-compatible.
