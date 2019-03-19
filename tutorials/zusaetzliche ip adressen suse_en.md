# Additional IP-Addresses Suse
## Introduction
This article demonstrates the setup of additional ip addresses on Suse.
Further information can be found [here](https://wiki.hetzner.de/index.php/Zusaetzliche_IP-Adressen/en)

## Configuration with YaST

Open the following dialog in YaST:

`Network Devices` -> `Network Settings` -> `<Name of the Network card>` -> `Edit` -> `Additional Addresses`

There you can add an entry for each additional IP by clicking on `[Add]`.

`Alias name =` You can choose this randomly, eg. `additional1`, `additional2`, etc.
You can find the IP addresses and subnet mask in the email.

For special requirements (eg. a mail server), you will probably need to make further adjustments.
For an Apache web server, the settings described are enough.

## Editing the configuration files directly

As an alternative to YaST, you can edit the network configuration files directly. You can find the configuration for `eth0` in `/etc/sysconfig/network/ifcfg-eth0` and you can add to it as follows:

```
IPADDR_2='188.40.40.74/32'
REMOTE_IPADDR_2='188.40.40.65'
```

You can choose the name for IP addresses as you wish (after the IPADDR_ prefix). In this example, `IPADDR_2` was used but it could just as easily be `IPADDR_FOO` or `IPADDR_BAR`. You can find further descriptions in the `ifcfg(5) man pages`.

## Virtualization

If you are using virtualization, then the additional IP addresses will be used via a guest system. To make sure that you can access these via the Internet, you need to adjust a specific configuration in the host system in order to transmit packets. While adjusting the configuration, you have a choice for the configuration of the additional IPs you want: routed and bridged.

### Routed (brouter)

With the routed configuration, the packets are routed. To do this, you need to set up an additional bridge with almost the exact same configuration (without a gateway) like eth0. (Note: While doing an installation using YaST, there is already a bridge available; you just need to correctly configure it.)

```
# cat /etc/sysconfig/network/ifcfg-eth0
# device: eth0
MTU=
STARTMODE='auto'
UNIQUE=
USERCONTROL='no'
BOOTPROTO='static'
IPADDR='(Haupt-IP)/32'
REMOTE_IPADDR='(Gateway-IP)'
NETMASK=
BROADCAST=
ETHTOOL_OPTIONS=
NAME=
NETWORK=
```

```
# cat /etc/sysconfig/network/ifcfg-br0
BOOTPROTO='static'
BRIDGE='yes'
BRIDGE_FORWARDDELAY='0'
BRIDGE_PORTS=
BRIDGE_STP='off'
IPADDR='(Haupt-IP)/32'
MTU=
STARTMODE='auto'
UNIQUE=
USERCONTROL='no'
BROADCAST=
ETHTOOL_OPTIONS=
NAME=
NETWORK=
REMOTE_IPADDR=
```

```
# cat /etc/sysconfig/network/routes
default [Gateway IP] - eth0
[Additional IP]/32 - - br0
```

Wichtig: IP Forwarding via Yast oder direkt in `/etc/sysctl.conf` aktivieren.

### Gast

```
# cat /etc/sysconfig/network/ifcfg-eth0
# device: eth0
MTU=
STARTMODE='auto'
UNIQUE=
USERCONTROL='no'
BOOTPROTO='static'
IPADDR='(Zusatz-IP)/32'
REMOTE_IPADDR='(Haupt-IP)'
NETMASK=
BROADCAST=
ETHTOOL_OPTIONS=
NAME=
NETWORK=
```

```
# cat /etc/sysconfig/network/routes
default (Haupt-IP) - eth0
```

## Conclusion
By now you should have configured your Suse-based System to use additional IPs.