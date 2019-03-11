# Windows Server Hyper V
## Einführung
Im Folgenden wird beschrieben wie zusätzliche Einzel-IPs in Verbindung mit Hyper-V für virtuelle Computer genutzt werden können.

Diese können mit Hyper-V nur mit virtuellen MAC Adressen genutzt werden, die über den Robot für jede Einzel-IP beantragt werden können.

## Rollen und Features

Benötigte Rollen und Features sind:

* `Hyper-V` und `Verwaltung` 

Diese können im Servermanager via `Rollen und Features hinzufügen` nachinstalliert werden.

Während der Installation von Hyper-V einen virtuellen Switch mit der physikalischen Netzwerkkarte erstellen:

![alt text](https://wiki.hetzner.de/images/thumb/0/05/W2012r2_hyper-v.png/800px-W2012r2_hyper-v.png "Logo Title Text 1")


### Virtuellen Switch anlegen

HINWEIS: Dieser Schritt ist nur notwendig, wenn bei der Installation der Hyper-V Rolle kein vSwitch angelegt wurde.

Hyper-V Manager öffnen und im Manager für virtuelle Switche einen neuen virtuellen Switch vom Typ Extern anlegen und den Haken bei `Gemeinsames Verwenden dieses Netzwerkadapters für das Verwaltungsbetriebssystem zulassen` setzen 

![alt text](https://wiki.hetzner.de/images/thumb/2/25/W2012r3_single-vswitch.png/800px-W2012r3_single-vswitch.png "Logo Title Text 1")

## Hyper-V

Neuen virtuellen Computer der `Generation 1` anlegen:

![alt text](https://wiki.hetzner.de/images/thumb/f/f0/W2012r2_hyperv-gen1.png/800px-W2012r2_hyperv-gen1.png "Logo Title Text 1")

Via `Einstellungen` die automatisch hinzugefügte Netzwerkkarte entfernen
Via `Hardware hinzufügen` eine neue Netzwerkkarte vom Typ ältere Netzwerkkarte hinzufügen und mit dem internen virtuellen Switch verbinden:

![alt text](https://wiki.hetzner.de/images/thumb/e/e4/W2012r2_hyperv-addnic.png/800px-W2012r2_hyperv-addnic.png "Logo Title Text 1")


Unter `Erweiterte Features` die virtuelle MAC-Adresse aus dem Hetzner [Robot](https://wiki.hetzner.de/index.php/Robot) statisch eintragen:

![alt text](https://wiki.hetzner.de/images/thumb/5/53/W2012r2_hyperv-mac.png/800px-W2012r2_hyperv-mac.png "Logo Title Text 1")

Virtuellen Computer starten und PXE Boot testen.
Bei korrekter Konfiguration erscheint das Hetzner PXE Bootmenü (blaues Logo):

![alt text](https://wiki.hetzner.de/images/thumb/8/8b/Pxe_boot.jpg/789px-Pxe_boot.jpg "Logo Title Text 1")

## Fazit
