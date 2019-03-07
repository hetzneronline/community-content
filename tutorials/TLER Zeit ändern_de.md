# TLER Zeit ändern
## Einleitung
Einige Festplatten besitzen ein Feature namens Time-Limited Error Recovery = TLER. Diese TLER Variable bestimmt die Zeit die eine Festplatte verwenden darf um defekte Sektoren bzw. andere Reparaturen durchzuführen. Ist die TLER Zeit größer als die voreingestellte Timeout Zeit vom RAID Controller kann es passieren das der RAID-Controller die Festplatte als defekt markiert, obwohl diese nur versucht defekte Sektoren zu reparieren. Die TLER Zeit kann man mit `smartctl` aber anpassen.

## TLER mit Adaptec Controller anpassen
[Hetzner Rescue-System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System) booten.

Die sg Devices aktivieren:

`modprobe sg`

Die einzelnen Festplatten kann man sich mit diesem Befehl anzeigen lassen:

`ls /dev/sg*`
(z.B. sg1)

TLER Zeit ändern

`smartctl -d sat -l scterc,70,70 /dev/sg1`

## TLER mit 3ware Controller anpassen
[Hetzner Rescue-System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System) booten.

Device herausfinden:

`ls /dev/tw*`
(z.B. twa)

Controller Nummer auslesen:

`tw_cli show | grep ^c | cut -c 2`(z.B. 0)

Daraus ergibt sich z.B. das Device `/dev/twa0`

Festplatten mit Hilfe der Controller Nummer auslesen

`tw_cli /c0 show | cut -c 2`(z.B. 0)

TLER Zeit mit der Festplatten Nummer und dem Device ändern:
`smartctl -d 3ware,0 -l scterc,70,70 /dev/twa0`

## TLER mit LSI Controller anpassen
[Hetzner Rescue-System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System) booten.

Device Nummer herausfinden:
`megacli -pdlist -aall | grep "Device Id:" | cut -c 12-`(z.B. 4)

TLER Zeit ändern mit Hilfe der Device Nummer:
`smartctl -d megaraid,4 -l scterc,70,70 /dev/sda`
## Fazit
Nun sollten sie die TLER Zeit ihrer Festplatte so eingestellt haben, dass die Festplatte nicht mehr als defekt markiert wird.