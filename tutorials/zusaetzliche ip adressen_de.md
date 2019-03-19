# Zusätzliche IP-Adressen
## Einführung

Bei Bestellung von dedizierten und virtuellen Servern wird neben einer IPv4-Adresse auch ein /64 IPv6-Subnetz zugewiesen.

Zusätzliche IPv4-Adressen können über den Robot bestellt werden. Siehe dazu: [IP-Adressen](https://wiki.hetzner.de/index.php/IP-Adressen).

Der Artikel beschränkt sich darauf, die entsprechenden Linux-Befehle zur Verdeutlichung der Konzepte zu zeigen.

Für Systeme wie FreeBSD ist eine andere Konfiguration notwendig.

## Hauptadresse

Als IPv4-Hauptadresse eines Dedicated/Root-Servers gilt die IP-Adresse, welche initial dem Server zugewiesen wurde und welche bei automatischen Installationen konfiguriert wird.

Für IPv6 gibt es keine fest definierte Hauptadresse. Bei automatischen Installationen wird die ::2 aus dem zugewiesenen Subnetz konfiguriert.

Bei dedizierten Servern und vServern aus der CX-Modellreihe wird das IPv6-Subnetz auf die Link-Local-Adresse des Netzwerkadapters geroutet. Falls Sie zusätzliche IPv4-Adressen mit eigenen separaten MAC-Adressen haben, kann das IPv6-Subnetz über den Hetzner-Robot auch auf deren Link-Lokal-Adresse geroutet werden.

Die jeweilige Link-Local-Adresse errechnet sich aus der MAC-Adresse nach [RFC 4291](http://tools.ietf.org/html/rfc4291) und wird automatisch konfiguriert:

```
# ip address
2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP qlen 1000
    link/ether 54:04:a6:f1:7b:28 brd ff:ff:ff:ff:ff:ff
    inet6 fe80::5604:a6ff:fef1:7b28/64 scope link
       valid_lft forever preferred_lft forever
```

Bei älteren vServer-Modellen (VQ/VX Produkte) erfolgt kein Routing des /64 Subnetzes. Dieses liegt bei diesen Produkten als lokales Netz an, wobei die ::1 als Adresse für das Gateway belegt ist (siehe unten).

Im folgenden wird `<10.0.0.1>` als IPv4-Hauptadresse verwendet. Es handelt sich dabei um keine reale IP-Adresse.

## Zusatzadressen

Sowohl Einzeladressen als auch Adressen aus Subnetzen werden generell über die Hauptadresse geroutet. Sei für den weiteren Verlauf angenommen, Sie hätten die Zusatzadresse/-netze:

* `<2001:db8:61:20e1::/64>` (IPv6-Subnetz)
* `<10.0.0.8>` (Einzeladresse)
* `<203.0.113.40/29>` (IPv4-Subnetz) 

Die zugeteilten Subnetze können nach eigenen Präferenzen weiter aufgeteilt oder weitergeleitet bzw. zugewiesen werden.

Bei IPv4 sind im Normfall die Netzwerkadresse und Broadcastadresse reserviert. Bezogen auf das o.g. Beispiel die `<203.0.113.40>` bzw. `<203.0.113.47>`.

Bei Verwendung als Sekundär-IP bzw. im Rahmen eines Point-to-Point-Setups, können auch die sonst reservierten Adressen verwendet werden. Dadurch stehen bei einem /29 IPv4-Subnetz alle 8 statt nur 6 Adressen zur Verfügung.

Bei IPv6 ist die erste Adresse (::0) eines Subnetzes die "Subnet-Router Anycast"-Adresse reserviert. IPv6 verwendet kein Broadcast, sodass die letzte Adresse in einem Subnetz anders als bei IPv4 auch nutzbar ist.

## Gateway

Bei IPv6 auf dedizierten und vServern aus der CX-Modellreihe ist das Gateway die `fe80::1`. Da es sich um eine Link-Local-Adresse handelt, ist die explizite Angabe des Netzwerkadapters (in der Regel `eth0`) notwendig:

`# ip route add default via fe80::1 dev eth0`

Bei älteren vServern (VQ/VX-Produktereihe) liegt der Gateway innerhalb des zugewiesenen Subnetzes:

```
# ip address add 2001:db8:61:20e1::2/64 dev eth0
# ip route add default via 2001:db8:61:20e1::1
```

Für IPv4 ist der Gateway die erste nutzbare Adresse des jeweiligen Subnetzes:

```
# Beispiel: 10.0.0.1/26 => Netzadr. ist 192.0.2.64/26
#
# ip address add 10.0.0.1/32 dev eth0
# ip route add 192.0.2.65 dev eth0
# ip route add default via 192.0.2.65
```

## Einzel-Adressen

Die zugewiesen Adressen können als weitere Adresse auf der physischen Netzwerkschnittstelle angelegt werden. Damit die IP-Adressen auch nach einem Neustart des Server wieder angelegt werden, muss dies in den entsprechenden Konfigurationsdateien des Betriebssystems/der Distribution hinterlegt werden. Weitere Details für [Debian/Ubuntu](https://wiki.hetzner.de/index.php/Netzkonfiguration_Debian)  bzw. [CentOS](https://wiki.hetzner.de/index.php/Netzkonfiguration_CentOS).

Hinzufügen einer (zusätzlichen) IP-Adresse:

`ip address add 10.0.0.8/32 dev eth0`

Alternativ kann diese innerhalb des Servers weitergeleitet werden (z.B. an virtuelle Maschinen):

```
ip route add 10.0.0.8/32 dev tap0
# oder
ip route add 10.0.0.8/32 dev br0

```

Die entsprechenden virtuellen Maschinen müssen dabei die Haupt-IP-Adresse des Root-Servers als Default-Gateway verwenden.

```
ip route add 10.0.0.1 dev eth0
ip route add default via 10.0.0.1
```

Bei Weiterleitung ist sicherzustellen, dass IP-Forwarding aktiviert ist:

`sysctl -w net.ipv4.ip_forward=1`

Falls eine separate MAC-Adresse über den Hetzner-Robot für die IP-Adresse eingestellt ist, so muss der für die IP-Adresse entsprechende Gateway verwendet werden.

## Subnetze

Neu vergebene IPv4-Subnetze werden auf die Haupt-IP-Adresse des Servers geroutet. Es wird wird daher kein Gateway benötigt.

IP-Adressen können analog zu Einzel-IPs als Sekundäradressen einem Netzwerkadapter zugewiesen werden:

` ip address add 203.0.113.40/32 dev eth0`

Sie können ebenfalls einzeln oder als ganzes weitergeleitet werden.

```
ip route add 203.0.113.40/29 dev tun0
#
ip route add 203.0.113.40/32 dev tap0
```

Anders als bei Einzeladressen kann bei Subnetzen die Adressvergabe (an virtuelle Maschinen) auch via DHCP erfolgen. Dabei muss eine Adresse des Subnetzes im Hostsystem konfiguriert werden.

`ip address add 203.0.113.41/29 dev br0`

Die Hosts an br0 verwenden diese entsprechend als Gateway. Anders als bei Einzel-IPs gelten dann Regeln für Subnetze, d.h. Netz- und Broadcast-IP können nicht genutzt werden.

Bei IPv6 ergeben sich durch das Routing des Subnetzes auf die Link-Local-Adresse viele Möglichkeiten zur weiteren Aufteilung des Subnetzes (/64 bis einschließlich /128), beispielsweise:

```
2a01:04f8:0061:20e1:0000:0000:0000:0000
                   │    │    │    │
                   │    │    │    └── /112-Subnet
                   │    │    │
                   │    │    └── /96-Subnet
                   │    │
                   │    └── /80-Subnet
                   │
                   └── /64-Subnet
```

Vor Weiterleitung ist sicherzustellen, dass Forwarding aktiviert ist:

`sysctl -w net.ipv6.conf.all.forwarding=1 net.ipv4.ip_forward=1`

Es kann das gesammte Subnetz weitergeleitet werden (z.B. VPN):

`ip route add 2001:db8:61:20e1::/64 dev tun0`

Oder nur ein Teil:

`ip route add 2001:db8:61:20e1::/80 dev br0`


Aus einem Subnetz können auch eine oder mehrere Einzeladressen herausgelöst werden, während der Rest weitergeleitet wird. Man beachte dabei die Präfixlängen:

```
ip address add 2001:db8:61:20e1::2/128 dev eth0
ip address add 2001:db8:61:20e1::2/64 dev br0
```

Die Hosts an br0 geben als Gateway dann `<2001:db8:61:20e1::2>` an.

## SLAAC (IPv6)

Weiterhin kann SLAAC (Stateless Address Autoconfiguration) in den angeschlossenen Hosts (br0) nutzen, indem auf dem Host `radvd` installiert wird. Dessen Konfiguration in `/etc/radvd.conf` erfordert, dass der Host wie beschrieben selbst eine Adresse aus `<2001:db8:61:20e1::>` auf der Bridge bzw. dem TAP-Device besitzt:

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

So erhalten die Hosts an der Linux-Bridge automatisch Routen und Adressen aus dem Subnetz, daran zu erkennen, dass innerhalb der Hosts ebensolche zu sehen sind:

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

(Hier zu sehen: Privacy-Adresse, SLAAC-Adresse des Netzes, und die [RFC 4291](http://tools.ietf.org/html/rfc4291)-Link-Local-Adresse des Links.)

Nutzung mit Virtualisierung per Routed-Methode

Siehe auch: [Kategorie:Virtualisierung](https://wiki.hetzner.de/index.php/Kategorie:Virtualisierung)

![alt text](https://wiki.hetzner.de/images/9/99/X-route.png "Logo Title Text 1")

Bei der "Routed"-Methode wird auf dem Server ein neues Netzwerkinterface erstellt, an dem eine oder mehrere VMs angeschlossen sind. Der Server selbst fungiert als Router, daher der Name.

Der Vorteil der Routed-Methode ist, dass Traffic den Host durchläuft, was sowohl für Diagnosewerkzeuge (tcpdump, traceroute) hilfreich, und für den Betrieb einer Host-Firewall, die die Filterung für VMs vornimmt, auch notwendig ist.

Einige Virtualisierungslösungen erstellen pro Einheit ein Netzwerkinterface (wie Xen und LXC) und müssen ggf. an einen virtuellen Switch (z.B. per Bridge- oder TAP-Interface) gekoppelt werden.

* Xen: Für jede domU taucht in der dom0 ein Interface vifM.N (leider mit dynamischen Nummern) auf. Diesen können dann entsprechend Adressbereiche zugewiesen werden. Alternativ können VIFs mittels Bridge-Interface zu einem Segment kombiniert werden; dies geht via ``vif=['mac=00:16:3e:08:15:07,bridge=br0', ]``-Direktive in `/etc/xen/vm/meingast.cfg`). 

* VirtualBox: Gäste können einem Host-Only-Interface (oft vboxnet0 genannt) oder einem bereits bestehendem TAP-Interface zugewiesen werden. Alle an ein HO-/TAP-Interface angeschlossenen Clients befinden sich im selben Segment. 

* VMware Server/Workstation: Erstellen Sie mithilfe den VMware-Programmen ein Host-Only-Interface (z.B. vmnet1), und fügen Sie diesem Adressbereiche zu. Weisen Sie die VM(s) dem erstellten HO-Interface zu. 

* Linux Containers (LXC, systemd-nspawn, OpenVZ): Für jeden Container taucht im Parent ein Interface ve-… auf. Diesen können dann entsprechend Adressbereiche zugewiesen werden. Alternativ können VE-Interfaces zu/mit einem Bridge-Interface kombiniert werden. 

* QEMU: verwendet TAP direkt, ähnl. VirtualBox. 

## Nutzung mit Virtualisierung per Bridged-Methode

![alt text](https://wiki.hetzner.de/images/1/1f/X-bridge.png "Logo Title Text 1")

Mit der Bridged-Methode wird die Konfiguration bezeichnet, bei der eine virtuelle Maschine direkt mit dem Anschlussnetz verbunden ist wie ein physikalisches System. Die direkte Verbindung mit dem Anschlussnetz ist nur für Einzel-IPv4-Adressen möglich. Subnetze sind immer geroutet.

Vorteil der Bridged-Lösung ist, dass sich die Netzwerkkonfiguration in der Regel einfach(er) gestaltet, da keine Routing-Regeln bzw. Point-to-Point-Konfiguration notwendig ist. Nachteil ist, dass die MAC-Adresse des Gasts außerhalb des Hostsystems "sichtbar" wird, und dass der Host, der die Bridge betreibt, per Definition nicht in traceroutes auftaucht. Für jede Einzel-IPv4-Adresse ist eine virtuelle MAC-Adresse über den [Hetzner Robot](http://robot.your-server.de/) zu beantragen, und die IPv6 Subnetze müssen über diese neue MAC dann geroutet werden (auch dazu ein Icon im Robot neben dem Subnetz).

* __VMware ESX:__ ESX setzt eine Bridge an dem physikalischem Adapter an, an dem das ESX-Managementsystem hängt und an die weitere VMs gebunden werden können, so z.B. eine Router-VM, die das eigentliche Betriebssytem fährt. Im vSphere Center können dann virtuelle Switches definiert werden, die dann über weitere NICs der Router-VM zugänglich gemacht werden. 

* Auch die anderen Virtualisierungslösungen offerieren Nutzung im Bridged-Modus, wir möchten uns der Einfachheit halber aber auf die einfacherere Routed-Methode beschränken, da dort auch die Problembehandlung leichter fällt (z.B. mit mtr/traceroute). Nur ESX benötigt dringend den Bridged-Modus. 

* Die Nutzung des Bridged-Modus erfordert z.T. die `sysctl`-Funktion `net.ipv4.conf.default.proxy_arp=1` (z.B. bei Xen). 


## Einrichtung unter verschiedenen Distributionen

Für die Einrichtung unter verschiedenen Distributionen haben wir separate Artikel:

[Xen](https://wiki.hetzner.de/index.php/Kategorie:Xen)
[SUSE](https://wiki.hetzner.de/index.php/Zus%C3%A4tzliche_IP-Adressen_Suse)
[Debian](https://wiki.hetzner.de/index.php/Netzkonfiguration_Debian)
[Gentoo](https://wiki.hetzner.de/index.php/Zus%C3%A4tzliche_IP-Adressen_Gentoo)
[CentOS](https://wiki.hetzner.de/index.php/Netzkonfiguration_CentOS)
[Proxmox VE](https://wiki.hetzner.de/index.php/Proxmox_VE)
[VMware ESXi](https://wiki.hetzner.de/index.php/VMware_ESXi)

## Fazit

Dieser Artikel hat ihnen hoffentlich das Grundprinzip von Subnetzen und zusätzlichen IP-Adressen verdeutlicht.


