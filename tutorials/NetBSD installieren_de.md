# NetBSD installieren
## Einführung
NetBSD ist ein unixoides Betriebssystem dessen Stärke es ist, dass es sich auf allen Architekturen sehr ähnlich verhält.

## Vorraussetzungen

Wer NetBSD installieren möchte, sollte sich wirklich sicher sein, da folgendes Problem besteht:

* Das [Rescue-System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System) ist ein Linux. Das BSD-Rescue hingegen ist ein FreeBSD.

* Im Linux Rescue-System lässt sich die Root-Partition z.B. so mounten: 

  `mount -r -t ufs -o ufstype=44bsd /dev/sda1 /mnt/netbsd`

Folgendes wird zusätzlich benötigt:

* Ein lokales UN*X System
* qemu 


## Installation

Zuerst erzeugt man ein leeres Image, beispielsweise in einer Größe von 2GB:

`dd if=/dev/zero of=install.img bs=2048 count=1048576`

Dann startet man qemu. Das Image wird als Festplatte festgelegt, die NetBSD install-iso als CDROM, booten dann von CDROM.

Auf das Image NetBSD installieren, dabei zu beachten:

* Nichts im MBR anpassen, also nur ein slice ("use whole disk")! 

Tipp:

* Eine / Partition, 1024 MB, eine Swap Partition auch 1024 MB
* /usr tmp etc nicht als eigene Partition (kommt später) 

qemu beenden, qemu neustarten, diesmal ohne CD und booten von unserem neuen Image. Dort loggt man sich ein und nimmt nur die wichtigsten Anpassungen vor:

* Nicht-root nutzer anlegen und zu wheel hinzufügen (für su). Sonst kann man sich später nicht über ssh einloggen (root-login wollen wir mal gar nicht erst erlauben!)
* In /etc/rc.conf hinzufügen: 

```
sshd=YES
dhclinet=YES
```

Der DHCP Server von Hetzner funktioniert einwandfrei. NetBSD konfiguriert dann alles von alleine.

* ssh keys erstellen: `/etc/rc.d/sshd start` (kann ziemlich dauern) 

ANMERKUNG:

Je nach Belieben vorerst unnötige Dienste ausschalten (sendmail...)

* Alle unnötigen Plattenzugriffe vermeiden! Wir wollen so viele Nullen im Image haben wie möglich, damit es komprimiert so klein wie möglich bleibt. Daher bringt Löschen von Dateien auch nichts! Alles, was nicht zwingend erforderlich ist auf NACH dem Aufspielen verschieben!
* qemu beenden und
* Image komprimieren (gzip -9) nach image.gz 

Image aufspielen:

Dazu entweder

* Image auf sicheren/schnellen Webspace hochladen => http://host/image.gz
* Ins Rescue-System booten
* Image aufspielen (wenn /dev/hda die Platte ist. Bei mir war es /dev/sda):
`wget -O - 'http://host/image.gz' | gzip -c -d > /dev/hda`

oder:

* Ins Rescue-System booten
* Von lokal Image über SSH aufspielen: 
`ssh root@server 'gzip -c -d > /dev/hda' < image.gz`

ANMERKUNG:

Statt direkt auf /dev/hda zu cat-en kann auch dd verwendet werden:

`dd of=/dev/hda bs=2048 count=1048576 conv=notrunc`

Folgendes hat bei mir funktioniert und nichtmal eine Minute gedauert:

Liegt das Image bei http://someotherhost/install.img.gz dann:

```
wget -O - 'http://someotherhost/install.img.gz' | gzip -d -c
| dd of=/dev/sda bs=2048 count=1048576 conv=notrun
```

Server rebooten und sich einloggen.


## Zusätzliche Schritte
Ich hatte hier Anfangs große Probleme, daher beschreibe ich es mal, obwohl die eigentliche Installation abgeschlossen ist:

Platte anpassen (da nur 2GB groß, statt der vollen Größe):

`fdisk -u /dev/wd0`

Nur Partition 0 ändern, auf volle Größe ausweiten.
Dann kontrollieren: 

`fdisk /dev/wd0`

Im Abschnitt `NetBSD disklabel disk geometry:`
Anzahl `cylinder` und `total sectors` aufschreiben. 

ACHTUNG: Wahrscheinlich ist es besser, jetzt einmal zu rebooten!

`disklabel -i wd0`

Mit `I` die geometry ändern: alles belassen (einfach ENTER) aber `cylinder` und `total sectors` anpassen. 

ANMERKUNG:

* Partition c => über volle Breite gehen lassen
* Partition d => dito.
* Partition e,f,g... => beliebig anpassen
* Mit 'C' partitionstabelle contagious machen (bestätigen)
* Mit 'W' schreiben 

ACHTUNG: Wahrscheinlich ist es besser, jetzt einmal zu rebooten!

Mit `newfs` die neuen Partitionen formatieren, mount-punkte festlegen, etc.
Praktisch wäre, `/usr`, `/home` etc. auf die neuen Partitionen zu kopieren. 

Ein abschließender Reboot und die Konfiguration kann beginnen.

## Weblinks

http://www.bsdforen.de/showthread.php?t=14574

http://www.daemonology.net/depenguinator/ 

## Fazit
Nun sollten sie eine funktionierende NetBSD Installation auf ihrem Server haben.