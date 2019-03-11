# Cloud floating IP persistent
## Introduction
???

Hint: If you are using more than one floating IP, then the number on the interface (eth0:1) will be increased (example eth0:2).

## On Debian based distributions (Ubuntu, Debian):
 SSH into the server

Create the configuration file and open an editor

```
touch /etc/network/interfaces.d/60-my-floating-ip.cfg
nano /etc/network/interfaces.d/60-my-floating-ip.cfg
```

Paste the following configuration into the editor and replace `your.float.ing.ip` with your floating ip:

IPv4:

```
auto eth0:1
iface eth0:1 inet static
    address your.float.ing.ip
    netmask 32
```

IPv6:

```
auto eth0:1
iface eth0:1 inet6 static
    address one IPv6 address of the subnet, e.g. 2a01:4f9:0:2a1::2
    netmask 64
```

Now you should restart your network. Caution: This will reset your network connection

`sudo service networking restart`


## RHEL based distributions (Fedora, CentOS):

SSH into the server

Create the configuration file and open an editor

```
touch /etc/sysconfig/network-scripts/ifcfg-eth0:1
vi /etc/sysconfig/network-scripts/ifcfg-eth0:1
```

Paste the following configuration into the editor and replace `your.float.ing.ip` with your floating ip

IPv4:

```
BOOTPROTO=static
DEVICE=eth0:1
IPADDR=your.float.ing.ip
PREFIX=32
TYPE=Ethernet
USERCTL=no
ONBOOT=yes
```


IPv6:

```
BOOTPROTO=none
DEVICE=eth0:1
ONBOOT=yes
IPV6ADDR=one IPv6 address of the subnet, e.g. 2a01:4f9:0:2a1::2/64
IPV6INIT=yes
```

Now you should restart your network. Caution: This will reset your network connection

`systemctl restart network`

## Conclusion
???
