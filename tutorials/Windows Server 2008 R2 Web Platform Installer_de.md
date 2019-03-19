# Windows Server 2008 R2 Web Platform Installer
## Einführung

Im Folgenden wird beschrieben, wie Sie unter Windows Server 2008 R2 den `Web Platform Installer`installieren und verwenden können.

## Installation
Die einfachste Möglichkeit stets die aktuellen Software Komponenten für die Microsoft Web-Plattform herunterzuladen und zu installieren bietet der kostenlose Web Platform Installer. Das Werkzeug ist eine gute Anlaufstelle auch für die Installation von Open Source-, ASP.NET- und PHP-Webanwendungen für den IIS:

![alt text](https://wiki.hetzner.de/images/0/03/10-webplatforminstaller.jpeg "Logo Title Text 1")

Den Web Platform installer finden Sie unter: `http://www.microsoft.com/web` -> `Downloads` -> `Web Platform Installer`

Bei unseren Windows Web Server 2008 R2 Installationen seit dem 17.02.2010 ist der Web Platform Installer bereits vorinstalliert. Sie finden ihn im Start-Menü unter `Alle Programme`.

Beim Starten lädt das Werkzeug eine Liste aller aktuellen Web-Komponenten für Ihr System vom Microsoft Server herunter:

![alt text](https://wiki.hetzner.de/images/3/34/11-webplatforminstaller.jpeg "Logo Title Text 1")

Relevante Komponenten aus den Bereichen Webserver, Media-Streaming, Entwicklungstools, Webanwendungen und Updates können ausgewählt werden.

Weiterführende Informationen finden Sie [hier](http://learn.iis.net/) -> `Microsoft Web Platform`


### Beispiel 1) Installation der URL Rewrite Funktionalität

![alt text](https://wiki.hetzner.de/images/8/85/12-webplatforminstaller.jpeg "Logo Title Text 1")

Anmerkung: Die Webplattform für den IIS wird fortlaufend erweitert, der Web Platform Installer bietet eine einfache Möglichkeit hier Schritt zu halten. Etwaige Abhängigkeiten der ausgewählten Pakete werden erkannt und automatisch hinzugefügt. Die Installation kann je nach den ausgewählten Komponenten Benutzereingaben bzw. das Zustimmen zu Lizenzbedingungen erfordern. Die ausgewählten Komponenten werden anschließend heruntergeladen und installiert.

![alt text](https://wiki.hetzner.de/images/8/8e/13-webplatforminstaller.jpeg "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/a/a5/14-webplatforminstaller.jpeg "Logo Title Text 1")

### Beispiel 2) Installation von Aquia Drupal mit allen Abhängigkeiten (PHP, MySQL,...)

Auswahl der Acquia Drupal Web-Anwendung:

![alt text](https://wiki.hetzner.de/images/4/4a/15-webplatforminstaller.jpeg "Logo Title Text 1")

Es wurden noch folgende Abhängigkeiten erkannt:

![alt text](https://wiki.hetzner.de/images/9/91/16-webplatforminstaller.jpeg "Logo Title Text 1")

Die MySQL Datenbank Installation benötigt noch ein `root`-Passwort:

![alt text](https://wiki.hetzner.de/images/6/64/17-webplatforminstaller.jpeg "Logo Title Text 1")

Die Webanwendung soll z.B. unter der Standard-Webseite eingerichtet werden:

![alt text](https://wiki.hetzner.de/images/5/59/18-webplatforminstaller.jpeg "Logo Title Text 1")

Acquia Drupal benötigt noch eine eigene MySQL-Datenbank:

![alt text](https://wiki.hetzner.de/images/a/a3/19-webplatforminstaller.jpeg "Logo Title Text 1")

Nach der Installation gibt es eine Zusammenfassung:

![alt text](https://wiki.hetzner.de/images/a/a3/20-webplatforminstaller.jpeg "Logo Title Text 1")

Die letzten Schritte des Acquia Drupal Setups werden ausgeführt:

![alt text](https://wiki.hetzner.de/images/4/43/21-webplatforminstaller.jpeg "Logo Title Text 1")

Ein paar Klicks und Eingaben später:

![alt text](https://wiki.hetzner.de/images/1/15/22-webplatforminstaller.jpeg "Logo Title Text 1")

## Fazit
Nun sollten sie Windows Server 2008 R2 mit Hilf des Web Installers installiert haben.
