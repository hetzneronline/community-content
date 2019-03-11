# Windows Server Hyper V
## Introduction
The following describes how additional single IPs can be used in conjunction with Hyper-V for virtual machines.

With Hyper-V this can only be done by using virtual MAC addresses, which can be applied for via Robot, for each additional single IP. 

## Roles and Features

The required roles and features are: 

* `Hyper-V` and `Management` 

These can be installed via the Server Manager using the `Add Roles and Features`.

During the installation of Hyper-V a virtual switch with the physical network card needs to be created: 

![alt text](https://wiki.hetzner.de/images/thumb/0/05/W2012r2_hyper-v.png/800px-W2012r2_hyper-v.png "Logo Title Text 1")

### Creating a virtual switch

NOTE: This step is only necessary if during the installation of Hyper-V a vSwitch was not created.

Open the Hyper-V manager and within the manager for virtual switches add an external virtual switch and select the option `Allow sharing this network adapter with the management operating system`. 

![alt text](https://wiki.hetzner.de/images/thumb/2/25/W2012r3_single-vswitch.png/800px-W2012r3_single-vswitch.png "Logo Title Text 1")

## Hyper-V

Create a new `Generation 1` virtual machine

![alt text](https://wiki.hetzner.de/images/thumb/f/f0/W2012r2_hyperv-gen1.png/800px-W2012r2_hyperv-gen1.png "Logo Title Text 1")

Via `Settings`remove the automatically added network card.
Via `Add Hardware` create a new legacy network card and connect it with the internal virtual switch: 

![alt text](https://wiki.hetzner.de/images/thumb/e/e4/W2012r2_hyperv-addnic.png/800px-W2012r2_hyperv-addnic.png "Logo Title Text 1")


Under `Advanced Features` statically enter the virtual MAC address, which can be gotten from the [Robot](https://wiki.hetzner.de/index.php/Robot)

![alt text](https://wiki.hetzner.de/images/thumb/5/53/W2012r2_hyperv-mac.png/800px-W2012r2_hyperv-mac.png "Logo Title Text 1")


Start the virtual machine and test PXE boot.
When properly configured the Hetzner PXE boot menu (blue logo) appears:

![alt text](https://wiki.hetzner.de/images/thumb/8/8b/Pxe_boot.jpg/789px-Pxe_boot.jpg "Logo Title Text 1")

## Conclusion

