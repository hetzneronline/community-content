
## Cloud Hosted Router - MikroTik
This tutorial is about a workaround on how to install the RouterOS on any Hetzner Cloud server. This can be done in just a few steps.

### Introduction

We are focusing on installing the OS and doing a basic setup in this documentation. For any further details for configuration, look up in the official wiki from [MikroTik](https://wiki.mikrotik.com/wiki/Main_Page).



### Overview
1. Installation
2. Make it secure
3. Basic Firewall setup

### 1. Installation
First, if not already done, create a server of your choice. Then boot it into the `rescue` system. The login credentials are shown while requesting it. Afterwards we need the `Raw disk image` from the website. Choose the desired version here: [Download](https://mikrotik.com/download#chr).
When this is done, simply curl the image and write it on the drive via dd. 
This command does all the necessary steps:
```
# curl -L <Link_of_image> | funzip | dd of=/dev/sda bs=1M
```

### 2. Make it secure

Afterwards you can reboot the server into the new installed OS. 
Keep in mind, that the default login credentials are `user : admin` and `password : none`. Therefore we recommend on immediately disable the admin user and add a new one.
Login to your server via ssh or the Hetzner Console and do the following commands:

```
# /user add name=<username> password=<userpassword> group=full
# /user remove admin
```
If desired you can add an IP-Address to a user, from where it is only allowed to access.
```
# /user set <username> allowed-address=<IPv4>/<Netmask>
```

Now we want to disable all unnecessary services. Current running services can be shown via `# /ip service print`.  In this case we will disable all except `ssh` :
```
# /ip service disable telnet,ftp,www,api,api-ssl,winbox
``` 
We are recommending to change the `default ssh port 22` with any other desired. 
```
# /ip service set ssh port=33458
```
The following commands disable unwanted management access to network devices. We recommend to disable it:
```
# /tool mac-server set allowed-interface-list=none
# /tool mac-server mac-winbox set allowed-interface-list=none
# /tool mac-server ping set enabled=no
# /tool bandwidth-server set enabled=no
# /ip neighbor discovery-settings set discover-interface-list=none 
# /ip dns set allow-remote-requests=no
# /ip proxy set enabled=no
# /ip socks set enabled=no
# /ip upnp set enabled=no
# /ip cloud set ddns-enabled=no update-time=no
# /ip ssh set strong-crypto=yes
```

### 3. Basic Firewall setup
Right from the start the CHR has a basic firewall setup and we do strongly recommend to not turn it off, if you are not 100% sure what to do. The following rules are adjusting it to make it more secure:
```
# /ip firewall filter
# add action=accept chain=input connection-state=established,related # accept established/related connections 
# add action=accept chain=input src-address-list=<list-name> # IP's in <list-name> are allowe to access 
# add action=accept chain=input protocol=icmp # allows ICMP
# add action=drop chain=input # Other connections getting droped
# /ip firewall address-list
# add address=10.0.0.1-10.0.0.254 list=<list-name> # adds addresses to <list-name>
```

We will now create some basic adjustments to the firewall rules for the clients. 
First add the desired private networks to a list:
```
# /ip firewall address-list
# add address=10.0.0.0/24 list=private_networks
# add address=10.0.1.0/24 list=private_networks
...
```
Now we want to secure those networks. 
First packets with `connection-state=established,related` are added to [FastTrack](https://wiki.mikrotik.com/wiki/Manual:IP/Fasttrack) and only new connections will be allowed by the firewall. We also will set a rule next to drop any invalid connection. Those are logged with the tag `invalid`.
The same is done for private IP's, which try to reach a public IP. To secure that non-public addresses from outside are trying to reach your server, we are dropping those packages, as well as packages from the LAN with non-private IP's.

``` 
# /ip firewall filter
# add action=fasttrack-connection chain=forward connection-state=established,related
# add action=accept chain=forward connection-state=established,related
# add action=drop chain=forward connection-state=invalid log=yes log-prefix=invalid
# add action=drop chain=forward dst-address-list=not_in_internet in-interface=bridge1 log=yes log-prefix=!public_from_LAN out-interface=!bridge1
# add action=drop chain=forward connection-nat-state=!dstnat connection-state=new in-interface=ether1 log=yes log-prefix=!NAT
# add action=drop chain=forward in-interface=ether1 log=yes log-prefix=!public src-address-list=private_networks
# add action=drop chain=forward in-interface=bridge1 log=yes log-prefix=LAN_!LAN src-address=!<privateIP-network>
```

### Conclusion

After you have followed all steps correctly, you should have a stable basic setup of the Cloud Hosted Router OS.
Any further instructions can be found [here](https://wiki.mikrotik.com/wiki/Manual:CHR).


