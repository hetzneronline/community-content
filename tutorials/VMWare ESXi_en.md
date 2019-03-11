# VMWare ESXi
## Introduction
### Hardware

* VMware vSphere Hypervisor 5.x and 6.0 (ESXi) use a filesystem called vmfs5 (formerly vmfs3), which uses GPT and supports drive sizes of up to 64 TiB (formerly 2 TiB).
* Software RAID is *not* supported by ESXi.
* The free version of ESXi starting with 5.5 supports a maximum of 4 TB RAM (formerly 32 GB RAM). 

### Network configuration

* VMware vSphere Hypervisor is an "operating system" designed for pure virtualization and supports neither NAT nor routing. Therefore, only a true bridged setup can be used.
* To use a subnet an additional single IP needs to be setup as a router VM. 

### Installation

The installation and configuration of ESXi takes about 20-30 minutes, even without any prior knowledge. Please check out the Installation Guide for further information on installing ESXi. 

### License

After installation the system has a test license, which expires after 60 days. For the long-term usage of the OS a separate (free) license is required. This can be requested by registering on the VMware website:
* [License for ESXi 5.x](https://my.vmware.com/web/vmware/evalcenter?p=free-esxi5)
* [License for ESXi 6.0](https://my.vmware.com/web/vmware/evalcenter?p=free-esxi6) 
The license can be entered via the vSphere Client, under the tab `Configuration`, the section `Software` and then under the menu item `Licensed functions`. After selecting this you can click on the `Edit` button to the right and then enter the license key. 

![alt text](https://wiki.hetzner.de/images/2/2a/Lizenzzuweisung_mit_vSphereClient.jpg "Logo Title Text 1")

## Hetzner specific information
### Installed hardware

The Dell PowerEdge models DX150, DX151, DX290, DX291  and DX141 are certified and compatible with all versions from 5.0 onwards. DX152 and DX292 are compatible from 6.5 onwards.

All other models are not certified by VMware, yet in most cases they can be run with VMware vSphere/ESXi.

#### Compatibility

(All information is subject to change)

```
Model 	vSphere/ESXi Version
AX50-SSD/AX60-SSD 	from 6.5a additional Intel NIC required
AX160 	from 6.5a
PX92 	from 6.5
PX91/PX121 	from 5.5 Update 1
PX90/PX120 	from 5.1
PX61 	from 5.5 Update 3 / 6.0 Update 2
PX61-NVMe 	from 6.0 Update 2
PX60/PX70 	from 5.5 Update 1
EX52/EX61/EX62
	from 6.5
EX41/EX41S/EX51/EX42
	from 5.5 Update 3 / 6.0 Update 2
(potentially an additional NIC required)
EX40/EX60 	5.0 - 5.1 Update 2, since 5.5 additional NIC required
EX6/EX6S/EX8/EX8S 	from 5.0
EX4/EX4S/EX10 	from 5.0 (with additional NIC)
EQ4/EQ6/EQ8/EQ9 	5.0 - 5.1 Update 2, since 5.5 additional NIC required
```

NOTES:

* The models mentioned above should work with the appropriate version. However, newer versions are not necessarily compatible. To make sure please gather your own information. 

* The Realtek driver present in ESXi 5.0 to 5.1 Update 2 was just a Tech Demo that has not been updated. Therefore, for smooth operation a server with an Intel NIC (PX60, PX91, DX151 etc.) is recommended. 

* The installation of vSphere 5.5 or newer on the EX40, EX60 and some EX41/EX51 models requires a custom installation ISO that includes drivers for the [Realtek NIC](http://www.bussink.ch/?p=1228). Alternatively, an additional compatible NIC can be installed. Please find the cost of an additional compatible NIC here: [Root Server Hardware](https://wiki.hetzner.de/index.php/Root_Server_Hardware/en#Miscellaneous) 

* Installing ESXi on the older DS or X servers is not possible. 

* When installing on a server with multiple identical drives, ESXi can potentially show the drives in a different order than the BIOS does. Should the screen be blank with only a blinking cursor after the installation is done and you restart, try booting from the other drive. 

### Network Configuration

For connectivity between multiple ESXi servers within the same subnet, host routes via gateway are necessary due to network security restrictions. 

```
host A
esxcfg-route -a <IP Host B> 255.255.255.255 <Gateway IP>
```

```
host B
esxcfg-route -a <IP Host A> 255.255.255.255 <Gateway IP>
```

### Single IP addresses

IP addresses are, by default, statically mapped to the MAC address of the host. It is possible however to get separate MAC addresses for the additional single IPs via Robot. These can then be configured for the virtual machines. To get these MAC addresses assigned, log in to Robot, choose `Server` from the menu on the left, select the desired server, and then click on the ´IPs` tab. Here you can click on the icon next to the single IP address to get a MAC address. 

![alt text](https://wiki.hetzner.de/images/b/ba/Esxi-mac-setzen.png "Logo Title Text 1")

### Subnets

To use a subnet (IPv4 as well as IPv6) in ESXi, at least one additional single IP is required as a router VM, since ESXi itself cannot route. When ordering a subnet, please make sure to note that it is required for ESXi and should be routed on the additional single IP. 

IMPORTANT Since IPv6 subnets are routed to link-local addresses (MAC-based), it is only possible to use IPv6 in a limited way (ie. in a single VM).
#### IPv4

The confirmation email of the subnet contains (for example) the following information:

```
Below you will find the IP subnet added to your server 192.168.13.156.

Subnet: 192.168.182.16 /28
Mask: 255.255.255.240
Broadcast: 192.168.182.31

Usable IP addresses:
192.168.182.17 to 192.168.182.30
```


You do NOT get a separate MAC for each IP from a subnet.

#### IPv6

All servers come with a /64 IPv6 subnet. To see which IPv6 subnet your server has please check the `IPs` tab of the server in Robot.

If you ordered your server before February 2013 this subnet can be ordered (freely) via Robot and will be automatically activated.

The IPv6 subnet is routed to the default link-local address (which is derived from the MAC address) of the main IP. Via Robot the routing of the IPv6 subnet can be switched to the link-local address of the virtual MAC (in other words, the additional single IP). This can be done in Robot, using the same symbol which is found next to additional single IPs to request virtual MAC addresses. The host system, so the ESXi itself, receives no IPv6 address. This is neither necessary nor possible because ESXi can not work with a fe80::1 gateway.

In order to use these IP addresses in virtual machines, a "router VM" supplemented by an additional virtual NIC from the new subnet is necessary. The subnet itself requires a new vSwitch in ESXi to which all VMs in the subnet will be connected.
#### Notes

The network card type for the router VM should not be VMXNET2 or VMXNET3, as otherwise the TCP performance can be very bad. As a workaround LRO in the VM can be disabled via `disable_lro=1`. More information on this bug can be found [here](http://www.vmware.com/support/vsphere4/doc/vsp_esxi41_vc41_rel_notes.html).

After an upgrade to VMware ESXi 5 this issue may appear again. You can address this issue by disabling Large Receive Offload (LRO) on the ESXi host:
* Log into the ESXi host with the vSphere Client.
* Select the host -> Configuration -> Software:Advanced Settings.
* Select Net and scroll down slightly more than half way.
* Set the following parameters from 1 to 0: 

```
Net.VmxnetSwLROSL
Net.Vmxnet3SwLRO
Net.Vmxnet3HwLRO
Net.Vmxnet2SwLRO
Net.Vmxnet2HwLRO
```

Reboot the ESXi host to activate these changes.

If you experience connection problems in systems with Realtek network cards then under certain circumstances deactivating offloading and activating polling can solve this. However, this also reduces the performance.

* checksum offload: deactivated
* segmentation offload: deactivated
* large receive offload: deactivated
* device polling: enabled 

#### Preparations in the vSphere client

Create a vSwitch (in the example the name `subnetz` is used)

![alt text](https://wiki.hetzner.de/images/3/30/Esxi-vswitch1.png "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/c/ca/Esxi-vswitch2.png "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/0/07/Esxi-vswitch3.png "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/1/10/Esxi-vswitch4.png "Logo Title Text 1")

Add a second NIC to the router VM. Connected network: subnetz (the previously created vSwitch)


![alt text](https://wiki.hetzner.de/images/f/f8/Esxi-router-nic.png "Logo Title Text 1")


The NIC of the virtual machine in the subnet. Connected network: subnetz

The networking overview should show the following:

![alt text](https://wiki.hetzner.de/images/8/87/Esxi-subnet.png "Logo Title Text 1")


#### Configuration of the Router VM

Example of `/etc/network/interfaces` on the router VM 

```
# The loopback network interface
auto lo
iface lo inet loopback
# The primary network interface
# WAN-NIC im VMnetwork
auto eth0
iface eth0 inet dhcp
# for the IPv6 subnet the configuration is analog to other virtualisations
iface eth0 inet6 static
 address 2a01:4f8:61:20e1::2
 netmask 128
 gateway fe80::1
# LAN NIC in Subnet
auto eth1
iface eth1 inet static
 address     192.168.182.30
 netmask     255.255.255.240
# The prefix/netmask can/must be changed according to the amount of network
# segments
iface eth1 inet6 static
 address    2a01:4f8:61:20e1::2
 netmask    64
```

Example of /etc/network/interfaces of a Linux VM in the subnet 

```
# The loopback network interface
auto lo
iface lo inet loopback
# The primary network interface
auto eth0
iface eth0 inet static
 address 192.168.182.17
 netmask 255.255.255.240
 gateway 192.168.182.30
iface eth0 inet6 static
 address    2a01:4f8:61:20e1::4
 netmask    64
 gateway    2a01:4f8:61:20e1::2
```

The router VM is now connected to both networks and can be used as a gateway for virtual machines in the subnet. Don't forget to activate IP forwarding in the kernel:

```
echo 1 > /proc/sys/net/ipv4/ip_forward
echo 1 > /proc/sys/net/ipv6/conf/all/forwarding
```

To make this persistent across reboots, add the following line to `/etc/sysctl.conf`

```
net.ipv4.ip_forward=1
net.ipv6.conf.all.forwarding=1
```

The virtual machines should now be accessible (via SSH for example) via their assigned IPs.


##Installation Guide

Choose the [Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System) as the OS for the server you order. 

If you would like RAID you can add a 4-Port RAID controller since ESXi doesn't support software RAID. 

Once the server is online (you will get an email from us informing you of this) you can order a KVM Console and use that to virtually mount an ISO file of the version of ESXi you want to install. Further information on ordering a KVM Console and using it to install an OS can be found on the KVM Console page. 

Afterwards, the following screen should be displayed:
![alt text](https://wiki.hetzner.de/images/thumb/7/7e/Esxi-installed.png/794px-Esxi-installed.png "Logo Title Text 1")


After a reboot login using the password that you entered during the installation. This is the root password for SSH as well as the password for the VMware vSphere Client (requires Windows). This can then be downloaded separately via a browser. 

![alt text](https://wiki.hetzner.de/images/9/9b/Esxi-vsphere.png "Logo Title Text 1")

After successful installation you can order up to three additional single IP addresses via Robot. You can get a MAC address for your additional single IP through Robot under the `IPs` tab. You will see a small button next to the IP address. Clicking on that button will give the IP address a virtual MAC.
Configure the MAC addresses of the virtual servers with the corresponding IP addresses through vSphere. Once this is done even DHCP will work via the Hetzner network! 

For additional information regarding ESXi and its usage, please refer to the [official Webseite](http://www.vmware.com/products/esxi/).

### Manual installation of updates

The installation of updates in the free version can only be done via the console or via VMware Go. An update can be several hundred megabytes, which can take a long time with a standard DSL connection, which is why the following guide can help. This is done at your own risk. There is no warranty or guarantee for correctness!

Prerequisite is SSH activated access and that the system is in maintenance mode. This can be activated via:

`vim-cmd hostsvc/maintenance_mode_enter`

#### Updating vSphere 5.0 to 5.1

First of all the VMware-ESXi-5.1.0-799733-depot.zip update needs to be downloaded from the VMWare Updates Page, or, used at your own risk and without warranty or liability, from the Hetzner Download Page and saved on the ESXi Host.

Once all the VMs have been shut down and the system has entered the maintenance mode via `vim-cmd hostsvc/maintenance_mode_enter`, the update can be installed in two ways. The following command refreshes the system and removes all the packages that are not included in the update. This is equivalent to a new installation.

`esxcli software profile install -d /vmfs/volumes/datastore1/VMware-ESXi-5.1.0-799733-depot.zip -p ESXi-5.1.0-799733-standard`

Alternatively, only the packages containted in the update can be updated to their new versions, leaving all other packages intact.

`esxcli software profile update -d /vmfs/volumes/datastore1/VMware-ESXi-5.1.0-799733-depot.zip -p ESXi-5.1.0-799733-standard`

Finally, the system must be rebooted. When the VMs are turned on for the first time after the reboot, it is possible that a message appears noting that the VM was copied or moved. This happens because the UUIDs get changed during the update. You can safely select "VM was moved" in this situation. See also: [VMware Help Page](https://kb.vmware.com/s/article/1010675)


#### Installation of Patches

After the patches have been transferred onto the system, they can be installed. It is important that the full path name is entered, eg.:

```
esxcli software vib install --depot="/vmfs/volumes/datastore1/patches/ESXi510-201210001.zip"
Installation Result
Message: The update completed successfully, but the system needs to be rebooted for the changes to be effective.
Reboot Required: true
[...]
```

After a reboot the maintenance mode needs to be exited:

`vim-cmd hostsvc/maintenance_mode_exit`

## Monitoring RAID controller

### 3ware Controller

For the 3ware controller there exists both a CIM Provider as well as a CLI. The 64-bit CLI for Linux can be used from version 9.5.2 onwards.

Note: 3ware controllers are only supported by ESXi 5.0 via an external driver.

### Adaptec Controller

For Adaptec controllers the CIM Provider and the CLI (arcconf) must be manually installed. Required is an up-to-date version of the driver. An installation guide can be found on the [Adaptec Website](http://download.adaptec.com/pdfs/installation_guides/vmware_esxi_41_cim_remotearcconf_installation_guide_3_2011.pdf)

* RAID driver Version 5.2.1.29800
* Remote Arcconf
* Adaptec CIM Providers 

Monitoring can be achieved through the installation of remote ARCCONF via a Windows/Linux system.

`$ arcconf GETCONFIG 1 AD`

#### LSI Controller

LSI provides a so-called CIM/SMIS provider. After the installation the hardware monitoring page in the vSphere client displays the status of the RAID. An active alarm is however, only possible in the paid version and when running vCenter.

Alternatively, the command line tool MegaCLI can be installed, which is also used to manage the RAID controller. A script can be used to automate the displaying of status information. This script and notifications must be run from another server.

### Parallel operation of onboard controller/hardware RAID

During the installation ESXi only "sees" one type of storage, so either the onboard SATA controller or an additional RAID controller. If drives are connected to both then the hardware controller is prioritized and the drives connected to the onboard controller are invisible. By manually loading the appropriate kernel module these drives can still be used.

`/sbin/vmkload_mod ahci`

To have this module loaded automatically during start, the line above must be added to `/etc/rc.local` and `/sbin/auto-backup.sh`

## Hardware change

### Change MAC address

In the event of a hardware (ex)change, especially the motherboard, it should be noted that the ESXi host retains its original MAC address. This leads to problems as the switch will not automatically forward the correct new main IP to the server, as the MAC address that is being broadcast is incorrect. The MAC address needs to be reset via the ESXi shell. There are several approches to do this, listed by the following [Knowledge Base Article](http://kb.vmware.com/selfservice/microsites/search.do?language=en_US&cmd=displayKC&externalId=1031111). The most elegant solution is when the ESXi host automatically recognizes the new MAC address when changing platforms and uses that. The following command can be used for that:

```
esxcfg-advcfg -s 1 /Net/FollowHardwareMac
```

Either perform this command before the platform change or, if the change has already happened, there are two options:

* Order a KVM Console console and enable the ESXi shell and then press Alt + F1 to switch to the console and enter the command. Afterwards pressing Alt + F2 will bring you back to the GUI.
* Temporarily teach the switch the new MAC address by booting into the Rescue System and then back into the ESXi host. As a result, the ESXi host is now reachable again via the main IP, but only for a limited amount of time. The length of this time depends on how long the switch takes to delete the ARP cache entry for this MAC address. Normally there is enough time to login via SSH and execute the command, assuming SSH access has been enabled. However, even this is configurable, as connecting via the ESXi client would be possible again. 


With either option a restart is required afterwards. This can be initiated via the console:

`reboot`

After a restart the MAC address should be set correctly and this can be verified in the ESXi shell via the following command:

`esxcfg-vmknic -l`

The new MAC address should show up next to the main IP. 

## Conclusion
By now you should have installed and configured ESXi on your server.