# S.M.A.R.T Fehlerüberwachung und -meldung bei Festplatten
## Einführung
S.M.A.R.T. ist eine Funktion, mit der moderne Festplatten sich selbst überwachen und gegebenenfalls einen Fehlerstatus an den Hostcontroller übermitteln können.

## Installation
Unter Linux können diese Funktionen mit Hilfe der Software [smartctl](https://sourceforge.net/projects/smartmontools/) gesteuert bzw. ausgelesen werden. Bei Debian wird S.M.A.R.T. einfach über `apt-get` installiert.

## S.M.A.R.T konfigurieren und nutzen

Aktivieren:

`smartctl -s on -d ata /dev/sda`

Abfragen:

```
smartctl -a -d ata /dev/sda
smartctl -A -d ata /dev/sda
```

Platteninfo:

`smartctl -i -d ata /dev/sda`

Gesundheitsstatus:

`smartctl -H /dev/sda`

Festplatten capabilities:

`smartctl -c -d ata /dev/sda`

Selbsttest:

`smartctl -t short -d ata /dev/sda`

Ergebnisse Selbstest:

`smartctl -l selftest -d ata /dev/sda`

Fehler anzeigen (wenn vorhanden):

`smartctl -l error -d ata /dev/sda`

### NVMe

siehe [Smart-Log abfragen](https://wiki.hetzner.de/index.php/NVMe#Smart-Log_abfragen)

## Fazit

Mit diesem Artikel sollten sie in der Lage sein die SMART-Werte ihrer Festplatten auszulesen.