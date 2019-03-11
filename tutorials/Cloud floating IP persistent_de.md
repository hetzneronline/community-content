# Cloud Floating IP Persistent
## Einführung
???

Hinweis: Wenn mehrere Floating IP's benutzt werden, muss die letzte Zahl beim Interface (eth0:1) erhöht werden (z.B. eth0:2).

## Auf Debian basierende Distributionen (Ubuntu, Debian):


Bauen Sie eine SSH-Verbindung zu Ihrem Cloud Server auf

Erstellen Sie die Konfigurationsdatei und öffnen einen Editor mit dieser:

```
touch /etc/network/interfaces.d/60-my-floating-ip.cfg
nano /etc/network/interfaces.d/60-my-floating-ip.cfg
```

Kopieren Sie das folgende Konfigurationsbeispiel in die Datei und ersetzen Sie `your.float.ing.ip` mit ihrer Floating IP.

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
    address Eine IPv6 Adresse aus dem Subnetz, e.g. 2a01:4f9:0:2a1::2
    netmask 64
```

Sie müssen nun das Netzwerk neustarten. Achtung: Dies wird Ihre Netzwerkverbindung kurzzeitig trennen.

`sudo service networking restart`


## Auf RHEL basierende Distributionen (Fedora, CentOS):

Bauen Sie eine SSH-Verbindung zu Ihrem Cloud Server auf

Erstellen Sie die Konfigurationsdatei und öffnen einen Editor mit dieser.

```
touch /etc/sysconfig/network-scripts/ifcfg-eth0:1
vi /etc/sysconfig/network-scripts/ifcfg-eth0:1
```

Kopieren Sie das folgende Konfigurationsbeispiel in die Datei und ersetzen Sie `your.float.ing.ip` mit ihrer Floating IP.

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
IPV6ADDR=Eine IPv6 Adresse aus dem Subnetz, e.g. 2a01:4f9:0:2a1::2/64
IPV6INIT=yes
```

Sie müssen nun das Netzwerk neustarten. Achtung: Dies wird Ihre Netzwerkverbindung kurzzeitig trennen.

`systemctl restart network`

## Fazit 
???
