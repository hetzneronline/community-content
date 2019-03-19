# Zusätzliche IP-Adressen Gentoo
## EInführung
In diesem Artikel wird die Einrichtung zusätzlicher IP-Adressen unter Gentoo beschrieben. 
Allgemeinere Informationen können [hier](https://wiki.hetzner.de/index.php/Zusaetzliche_IP-Adressen) gefunden werden.

## Konfiguration mit iproute2

Zunächst den Schnittstellen-Handler iproute2 aktivieren:

`emerge iproute2`

Die zusätzlichen IP-Adressen müssen in `/etc/conf.d/net` eingetragen werden:

```
modules=( "iproute2" )
config_eth0=(
   "aaa.aaa.aaa.aaa netmask 255.255.255.224 brd xxx.xxx.xxx.xxx"
   "bbb.bbb.bbb.bbb netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "ccc.ccc.ccc.ccc netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "ddd.ddd.ddd.ddd netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "eee.eee.eee.eee netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "fff.fff.fff.fff netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "ggg.ggg.ggg.ggg netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
)
routes_eth0=(
   "default via rrr.rrr.rrr.rrr"
)
```

Siehe [Legende](## Legende)

Hinweis: Es reicht die Angabe der Routeradresse des Subnetzes der Haupt-IP, das funktioniert dann auch für die zusätzlichen IP-Adressen.

### Kürzere Schreibweise

Genau den gleichen Effekt hat die kürzere Maskenschreibweise (IP/CIDR):

```
modules=( "iproute2" )
config_eth0=(
   "aaa.aaa.aaa.aaa/27" # Die Haupt-IP des Servers
   "bbb.bbb.bbb.bbb/29" # Die nutzbaren IPs des Subnet
   "ccc.ccc.ccc.ccc/29"
   "ddd.ddd.ddd.ddd/29"
   "eee.eee.eee.eee/29"
   "fff.fff.fff.fff/29"
   "ggg.ggg.ggg.ggg/29"
)
routes_eth0=(
   "default via rrr.rrr.rrr.rrr" # IP des zuständigen Netz-Routers
)
```

Um ohne Zusatz-Route die benachbarten Server zu erreichen, ändert man den Eintrag zur Haupt-IP wie folgt:

`"aaa.aaa.aaa.aaa peer rrr.rrr.rrr.rrr"`
Siehe Legende

## Routing des Subnetzes
Man kann die Subnetz-IPs auch über das entsprechende Gateway (nur wenn Gateway-IP zugewiesen wurde) routen lassen:

```
routes_eth0=(
   "default via rrr.rrr.rrr.rrr" # IP des zuständigen Netz-Routers
   "sss.sss.sss.sss/29 via ggg.ggg.ggg.ggg" # IP des Subnet über das entsprechende Subnetz-Gateway
)
```

## Alternative Konfiguration ohne iproute

Diese Konfiguration kommt ohne iproute2 aus, ist aber umständlicher zu konfigurieren. Allgemein wird die Methode mittels iproute2 empfohlen.

Um die Adressen einzeln ansprechen können, muss man noch je nach Subnetz anders routen:

in `/etc/conf.d/net` :

```
config_eth0=(
   "aaa.aaa.aaa.aaa netmask 255.255.255.224 brd xxx.xxx.xxx.xxx"
   "bbb.bbb.bbb.bbb netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "ccc.ccc.ccc.ccc netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "ddd.ddd.ddd.ddd netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "eee.eee.eee.eee netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
   "fff.fff.fff.fff netmask 255.255.255.248 brd yyy.yyy.yyy.yyy"
)
routes_eth0=(
   "default via rrr.rrr.rrr.rrr"
   "sss.sss.sss.sss/29 dev eth0 table web"
   "default via ggg.ggg.ggg.ggg table web"
)
```
dazu in /etc/conf.d/local.start

```
ip rule add from bbb.bbb.bbb.bbb table web
ip rule add from ccc.ccc.ccc.ccc table web
ip rule add from ddd.ddd.ddd.ddd table web
ip rule add from eee.eee.eee.eee table web
ip rule add from fff.fff.fff.fff table web
```

und in /etc/iproute2/rt_tables

`100     web`
hinzufügen.

## IPv6

Sollten schon IPv4 Einträge vorhanden sein, können diese einfach erweitert werden.

`/etc/conf.d/net`:

```
# Native IPv6 /64
v6net="2a01:4f8:61:20e1"
```

```
# Beispiel: Sie haben ein Subnetz 2a01:4f8:61:20e1::/64
# und Sie wollen 2a01:4f8:61:20e1::2 aus diesem Subnetz benutzen:
config_eth0=(
    "${v6net}::2/64" # Gewünschte IP aus dem IPv6 block.
                     # Jede weitere IP muss genau so hinzugefügt werden.
    ...
)
```

```
routes_eth0=(
   # Gateway
   "default via fe80::1 dev eth0"
)
```

Für weitere Informationen siehe [hier](https://wiki.hetzner.de/index.php/Zusaetzliche_IP-Adressen#IPv6_Subnetz)

## Legende

* a = Haupt-IP-Adresse
* x = Broadcast-Adresse des Subnetzes der Haupt-IP
* b-f = Zusatz-IP-Adressen
* y = Broadcast-Adressen des Subnetzes der Zusatz-IPs
* g = Wenn das Subnetz nach dem 14.11.2007 vergeben wurde, kann eine IP zusätzlich genutzt werden, dabei handelt es sich um die ehemalige Gateway-IP.
* r = Router-IP-Adresse des Subnetzes der Haupt-IP
* s = Netzwerk-IP des Subnetzes
Bei Unklarheiten bitte an den Support wenden.

## Weblinks
Sehen Sie dazu auch das passende Thema im offiziellen [Gentoo-Forum](http://forums.gentoo.org/viewtopic-t-571184.html).

## Fazit
Nun sollten sie über einen der gezeigten Wege zusätzliche IP-Adressen auf ihrem Gentoo basierten Server eingerichtet haben.