# Software-RAID
## Einführung
Von Software-RAID spricht man, wenn das Zusammenwirken mehrerer Festplatten komplett softwareseitig organisiert wird.

Mit RAID Level 1 (mirroring) erreicht man erhöhte Sicherheit, da beim Ausfall einer einzelnen Festplatte alle gespeicherten Daten noch auf der zweiten Festplatte vorhanden sind. Mit RAID Level 0 (striping) erreicht man bei zwei verwendeten Festplatten doppelte Speicherkapazität und erhöhte Lesegeschwindigkeit gegenüber RAID 1 - verliert allerdings alle Daten wenn auch nur eine Festplatte ausfällt.

Das von uns angebotene [installimage](https://wiki.hetzner.de/index.php/Installimage) zur Installation von Betriebssystemen bietet die Möglichkeit zur Konfiguration verschiedener Raidlevels. Dabei lässt sich das Software-RAID auch mit LVM kombinieren.

Weiterhin werden von Hetzner vorinstallierte Systeme mit RAID-Superblöcken der Version 1.2 ausgeliefert, wenn dies vom Betriebssystem unterstützt wird (d.h. alle System mit grub2 als Bootloader). Bei der Installation via VNC kann es daher vorkommen, daß die Installer andere Metadaten-Versionen nutzen.

## E-Mail-Benachrichtigung bei Ausfall einer Festplatte im Software-RAID
Voraussetzung: installierter und konfigurierter Mailserver

### Debian/Ubuntu/CentOS
`/etc/mdadm/mdadm.conf` bzw. `/etc/mdadm.conf` (CentOS) öffnen und folgende Zeile editieren:

`MAILADDR root`

Hier kann direkt eine Zieladresse angegeben werden. Alternativ bietet es sich an alle an root gerichteten Emails via `/etc/aliases` an eine bestimmte Mailadresse weiterzuleiten.

Optional kann auch die Absenderadresse konfiguriert werden:

`MAILFROM mdadm@example.com`

Wichtig bei Debian und Ubuntu ist, dass `AUTOCHECK` in der Datei `/etc/default/mdadm` auf `true` eingestellt ist:

```
# grep AUTOCHECK= /etc/default/mdadm
AUTOCHECK=true
```

### openSUSE


`/etc/sysconfig/mdadm` editieren und die Variable `MDADM_MAIL` auf die gewünschte Adresse, an die Benachrichtigungen gesendet werden sollen, setzen:

`MDADM_MAIL="example@example.com"`

### über ein python Script

Eine weitere Möglichkeit besteht darin, über ein [python Script](https://git.gu471.de/gu471/ServerPub/blob/master/root/send_smart.py) ([Handhabung](https://gu471.de/chapters/24) die Ausgaben von

`cat /proc/mdstat`

und

`smartctl -A -d ata /dev/sdaX`

zu parsen und nur die nötigen Informationen per eMail zu schicken.

Der Vorteil eines selbstgeschriebenen Scripts besteht darin, dass die eMail auch im HTML-Format geschickt werden kann und somit ein schnellerer Überblick (insbesondere über die S.M.A.R.T.-Daten) gewährleistet werden kann.

## Software-RAID auflösen

Um ein Software-RAID aufzulösen kann man im Rescue-System folgende Befehle absetzen:

```
mdadm --remove /dev/md0
mdadm --remove /dev/md1
mdadm --remove /dev/md2
```

```
mdadm --stop /dev/md0
mdadm --stop /dev/md1
mdadm --stop /dev/md2
```

Danach kann die Festplatte wieder normal formatiert werden (zb. mit ext3):

```
mkfs.ext3 /dev/sda
mkfs.ext3 /dev/sdb
```

Das Ergebnis kann mittels

`fdisk -l`

überprüft werden. Das Software-RAID sollte nun verschwunden sein.

Danach kann [installimage](https://wiki.hetzner.de/index.php/Installimage) aufgerufen werden, um ein neues Betriebssystem aufzuspielen.

Wenn ein Software-RAID existiert reicht das pure aufrufen von installimage und das deaktiveren von einem Software-RAID nicht aus. Der Server fährt in diesem Fall nicht mehr hoch.


## Weiterführende Infos
[Betriebssystem Images mit installimage installieren ](https://wiki.hetzner.de/index.php/Installimage) incl. Software-RAID-Unterstützung, Betriebssystemeunabhängig

[Austausch einer defekten Festplatte im Software-RAID](https://wiki.hetzner.de/index.php/Festplattenaustausch_im_Software-RAID)

## Fazit
Hiermit sollten sie in der Lage sein, ein Software-Raid einzurichten, es wieder aufzulösen und bei Fehlern über Mail benachrichtigt werden.