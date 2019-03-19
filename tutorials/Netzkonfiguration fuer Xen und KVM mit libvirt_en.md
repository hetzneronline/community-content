# Net Configuration for Xen and KVM with libvirt
## Introduction
When using Xen or KVM it is recommendet to configure them with libvirt. It drastically extends the possibilites for using multiple subnets, virtual networks and switches.

## Single-IPv4-Adresses
### Routed bridge (brouter)
Configuring our new Routed Bridge for additional IPs needs to be done manually. Configuring said bridge can be found here for [Debian](https://wiki.hetzner.de/index.php/Netzkonfiguration_Debian) or [CentOS](https://wiki.hetzner.de/index.php/Netzkonfiguration_CentOS) .

## Subnet

For a subnet one can simply use this xml-file:

```xml
<network>
<name>hetzner-subnet1</name>
<uuid>(random uuid)</uuid>
<forward dev='eth0' mode='route'/>
<bridge name='virbr2' stp='off' forwardDelay='0' />
<ip address='<first-subnet-ip>' netmask='255.255.255.224' />
</network>
```

The UUID can be left out, or created new with `uuidgen`.
The completed XML is being saved and published in libvirt via `virsh net-define <dateiname>`. The new Network has the name `hetzner-subnetz1` in libvirt.

Type in
`virsh net-autostart hetzner-subnetz1` 
to make sure the network is immediately ready after every system start.

## Activating the Net configuration

`virsh net-start hetzner-subnetz1`

## Conclusion

By now you should have configured your Routed Bridge with libvirt.

