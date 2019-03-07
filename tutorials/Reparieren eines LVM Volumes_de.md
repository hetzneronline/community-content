# Reparieren eines LVM-Volumes
## Einleitung
Wenn Sie nach einem SSH Login diese Warnmeldungen angezeigt bekommen,
`*** /dev/md2 should be checked for errors ***`
`*** /dev/md1 should be checked for errors ***`


dann wird es Zeit, die angegebenen logischen Partitionen zu reparieren.
In diesem Artikel soll nun erläutert werden, wie sie den Fehler beheben können.

## Reparatur
Achtung: Die logischen Partitionen dürfen nicht gemountet sein. Überprüfen Sie mit `mount`, welche Partitionen mit welchen Dateisystemen versehen und gemountet sind. Hier ein Auszug:

```
mount
/dev/md1 on /boot type ext3 (rw)
/dev/md2 on / type ext3 (rw)
```
Notieren Sie sich obige Ausgaben für md1 und md2. Starten Sie nun das [Hetzner Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System).

Es soll nun diese Fehlermeldung vermieden werden:

`@ WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED! @`


`<IP>` steht für Ihre IP Adresse im Format `123.123.123.123`. Deshalb müssen Sie die Server Änderungen auf Ihrem Rechner bestätigen:

`ssh-keygen -R <IP>`

Erst jetzt ist diese Sicherheitswarnung weg. Loggen Sie sich nun über ssh auf Ihrem Server ein und verwenden Sie dieses Mal das Rescue-Passwort:

`ssh root@<IP>`

Es gibt nun keine Mount Verbindungen für md1 und md2:

`mount | grep md`

Hier werden keine /dev/md1 Einträge wie vorhin angezeigt.


Mit diesem Kommando wird die Formatierung automatisch ermittelt:

`fsck -C0 -y /dev/md1`

Wenn Sie die Formatierung der Partition bereits kennen (Kommando `mount`), dann kann alternativ das direkte Kommando verwendet werden (statt `ext3` ggf. die zuvor ermittelte Formatierung einsetzen).

`/sbin/fsck -t ext3 /dev/md1`

Nun machen Sie einen Warmstart des Servers, um wieder in Ihr eigenes System hochzufahren:
`reboot`

Danach müssen Sie auf Ihrem Rechner wiederum die Server Änderungen bestätigen.

`ssh-keygen -R <IP>`

## Fazit
Mit den oben genannten Schritten sollten die Warnmeldungen verschwunden und die logischen Partitionen repariert sein.
