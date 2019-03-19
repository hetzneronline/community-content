# CentOS/Fedora via Kickstart installieren
## Einführung
In diesem Artikel wird die individuelle automatische Installation via Kickstart erklärt. 
* Das Hetzner.Kickstarter-Script bereitet im Rescue-Modus eine Installations-Umgebung auf der ersten Festplatte vor.
* Die zu installierende Konfiguration wird anhand einer per http zugänglichen Kickstart-Konfigurations-Datei ermittelt und automatisch durchgeführt.
* Eine Remote-Console (KVM-Konsole) wird nicht benötigt 

## Vorbereitungen

* Kickstart-Konfigurations-Datei erstellen. Die Kickstart-Datei muss absolut fehlerfrei sein.
*  Kickstart-Konfigurations-Datei auf einem eigenen Web-Server hinterlegen
* Alternativ eines der Beispiele auswählen: Siehe [Hetzner Kickstart Examples](https://wiki.hetzner.de/index.php/Hetzner_Kickstart_Examples)
* Im Hetzner-Robot den Reverse-DNS-Eintrag für den Server setzen 

## Installationsumgebung einrichten

* Im Hetzner Robot den Rescue-Modus aktivieren (64bit)
* Im Hetzner Robot einen Reset auslösen (Automatischen Hardware-Reset auslösen)
* Per SSH auf den Server einloggen
* Das Hetzner.Kickstarter-Script herunterladen 

`wget https://wiki.hetzner.de/images/2/24/Hetzner.Kickstarter.txt`

* Ausführen des Hetzner.Kickstarter-Scripts 

`sh Hetzner.Kickstarter.txt https://wiki.hetzner.de/images/9/91/Kickstart.cfg.txt`

* Erscheinen irgendwelche Fehlermeldungen?
* Server Reboot 

`reboot`

## Automatische Installation

* Die Installation beginnt nach dem reboot automatisch und sollte ohne Eingriff durchlaufen
* Wenn die Installation abgeschlossen ist, erfolgt ein automatischer Reboot.
* Nach ca. 10-20 Minuten sollte der Server per ssh erreichbar sein. 

### Optional: Über VNC mitverfolgen

Die Installation lässt sich über VNC mitverfolgen
Wenn ein VNC Listener aktiv und korrekt konfiguriert ist, startet VNC von allein (NAT Port 5500)
Sonst manueller Start von VNC (wie auch bei der Hetzner-VNC-Installation) 

## Weiterführende Informationen
* [Reverse-DNS-Eintrag für den Server setzen](https://wiki.hetzner.de/index.php/DNS-Reverse-DNS)
* Schlüssel bei Hetzner hinterlegen
* VNC Listener hinter NAT-Router 

## Fazit
Nun sollten sie CentOS über Kickstart auf ihrem Server installiert haben.