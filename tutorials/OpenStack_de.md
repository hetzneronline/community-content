# OpenStack / StackOps bei Hetzner installieren 
## Einführung

In diesem Artikel wird OpenStack auf einem Server von Hetzner installiert. [OpenStack](http://www.openstack.org/) ist eine Software die eine freie Architektur für Cloud Computer zur Verfügung stellt.

### Vorbereitung
Um gleich zu beginnen, benötigt ihr auf eurem lokalem Rechner einen VNC Viewer (z.B. [TightVNC](http://www.tightvnc.com/)) und einen SSH Client (openssh oder putty).
Openstack soll mittels qemu direkt auf der ersten Server-Platte installiert und konfiguriert werden.

### Rescue System vorbereiten
Zuerst muss der Server in den Rescue Mode gebootet werden. Um Verwechslungen zu vermeiden, werden die Terminals mit `resuce`und `openbsd` bezeichnet. Im Rescue System setzen wir das um:
 
`# PS1="rescue # "`

Damit wir mit den beiden Festplatten im Server einzeln arbeiten können, müssen wir erstmal den alten Raid auflösen:

```
rescue # mdadm --stop /dev/md0
rescue # mdadm --stop /dev/md1
rescue # mdadm --stop /dev/md2
```

Da im Rescue System nicht allzuviel Platz ist, nutzen wir die zweite Festplatte erstmal als Speicherplatz für das OpenBSD CD-ISO. Wir partitionieren die Festplatte um auf eine Linux-Partition und formatieren diese.

```
rescue # fdisk /dev/sdb   # o = alles löschen, n = neue partition anlegen
rescue # mkfs.ext3 -j /dev/sdb1
rescue # mount /dev/sdb1 /mnt
```

Jetzt ziehen wir uns das CD-ISO nach Wahl. Mirrors findest du [hier](https://sourceforge.net/projects/stackops/files/).

```
rescue # cd /mnt
rescue # wget <stackops download url>
```

Wir müssen noch die `iptables-Regel` um den Traffic der virtuellen Maschiene nach aussen weiterzuleitenund das Packet `sudo` für das `qemu-ifup Script`nachinstallieren: 

```
rescue # iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
rescue # apt-get install sudo
```

Wenn qemu gleich startet, gibt es zwei Möglichkeiten es zu nutzen. Entweder man aktiviert bei seiner SSH Verbindung die X11-Weiterleitung (-Y) was allerdings wirklich *sehr* langsam ist oder man nutzt den bei qemu mitgelieferten VNC-Server, welcher ein wenig performanter ist. Wir nutzen letzeres und stellen die Verbindung zum VNC-Server über ssh her. Wer gerade auf einem UNIX-artigen System arbeitet, kann das mit folgendem Befehl von seiner lokalen Maschiene aus tun:

`lokal # ssh -L 5900:localhost:5900 root@<unsere-server-ip>`

Das Äquivalent kann man mit PuTTy auch zusammenklicken, beim Verbindungsmenü gibt es den Unterpunkt `Tunnel`.

Jetzt sind wir soweit und können die Installations-CD starten. Dazu führen wir folgenden Befehl aus. Wenn ihr i386 gewählt habt, könnt ihr einfach "qemu" nutzen, i.d.R. habt ihr amd64 gewählt, wozu das entsprechende qemu auch genutzt werden muss:

`rescue # qemu-system-x86_64 -m 1024 -hda /dev/sda -net nic -net tap -cdrom /mnt/install46.iso -boot d -vnc localhost:0 &`

Bevor wir in qemu einsteigen, benötigen wir noch ein paar Informationen vom Server. Notiert euch `IP Adresse` und `Netzmaske` vom `eth0`- und `tap0`-Interface. Desweiteren werden die `DNS Server` benötigt.

```
rescue # ifconfig tap0
rescue # ifconfig eth0
rescue # cat /etc/resolv.conf
```

## StackOps-Installation

Jetzt können wir die Installation von StackOps beginnen. Dazu startet man den VNC-Viewer seiner Wahl und verbindet sich mit `localhost` (wir haben ja gerade einen Tunnel erstellt).

`lokal # vncviewer localhost`

Es sollte der Boot-Bildschirm der StackOps-Distribution erscheinen.

![alt text](https://wiki.hetzner.de/images/d/d2/StackOps_boot.png "Logo Title Text 1")

Hier wählst Du je nach persönlicher Vorliebe einen der beiden folgenden Punkte aus:

* Install StackOps node (US Keyboard)
* Install StackOps node (Keyboard selection)

Es findet eine fast normale Ubuntu-Installation statt.

Die wichtigste Einstellung die abgefragt wird ist die nach den Netzwerk-Einstellungen. Diese haben wir uns ja in einem vorherigen Schritt bereits notiert.

__Tipp:__ Bitte unbedingt auf die richtige Netzwerk-Maske achten. Bei Hetzner ist diese meistens nicht `255.255.255.0`!

Es werden eine Reihe von Fragen gestellt, deren Beantwortung aber hoffentlich keine Probleme bereiten sollte.

![alt text](https://wiki.hetzner.de/images/9/95/StackOps_Installation_abgeschlossen.png "Logo Title Text 1")

Das Herausnehmen der CD (also des ISO-Images) ist im QEMU nicht so einfach. Daher nehmen wir einfach in Kauf, dass noch einmal von dem ISO-Image gebootet wird und wählen dann im Startbildschirm die Option `Boot from first harddisk`.

![alt text](https://wiki.hetzner.de/images/f/fd/StackOps_boot_firsthd.png "Logo Title Text 1")

## StackOps für die reale Welt vorbereiten

* Netzwerk-Konfiguration
* root-Password ändern:
`/etc/udev/rules.d/70-persistent-net.rules`

Nun starten wir den Server neu:

`rescue # reboot`

und verbinden uns mit: 

`lokal # ssh root@<server ip>`


## Fazit
Nun sollten sie eine funktionierende StackOps Installation auf ihrem Server am Laufen haben.
