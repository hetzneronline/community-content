# Netzkonfiguration für Xen und KVM mit libvirt
## Einführung

Sowohl für die Nutzung von Xen als auch für KVM empfiehlt es sich libvirt zur Einrichtung zu Verwenden. Grund ist u.a., dass mit den Xen-Skripten ohne Modifikationen nur ein virtuelles Netzwerk (Bridge) eingerichtet werden kann. Möchte man neben den kostenlosen Zusatz-IPs ein oder mehrere Subnetze für VMs betreiben, benötigt eine Möglichkeit mehrere virtuelle Switche (realisiert über Software-Bridges) anzulegen.

## Einzel-IPv4-Adressen
### Routed bridge (brouter)

Die Konfiguration der neuen routed Bridge für die Zusatz-IPs muß leider händisch geschehen, da libvirt automatisch unpassende Firewallregeln anlegt und sich dieses Verhalten nicht abschalten lässt. Die Konfiguration einer solchen Bridge erfolgt wie unter Netzkonfiguration [Debian](https://wiki.hetzner.de/index.php/Netzkonfiguration_Debian) oder [CentOS](https://wiki.hetzner.de/index.php/Netzkonfiguration_CentOS) beschrieben.

## Subnetz

Für ein Subnetz kann hingegen folgendes XML-Template verwendet werden:

```xml
<network>
<name>hetzner-subnetz1</name>
<uuid>(irgendeine uuid)</uuid>
<forward dev='eth0' mode='route'/>
<bridge name='virbr2' stp='off' forwardDelay='0' />
<ip address='<Erste-Subnetz-IP>' netmask='255.255.255.224' />
</network>
```
Die UUID kann weggelassen werden, es ist aber auch möglich selbst eine mittels `uuidgen` zu erzeugen.

Das fertige XML wird dann in eine Datei gespeichert und mittels `virsh net-define <dateiname>` in libvirt bekannt gemacht. Das neue "Netzwerk" hat in libvirt den Namen `hetzner-subnetz1`.

Nach dem Aufruf von

`virsh net-autostart hetzner-subnetz1` 

ist sichergestellt, dass das Netzwerk auch tatsächlich nach jedem Systemstart sofort verfügbar ist.

## Netzkonfiguration aktivieren

`virsh net-start hetzner-subnetz1`

## Fazit

