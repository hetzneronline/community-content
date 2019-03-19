# Additional IP addresses
## Intoduction

All dedicated and virtual servers come with an IPv4 address, as well as a /64 IPv6 subnet.

Additional IPv4 addresses can be ordered via the [Robot](https://wiki.hetzner.de/index.php/Robot/en). See also: [IP Addresses](https://wiki.hetzner.de/index.php/IP-Adressen/en)

Note: This article is limited to showing the corresponding linux commands to illustrate the general concepts.
For systems such as FreeBSD a different [configuration](https://wiki.hetzner.de/index.php/FreeBSD_installieren/en#Network_Configuration) is necessary. 

## Main address

The main IPv4 address of a server is the IP that is originally assigned to the server and is configured in the automatic installations.

For IPv6 there is no clearly defined main address. In automatic installations the ::2 from the assigned subnet is configured.

With dedicated servers and virtual servers from the CX line, the IPv6 subnet is routed on the link-local address of the network adapter. If additional single IPv4 addresses have been ordered with their own MAC addresses, then the IPv6 subnet can be routed onto their link-local address using the [Robot](https://wiki.hetzner.de/index.php/Robot/en).

The particular link-local address is calculated from the MAC address using [RFC 4291](http://tools.ietf.org/html/rfc4291) and is automatically configured:

```
# ip address
2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    link/ether 54:04:a6:f1:7b:28 brd ff:ff:ff:ff:ff:ff
    inet6 fe80::5604:a6ff:fef1:7b28/64 scope link
       valid_lft forever preferred_lft forever
```

With older virtual server models (VQ/VX lines) there is no routing of the /64 IPv6 subnet. This is a local area network, whereby the ::1 of the subnet is used as the gateway (see below).

Following, `<10.0.0.2>` is used as the main IPv4 address. It is not a real IP address.

## Additional addresses

Both individual addresses and addresses from subnets are generally routed via the main IP address. For the rest of this guide we will assume the following additional addresses/networks:

* `<2001:db8:61:20e1::/64>` (IPv6 Subnet)
* `<10.0.0.8>` (Single Address)
* `<203.0.113.40/29>` (IPv4 Subnet) 

The allocated subnets can be further divided, forwarded, or assigned, depending on your own preferences.

With IPv4 the network and broadcast addresses are normally reserved. Based on the above example, that would be the IPs `<203.0.113.40>` and `<203.0.113.47>`. These addresses may be used when you use IPs from subnet as a secondary IP or as part of a point-to-point setup. As a result, in a /29 subnet all 8 IPs are usable, rather than just 6.

With IPv6 the first address (::0) of the subnet is reserved as the "Subnet-Router anycast" address. IPv6 does not use a broadcast, so the last address is also usable (as opposed to with IPv4).

## Gateway

For IPv6 on dedicated servers and virtual servers from the CX line, the gateway is fe80::1. Since this is a link-local address, the explicit specification of the network adapter (usually `eth0`) is necessary:

`# ip route add default via fe80::1 dev eth0`

For older virtual server models (VQ/VX lines) the gateway lies within the assigned subnet:

```
# ip address add 2001:db8:61:20e1::2/64 dev eth0
# ip route add default via 2001:db8:61:20e1::1
```

For IPv4, the gateway is the first usable address of each subnet:

```
# Example: 10.0.0.2/26 => Network address is 192.0.2.64/26
#
# ip address add 10.0.0.2/32 dev eth0
# ip route add 192.0.2.65 dev eth0
# ip route add default via 192.0.2.65
```

## Individual addresses

The assigned addresses can be configured as additional addresses on the network interface. To ensure the IP addresses are configured after a restart, the corresponding configuration files of the operating system/distribution need to be adjusted accordingly. Further details can be found on the pages for [Debian/Ubuntu](https://wiki.hetzner.de/index.php/Netzkonfiguration_Debian/en) and [CentOS](https://wiki.hetzner.de/index.php/Netzkonfiguration_CentOS/en).

Add an (additional) IP address:

`ip address add 10.0.0.8/32 dev eth0`

Alternatively, it can be forwarded within the server (e.g. for virtual machines):

```
ip route add 10.0.0.8/32 dev tap0
# or
ip route add 10.0.0.8/32 dev br0
```

The corresponding virtual machines have to use the main IP address of the server as the default gateway.

```
ip route add 10.0.0.2 dev eth0
ip route add default via 10.0.0.2
```

When forwarding the IP ensure that IP forwarding is enabled:

`sysctl -w net.ipv4.ip_forward=1`

If a separate MAC address has been set for the IP address via the Robot, then the corresponding gateway of the IP address needs to be used.

## Subnets

Newly assigned IPv4 subnets are statically routed on the main IP address of the server, so no gateway is required.

The IPs can be assigned as secondary addresses to the network adapters, just like single additional IPs:

`ip address add 203.0.113.40/32 dev eth0`

They can also be forwarded individually or as a whole.

```
ip route add 203.0.113.40/29 dev tun0
# or
ip route add 203.0.113.40/32 dev tap0
```

Unlike single IPs, subnet IPs can also be assigned (to virtual machines) using DHCP. Therefore an address from the subnet needs to be configured on the host sytem.

`ip address add 203.0.113.41/29 dev br0`

The hosts on br0 use this address as the gateway. Unlike single IPs the rules for subnets then apply, i.e. network and broadcast IP cannot be used.

For IPv6 the routing of the subnet on the link-local address leads to many possibilities for further division of the subnet in various sizes (/64 up to and including /128). For example:

```
2a01:04f8:0061:20e1:0000:0000:0000:0000
                   │    │    │    │
                   │    │    │    └── /112 Subnet
                   │    │    │
                   │    │    └── /96 Subnet
                   │    │
                   │    └── /80 Subnet
                   │
                   └── /64 Subnet
```

Before forwarding, make sure that it is active:

`sysctl -w net.ipv6.conf.all.forwarding=1 net.ipv4.ip_forward=1`

The entire subnet can be forwarded (e.g. VPN):

`ip route add 2001:db8:61:20e1::/64 dev tun0`

Or just a part:

`ip route add 2001:db8:61:20e1::/80 dev br0`

From a single subnet individual addresses can be extracted, while the remainder is forwarded. Note the prefix lengths:

```
ip address add 2001:db8:61:20e1::2/128 dev eth0
ip address add 2001:db8:61:20e1::2/64 dev br0
```

The hosts on `br0` will show `<2001:db8:61:20e1::2>` as the gateway.

## SLAAC (IPv6)

Furthermore, SLAAC (Stateless Address Autoconfiguration) can be used in the connected hosts (br0), by installing `radvd` on the host. The configuarion in `/etc/radvd.conf` requires that the host possesses an address from `<2001:db8:61:20e1::>` on the bridge or Tap device:

```
interface tap0
{
        AdvSendAdvert on;
        AdvManagedFlag off;
        AdvOtherConfigFlag off;
        prefix 2001:db8:61:20e1::/64
        {
                AdvOnLink on;
                AdvAutonomous on;
                AdvRouterAddr on;
        };
        RDNSS 2001:db8:0:a0a1::add:1010
              2001:db8:0:a102::add:9999
              2001:db8:0:a111::add:9898
        {
        };
};
```

Thus the hosts will automatically receive routes and addresses from the subnet. This can be seen within the hosts:

```
$ ip address
2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    link/ether 08:00:27:0a:c5:b2 brd ff:ff:ff:ff:ff:ff
    inet6 2001:db8:61:20e1:38ad:1001:7bff:a126/64 scope global temporary dynamic
       valid_lft 86272sec preferred_lft 14272sec
    inet6 2001:db8:61:20e1:a00:27ff:fe0a:c5b2/64 scope global dynamic
       valid_lft 86272sec preferred_lft 14272sec
    inet6 fe80::a00:27ff:fe0a:c5b2/64 scope link
       valid_lft forever preferred_lft forever
```

(Seen here: privacy address, SLAAC address of the subnet, and the [RFC 4291](http://tools.ietf.org/html/rfc4291) link-local address of the link.)

## Use with virtualization per routed method

See also: [Category:Virtualization](https://wiki.hetzner.de/index.php/Virtualisierung/en)

![alt text](https://wiki.hetzner.de/images/9/99/X-route.png "Logo Title Text 1")

In the routed method a new network interface is configured on the server to which one one or more VMs are connected. The server itself acts as a router, hence the name.

The advantage of the routed method is that traffic has to flow through the host. This is useful for diagnostic tools (tcpdump, traceroute) and also necessary for the operation of a host firewall which performs the filtering for VMs.

Some virtualization solutions create a network interface per unit (like Xen and LXC) and may need to be coupled with a virtual switch (e.g. via a bridge or TAP interface).

* Xen: For each domU an interface vifM.N (unfortunately with dynamic numbers) shows up in the dom0. These can be assigned addresses accordingly. Alternatively VIFs can be combined into a segment using a bridge interface; this is achieved via `vif=['mac=00:16:3e:08:15:07,bridge=br0',]` directives, in `/etc/xen/vm/meingast.cfg`. 

* VirtualBox: Guests are tied to an existing TAP interface and thus form a segment per TAP device. Create TAP interfaces according to your distribution. In the settings dialog of a single machine, select for assignment: `Network` > Attached to: `Bridged Adapter`. Name: `tap0`. 

* VMware Server/Workstation: Using your VMware programs create a host-only interface (e.g. vmnet1) and add to it the address area. Assign the VMs to this created host-only interface. 

* Linux Containers (LXC, systemd-nspawn, OpenVZ): For each container an interface ve-… shows up in the parent. These can be assigned addresses accordingly. Alternatively, VE interfaces can be combined with a bridge interface. 

* QEMU: Uses TAP, similar to VirtualBox. 

## Use with virtualization per bridged method

![alt text](https://wiki.hetzner.de/images/1/1f/X-bridge.png "Logo Title Text 1")

The bridged method describes the configuration which enables a virtual machine to be bridged directly to the connecting network just like a physical machine. This is possible only for single IP addresses. Subnets are always routed.

The advantage of the bridged solution is that the network configuration is usually simple to implement because no routing rules or point-to-point configuration is necessary. The disadvantage is that the MAC address of the guest system becomes "visible" from the outside. Therefore each individual IP address must be given a virtual MAC address, which is possible via the [Robot](https://wiki.hetzner.de/index.php/Robot/en). The IPv6 subnet must then be routed via this new MAC (an icon next to the subnet in the Robot allows this).

* __VMware ESX:__ ESX sets a bridge to the physical adapter, on which the VM kernel hangs and to which further VMs can be bound. For example, a router VM that runs the actual operating system. In ESX further virtual switches can be defined, which are then made available to the router VM through other NICs. 

* The other virtualization solutions offer bridged mode, but for the sake of simplicity we will restrict ourselves to the simpler routed method, since it is also easier for troubleshooting (e.g. mtr/traceroute). Only ESX urgently requires bridged mode. 

* The use of bridged mode currently requires the sysctl function `net.ipv4.conf.default.proxy_arp=1` (e.g. with Xen). 

## Setup under different distributions

Setup guides for different distributions can be found here:

[Xen](https://wiki.hetzner.de/index.php/Kategorie:Xen)
[SUSE](https://wiki.hetzner.de/index.php/Zus%C3%A4tzliche_IP-Adressen_Suse/en)
[Debian](https://wiki.hetzner.de/index.php/Netzkonfiguration_Debian/en)
[Gentoo](https://wiki.hetzner.de/index.php/Zus%C3%A4tzliche_IP-Adressen_Gentoo)
[CentOS](https://wiki.hetzner.de/index.php/Netzkonfiguration_CentOS/en)
[Proxmox](https://wiki.hetzner.de/index.php/Proxmox_VE/en)
[VMware ESXi](https://wiki.hetzner.de/index.php/VMware_ESXi/en)

## Conclusion
By now you should have a basic understanding of Subnets and configuring additional ip adresses.
