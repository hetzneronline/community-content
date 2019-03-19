# Zusätzliche IP-Adressen Suse
## Einführung
In diesem Artikel wird die Einrichtung zusätzlicher IP-Adressen unter Suse beschrieben. 
Allgemeinere Informationen können [hier](https://wiki.hetzner.de/index.php/Zusaetzliche_IP-Adressen) gefunden werden.
 
## Konfiguration mit YaST

Im YAST folgenden Dialog aufrufen:

`Netzwerkgeräte` -> `Netzwerkkarte` -> Netzwerkkarte auswählen -> `[Bearbeiten]` -> `[Erweitert...]` -> `Zusätzliche Adressen`

Dort über `[Hinzufügen]`für jede zusätzliche IP einen weiteren Eintrag hinzufügen.

`Aliasname = `scheinbar beliebig, z.B. `zusatz1`, `zusatz2`, usw...
IP-Adressen und Netzmaske aus der Mail entnehmen.

Für spezielle Anforderungen (z.B. Mailserver) wird man vermutlich noch weitere Einstellungen vornehmen müssen.
Zum Aufruf des Apache-Webserver reichen die beschriebenen Einstellungen jedenfalls aus.

## Konfigurationsdateien direkt bearbeiten

Als Alternative zu YaST kann man die Netzwerkkonfigurationsdateien direkt bearbeiten. Die Konfiguration für `eth0` findet sich in `/etc/sysconfig/network/ifcfg-eth0` und lässt sich wie folgt erweitern:

``` 
IPADDR_2='188.40.40.74/32'
REMOTE_IPADDR_2='188.40.40.65'
```

Der Variablenname für IP-Adressen ist (nach IPADDR_-Präfix) frei wählbar, hier wurde `IPADDR_2` verwendet, es hätte genauso auch `IPADDR_FOO` oder `IPADDR_BAR` sein dürfen. Weitere Beschreibungen entnehmen Sie der `ifcfg(5)-Manpage`.


## Virtualisierung

Bei Einsatz von Virtualisierung werden die zusätzliche IP-Adressen durch die Gast-Systeme genutzt. Damit diese im Internet erreichbar sind, muß im Hostsystem eine Konfiguration entsprechend angepasst werden, um die Pakete weiterzuleiten. Dabei gibt es für zusätzliche Einzel-IPs zwei Möglichkeiten: Routed und Bridged.

### Routed (brouter)

Bei einer Routed-Konfiguration werden die Pakete geroutet. Dafür muß eine zusätzliche Bridge mit nahezu gleicher Konfiguration (ohne Gateway) wie eth0 angelegt werden. (Hinweis: bei Installation via YAST ist eine entsprechende Bridge bereits vorhanden und muß nur entsprechend konfiguriert werden.)

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
 # cat /etc/sysconfig/network/routes
default [Gateway-IP] - eth0
[Zusatz-IP]/32 - - br0
```

Für die virtuellen Maschinen muß eine Bridge konfiguriert werden. Bei einem Subnetz kann eine vereinfachte Konfiguration benutzt werden.

* Bridge für zusätzliches Subnetz 

```
# cat /etc/sysconfig/network/ifcfg-br0
BOOTPROTO='static'
BRIDGE='yes'
BRIDGE_FORWARDDELAY='0'
BRIDGE_PORTS=
BRIDGE_STP='off'
IPADDR='(IP aus Subnetz)/(CIDR des Subnetz)'
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

Für Einzel-IPs oder zur Nutzung aller Subnetz-IPs (Point-to-Point)

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

Wichtig: IP Forwarding via Yast oder direkt in `/etc/sysctl.conf` aktivieren.


### Gast

Point-to-Point für zusätzliche Einzel-IPs

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

Standardkonfiguration für zusätzliches Subnetz:

```
# cat /etc/sysconfig/network/ifcfg-eth0
# device: eth0
MTU=
STARTMODE='auto'
UNIQUE=
USERCONTROL='no'
BOOTPROTO='static'
IPADDR='(Subnetz-IP)/(CIDR Subnetz)'
REMOTE_IPADDR='(IP der Bridge)'
NETMASK=
BROADCAST=
ETHTOOL_OPTIONS=
NAME=
NETWORK=
```

```
# cat /etc/sysconfig/network/routes
default (Bridge-IP) - eth0
```

## Fazit

Hiermit sollten sie zusätzliche IPs auf ihrem Suse-basierten System installiert haben.


