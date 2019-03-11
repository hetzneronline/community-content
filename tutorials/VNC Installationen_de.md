# VNC Installationen
## Einführung
Seit Juni 2007 bietet die Hetzner Online GmbH über den Robot das starten von sogenannten VNC-Installationen an.

VNC ist eine Software, die den Bildschirminhalt eines entfernten Rechners (auf dem die VNC-Server Software läuft) auf einem lokalen Rechner (auf dem die VNC-Viewer Software läuft) anzeigt und im Gegenzug Tastatur- und Mausbewegungen des lokalen Rechners an den entfernten Rechner sendet. Somit kann der entfernte Server installiert werden, als würde man direkt am Monitor sitzen.

Hierbei werden alle benötigten Daten über das Netzwerk kopiert. Da die benötigten Dateien auf dem Firmeneigenen Downloadserver liegen, sollte sich das Installieren der Pakete nicht länger gestalten als von CD oder DVD.

Man benötigt jedoch einen VNC-Client Infos [hier](http://de.wikipedia.org/wiki/Virtual_Network_Computing) um auf die Installationsroutine zugreifen zu können.

## Starten einer VNC-Installation
Um eine VNC-Installation zu starten, kann man im Robot unter dem Menüpunkt `VNC-Installationen` bei einer Liste seiner Server via Dropdown das gewünschte Betriebssystem, die Architektur und die gewünschte Sprache für den jeweiligen Server auswählen. Nach Bestätigung wird nun ein Passwort angezeigt mit dem man sich ca. 1-2 Minuten nach einem Reset des Servers per VNC einloggen kann. VNC Adresse wäre:

`<IP-Adresse>:1`

oder

`<IP-Adresse>:5901`

Beispiel:

```
192.168.0.1:1
192.168.0.1:5901
```

## Kompatible Betriebssysteme
* CentOS 6.9
* CentOS 7.5
* Fedora 28
* openSUSE 42.3
* openSUSE 15.0

## Besonderheiten bei openSuSE
Die VNC-Installation von openSuSE erfolgt in zwei Schritten. Zunächst werden die Sprachen, die Uhrzeit, die Partitionen, die Pakete, der Bootloader usw. ausgewählt und installiert. Nach der Installation bootet das System erneut und stellt im zweiten Schritt automatisch erneut einen VNC-Zugang zur Verfügung. Sie können sich mit dem bereits vorher verwendeten Passwort erneut einloggen. Im zweiten Schritt wird die Installation abgeschlossen, was in der Regel nur minimale Nutzerinteraktion erfordert. Anschließend bootet das System normal und ist per SSH erreichbar.

__Zu beachten ist dabei:__

Standardmäßig ist die Firewall aktiv und SSH wird blockiert! Daher sollte bei der Installation (erster Schritt) die vorgeschlagene Auswahl an Paketen um das "erweiterte Basispaket" reduziert werden. Es wird dann keine Firewall installiert. Sie kann nach Abschluss der VNC-Installation einfach über YAST nachinstalliert und konfiguriert werden.

Achtung: Wird die Firewall im ersten Schritt installiert, ist ein späterer Zugriff auf den Server nicht mehr möglich.

__Bei Installationen mit Software RAID ist zu beachten:__

Wird `/boot` auf einer eigenen RAID 1 Partition eingehangen, muss die Konfiguration des Bootloaders geändert werden. Der Bootloader muss in diesem Falls aus dem MBR heraus gestartet werden. openSuSE ändert den Konfigurationsvorschlag des Bootloaders (GRUB) nicht automatisch ab, was ohne Korrektur dazu führt, dass das System nicht bootet.

Wird auch der swap auf einer RAID 1 Partition untergebracht, so ist der swap nach der Installation beim ersten Start des Systems deaktiviert.

Mit `cat /proc/mdstat` erscheint die swap Partition als `active (auto-read-only)`.

Ggf. erfolgt gleichzeitig ein resync zwischen den anderen RAID Partitionen (Abhängig von Ihrer Partitionierung). Den Fortschritt können sie mit `cat /proc/mdstat` verfolgen.

Ist dieser abgeschlossen, können Sie den swap über folgende Befehle aktivieren:

```
swapoff -a
mdadm --readwrite /dev/mdX
```

wobei X = RAID index ist.

Nun findet ein resync des swap RAIDs statt. Im Anschluss sollte das System noch einmal gebootet werden.

Wenn RealVNC oder UltraVNC als Client verwendet wird, kann es unter Umständen zu Verbindungsabbrüchen kommen. Versuchen Sie in diesem Fall folgende Einstellungen:

* Hextile Encoding
* Color Level Low (256 Farben)

## Besonderheiten bei Fedora
Bei der Installation wird die Firewall automatisch aktiviert. Standardmäßig werden nur eine wenige Ports zugelassen - darunter auch SSH. Die Regeln werden mittels [firewalld](https://fedoraproject.org/wiki/FirewallD) dynamisch erstellt. Einstellungen an der Firewall können mit Hilfe von `firewall-cmd`vorgenommen werden

Ebenfalls sind die [bekannten Bugs](https://fedoraproject.org/wiki/Common_F22_bugs) bei der Installation zu beachten.

Fedora 25 kann nicht auf dem vServer CX 10 installiert werden, da mehr als 1GB RAM für die Installation benötigt werden.

## Fazit

Hiermit sollten sie eine Fernsteuerung ihres Servers über VNC eingerichtet haben.