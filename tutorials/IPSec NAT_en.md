# IPsec NAT
## Introduction

To establish a secure connection between hosts IPSec is often used. During installation one must remember, that CX-vServers translate the public IP via 1:1 NAT to an internal IP.
This tutorial uses StrongSWAN and Pre-shared-keys to establish a transparent IPSec connection between an external host and a CX vServer. The public IPs can then be used on both sides.

## Data
### Host Alice (Endpoint Host A)

* System: Debian 8 jessie
* IPSec: strongswan 5.2.1-6+deb8u2
* IPv4: 192.0.2.10
* IPv6: 2001:db8:61:20e1::2 

### Host Bob (Endpoint CX vServer)

* System: Debian 8 jessie
* IPSec: strongswan 5.2.1-6+deb8u2
* IPv4: 203.0.113.40
* IPv4 intern: 172.31.1.100
* IPv6: 2a01:4f8:db8:c17::2 

## Installation

We build two tunnels, one for  IPv4 and one for IPv6.

### Alice

Installing packages:

```
# apt-get install strongswan
# ipsec stop
```

Creating/Modifying the  `/etc/ipsec.conf` file with the correct values:

```
 version 2.0
 #
 config setup
 #
 conn NameDerVerbindung
         type=transport
         keyingtries=0
         authby=secret
         auto=start
         leftid=192.0.2.10
         left=192.0.2.10
         rightid=203.0.113.40
         right=203.0.113.40
 #
 conn NameDerVerbindungSix
         type=transport
         keyingtries=0
         authby=secret
         auto=start
         leftid=2001:db8:61:20e1::2
         left=2001:db8:61:20e1::2
         rightid=2001:db8:c17::2
         right=2001:db8:c17::2
```

Some StrongSwan Versions use specific insertions. In our case they are exactly 8 Spaces or one tabulator.

Creating/Modifying the  `/etc/ipsec.secrets` file with the correct values:

```
 203.0.113.40 192.0.2.10 : PSK "SuperGeheimesPasswortFuerIp4Tunnel"
 2001:db8:c17::2 2001:db8:61:20e1::2 : PSK "SuperGeheimesPasswortFuerIp6Tunnel"
```

Starting the IPSec-Tunnel:

`# ipsec start`

### Bob

Installing Packages:

```
# apt-get install strongswan
# ipsec stop
```

Creating/Modifying the  `/etc/ipsec.conf` file with the correct values:

```
 version 2.0
 #
 config setup
 #
 conn NameDerVerbindung
         type=transport
         keyingtries=0
         authby=secret
         auto=start
         leftid=203.0.113.40
         left=172.31.1.100
         rightid=192.0.2.10
         right=192.0.2.10
 #
 conn NameDerVerbindungSix
         type=transport
         keyingtries=0
         authby=secret
         auto=start
         leftid=2001:db8:c17::2
         left=2001:db8:c17::2
         rightid=2001:db8:61:20e1::2
         right=2001:db8:61:20e1::2
```
Creating/Modifying the  `/etc/ipsec.secrets` file with the correct values:


```
 192.0.2.10 203.0.113.40 : PSK "SuperSecretPassworwForIp4Tunnel"
 2001:db8:61:20e1::2 2001:db8:c17::2 : PSK "SuperSecretPasswordForIp6Tunnel"
```

Starting the IPSec-Tunnel

`# ipsec start`

## Test

The tunnels should be created successfully. You can check it in `/var/log/syslog` or `/var/log/auth.log`

The command:

` ipsec status`

should create something like this:

```
 Security Associations (2 up, 0 connecting):
  TemplateTwo[20]: ESTABLISHED 76 minutes ago, 172.31.1.100[203.0.113.40]...192.0.2.10[192.0.2.10]
  TemplateTwo{12}:  INSTALLED, TRANSPORT, ESP in UDP SPIs: c5b8b7f7_i ccf37767_o
  TemplateTwo{12}:   172.31.1.100/32 === 192.0.2.10/32
 TemplateTwoSix[19]: ESTABLISHED 82 minutes ago, 2001:db8:c17::2[2001:db8:c17::2]...2001:db8:61:20e1::2[2001:db8:61:20e1::2]
 TemplateTwoSix{11}:  INSTALLED, TRANSPORT, ESP SPIs: c5521750_i cbe50d53_o
 TemplateTwoSix{11}:   2001:db8:c17::2/128 === 2001:db8:61:20e1::2/128
```

You should be able to see the ESP Packages via `tcpdump` (simple Ping):

```
 13:42:53.000336 IP 203.0.113.40.ipsec-nat-t > 192.0.2.10.ipsec-nat-t: UDP-encap: ESP(spi=0xccf37767,seq=0x516), length 116
```

## Conclusion

By now you should be able to install an IPSec Connection.